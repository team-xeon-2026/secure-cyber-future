<?php
// Enable GZIP compression for faster delivery
if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
    ob_start('ob_gzhandler');
} else {
    // Start output buffering to ensure clean JSON responses
    ob_start();
}// Include database configuration file
require_once 'config.php';

// Include admin authentication file
require_once 'admin_auth.php';

// Ensure the user is logged in as admin
require_admin_login();

// Handle PDF proxy request to solve embedding issues
if (isset($_GET['action']) && $_GET['action'] == 'pdf_proxy' && isset($_GET['url'])) {
    $pdfUrl = urldecode($_GET['url']);
    
    // Validate URL to prevent security issues
    if (filter_var($pdfUrl, FILTER_VALIDATE_URL)) {
        // Get the PDF content
        $pdfContent = @file_get_contents($pdfUrl);
        
        if ($pdfContent !== false) {
            // Set proper headers for inline PDF viewing
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="resume.pdf"');
            header('Cache-Control: public, max-age=3600');
            
            // Output the PDF content
            echo $pdfContent;
            exit;
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            echo "Error loading PDF content.";
            exit;
        }
    } else {
        header('HTTP/1.1 400 Bad Request');
        echo "Invalid URL provided.";
        exit;
    }
}

// Using getDbConnection() from config.php

function generateUUID() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// Handle application status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_application_status') {
    $applicationId = $_POST['application_id'];
    $status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';

    try {
        $conn = getDbConnection();
        
        // First check if status column exists
        $statusCheck = "SELECT EXISTS (
            SELECT FROM information_schema.columns 
            WHERE table_name = 'job_applications' 
            AND column_name = 'status'
        )";
        
        $statusStmt = $conn->prepare($statusCheck);
        $statusStmt->execute();
        $hasStatus = $statusStmt->fetchColumn();
        
        // Separately check if notes column exists
        $notesCheck = "SELECT EXISTS (
            SELECT FROM information_schema.columns 
            WHERE table_name = 'job_applications' 
            AND column_name = 'notes'
        )";
        
        $notesStmt = $conn->prepare($notesCheck);
        $notesStmt->execute();
        $hasNotes = $notesStmt->fetchColumn();
        
        // Add needed columns if they don't exist
        if (!$hasStatus) {
            $conn->exec("ALTER TABLE job_applications ADD COLUMN status VARCHAR(50) DEFAULT 'pending'");
            error_log("Added status column to job_applications table");
        }
        
        if (!$hasNotes) {
            $conn->exec("ALTER TABLE job_applications ADD COLUMN notes TEXT DEFAULT ''");
            error_log("Added notes column to job_applications table");
        }
        
        // Now update with both fields if they exist, or just status if notes doesn't exist
        if ($hasNotes || !$hasNotes) {
            $sql = "UPDATE job_applications SET status = ?, notes = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$status, $notes, $applicationId]);
        } else {
            $sql = "UPDATE job_applications SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$status, $applicationId]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Application status updated successfully!']);
    } catch (PDOException $e) {
        error_log("Status update error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle bulk application actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'bulk_update_applications') {
    $applicationIds = $_POST['application_ids'];
    $status = $_POST['status'];
    
    try {
        $conn = getDbConnection();
        
        // Check if status column exists
        $statusCheck = "SELECT EXISTS (
            SELECT FROM information_schema.columns 
            WHERE table_name = 'job_applications' 
            AND column_name = 'status'
        )";
        
        $statusStmt = $conn->prepare($statusCheck);
        $statusStmt->execute();
        $hasStatus = $statusStmt->fetchColumn();
        
        // Add status column if it doesn't exist
        if (!$hasStatus) {
            $conn->exec("ALTER TABLE job_applications ADD COLUMN status VARCHAR(50) DEFAULT 'pending'");
            error_log("Added status column to job_applications table for bulk update");
        }
        
        // Convert array to string for PostgreSQL
        $idPlaceholders = implode(',', array_fill(0, count($applicationIds), '?'));
        
        // Check if updated_at column exists
        $updatedAtCheck = "SELECT EXISTS (
            SELECT FROM information_schema.columns 
            WHERE table_name = 'job_applications' 
            AND column_name = 'updated_at'
        )";
        
        $updatedAtStmt = $conn->prepare($updatedAtCheck);
        $updatedAtStmt->execute();
        $hasUpdatedAt = $updatedAtStmt->fetchColumn();
        
        // Use appropriate SQL based on column existence
        if ($hasUpdatedAt) {
            $sql = "UPDATE job_applications SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id IN ($idPlaceholders)";
        } else {
            $sql = "UPDATE job_applications SET status = ? WHERE id IN ($idPlaceholders)";
        }
        
        $params = array_merge([$status], $applicationIds);
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['success' => true, 'message' => 'Applications updated successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Get applications data for the admin panel
function getApplications($limit = 10, $offset = 0, $filters = []) {
    try {
        $conn = getDbConnection();
        
        $whereConditions = [];
        $params = [];
        
        // Add filter conditions
        if (!empty($filters['status'])) {
            $whereConditions[] = "a.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['job_id'])) {
            $whereConditions[] = "a.job_id = ?";
            $params[] = $filters['job_id'];
        }
        
        // Build WHERE clause
        $whereClause = "";
        if (!empty($whereConditions)) {
            $whereClause = "WHERE " . implode(" AND ", $whereConditions);
        }
        
        // Determine sort order - Default to newest first
        $orderBy = "ORDER BY a.created_at DESC";
        
        // Debug log - comment out in production
        error_log("Fetching applications with filters: " . json_encode($filters));
        
        // Add pagination parameters
        $params[] = $limit;
        $params[] = $offset;
        
        // Main query for job_applications table
        $sql = "
            SELECT a.*, 
                  a.position as job_title,
                  a.resume_path as resume_url
            FROM job_applications a
            $whereClause
            $orderBy
            LIMIT ? OFFSET ?
        ";
        
        error_log("SQL Query: $sql");
        error_log("Params: " . json_encode($params));
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process resume paths to full URLs
        foreach ($applications as &$application) {
            if (!empty($application['resume_url'])) {
                // If resume_url doesn't already have a full URL format
                if (strpos($application['resume_url'], 'http') !== 0) {
                    // Construct the Supabase storage URL
                    $fileName = basename($application['resume_url']);
                    $application['resume_url'] = getSupabaseStorageUrl('resumes', $fileName);
                }
            }
        }
        
        // Get total count for pagination
        $countSql = "
            SELECT COUNT(*) as total 
            FROM job_applications a
            $whereClause
        ";
        
        $countStmt = $conn->prepare($countSql);
        $countParams = array_slice($params, 0, -2); // Remove limit and offset
        $countStmt->execute($countParams);
        $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Log the results
        error_log("Retrieved " . count($applications) . " applications out of $totalCount total");
        
        // If no applications found but should exist in the database, check tables
        if (count($applications) == 0) {
            // Check if job_applications table exists and has data
            $checkTablesql = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'";
            $tableStmt = $conn->prepare($checkTablesql);
            $tableStmt->execute();
            $tables = $tableStmt->fetchAll(PDO::FETCH_COLUMN);
            error_log("Available tables: " . implode(", ", $tables));
            
            if (in_array('job_applications', $tables)) {
                $countAllSql = "SELECT COUNT(*) FROM job_applications";
                $countAllStmt = $conn->prepare($countAllSql);
                $countAllStmt->execute();
                $allCount = $countAllStmt->fetchColumn();
                error_log("Total records in job_applications table: $allCount");
                
                if ($allCount > 0) {
                    // Table exists and has data, dump sample for debugging
                    $sampleSql = "SELECT * FROM job_applications LIMIT 1";
                    $sampleStmt = $conn->prepare($sampleSql);
                    $sampleStmt->execute();
                    $sample = $sampleStmt->fetch(PDO::FETCH_ASSOC);
                    error_log("Sample application record: " . json_encode($sample));
                }
            }
        }
        
        return [
            'applications' => $applications,
            'total' => $totalCount
        ];
    } catch (PDOException $e) {
        error_log('Error fetching applications: ' . $e->getMessage());
        error_log('SQL state: ' . $e->getCode());
        return [
            'applications' => [],
            'total' => 0
        ];
    }
}

// Get all job positions for the filter dropdown
function getJobsForFilter() {
    try {
        $conn = getDbConnection();
        // Get distinct positions from job_applications
        $sql = "SELECT DISTINCT position FROM job_applications ORDER BY position ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $positions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Format to match expected structure
        $jobs = [];
        foreach ($positions as $position) {
            $jobs[] = [
                'id' => $position, // Use position as ID for filtering
                'title' => $position
            ];
        }
        
        return $jobs;
    } catch (PDOException $e) {
        error_log('Error fetching jobs: ' . $e->getMessage());
        return [];
    }
}

/**
 * Generate a Supabase storage URL for a file
 * @param string $bucket The storage bucket name
 * @param string $fileName The file name
 * @return string The complete Supabase storage URL
 */
function getSupabaseStorageUrl($bucket, $fileName) {
    global $config;
    
    // Extract project ID from the database connection string
    $projectId = '';
    if (isset($config['db']['user'])) {
        $parts = explode('.', $config['db']['user']);
        if (count($parts) > 1) {
            $projectId = $parts[1];
        }
    }
    
    // Construct the storage URL
    $storageUrl = "https://ohgmrgsovsgbrbyuiwfx.supabase.co/storage/v1/object/public/{$bucket}/{$fileName}";
    
    return $storageUrl;
}

/**
 * Create a proxy URL for PDF viewing that doesn't trigger downloads
 * @param string $originalUrl The original Supabase URL
 * @return string A URL that can be used for embedding PDFs
 */
function createPdfProxyUrl($originalUrl) {
    // Use our own proxy endpoint to serve the PDF with the right headers
    return "admin.php?action=pdf_proxy&url=" . urlencode($originalUrl);
}

// Get statistics for dashboard
function getApplicationStats() {
    try {
        $conn = getDbConnection();
        
        // Get counts per status
        $sql = "
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN status = 'reviewed' THEN 1 END) as reviewed,
                COUNT(CASE WHEN status = 'interview' THEN 1 END) as interview,
                COUNT(CASE WHEN status = 'hired' THEN 1 END) as hired,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected
            FROM job_applications
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If no status fields exist in job_applications, check the table structure
        if ($result['total'] > 0 && 
            $result['pending'] === null && 
            $result['reviewed'] === null && 
            $result['interview'] === null) {
            
            // Get table structure
            $structureSql = "SELECT column_name FROM information_schema.columns WHERE table_name = 'job_applications'";
            $structureStmt = $conn->prepare($structureSql);
            $structureStmt->execute();
            $columns = $structureStmt->fetchAll(PDO::FETCH_COLUMN);
            error_log("job_applications columns: " . implode(", ", $columns));
            
            // Default to showing counts by auto-calculated status
            // In case there's no explicit status column
            if (!in_array('status', $columns)) {
                return [
                    'total' => $result['total'],
                    'pending' => $result['total'], // Treat all as pending by default
                    'reviewed' => 0,
                    'interview' => 0,
                    'hired' => 0,
                    'rejected' => 0
                ];
            }
        }
        
        return $result;
    } catch (PDOException $e) {
        error_log('Error fetching stats: ' . $e->getMessage());
        return [
            'total' => 0,
            'pending' => 0,
            'reviewed' => 0,
            'interview' => 0,
            'hired' => 0,
            'rejected' => 0
        ];
    }
}

// Initialize variables for the page
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$filters = [];

// Removed filter parameters from URL

// Get applications data for the initial load
$applicationsData = getApplications($limit, ($currentPage - 1) * $limit, $filters);
$applications = $applicationsData['applications'];
$totalApplications = $applicationsData['total'];

// Always ensure the job_applications table has the required columns
ensureApplicationTableExists();

// Get jobs for the filter dropdown
$jobs = getJobsForFilter();

// Get stats for the dashboard
$stats = getApplicationStats();

// process_job.php (Job insertion)

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_job') {
    $jobTitle = $_POST['job-title'];
    $jobDepartment = $_POST['job-department'];
    $jobPostedDate = $_POST['job-posted-date'];
    $jobStatus = $_POST['job-status'];
    $jobDescription = $_POST['job-description'];

    try {
        $conn = getDbConnection();
        $uuid = generateUUID();
        $sql = "INSERT INTO jobs (id, title, department, posted_date, status, description) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$uuid, $jobTitle, $jobDepartment, $jobPostedDate, $jobStatus, $jobDescription]);
        echo json_encode(['success' => true, 'message' => 'Job added successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_job') {
    $jobId = $_POST['jobId'];

    try {
        $conn = getDbConnection();
        $sql = "DELETE FROM jobs WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$jobId]);
        echo json_encode(['success' => true, 'message' => 'Job deleted successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_job') {
    $jobId = $_POST['jobId'];
    $jobTitle = $_POST['job-title'];
    $jobDepartment = $_POST['job-department'];
    $jobPostedDate = $_POST['job-posted-date'];
    $jobStatus = $_POST['job-status'];
    $jobDescription = $_POST['job-description'];

    try{
        $conn = getDbConnection();
        $sql = "UPDATE jobs SET title = ?, department = ?, posted_date = ?, status = ?, description = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$jobTitle, $jobDepartment, $jobPostedDate, $jobStatus, $jobDescription, $jobId]);
        echo json_encode(['success' => true, 'message' => 'Job updated successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

/**
 * Check if the job_applications table exists in the database and ensure it has necessary columns
 */
function ensureApplicationTableExists() {
    try {
        $conn = getDbConnection();
        
        // Check if table exists
        $sql = "SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'job_applications'
        )";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $tableExists = $stmt->fetchColumn();
        
        if (!$tableExists) {
            error_log("job_applications table does not exist");
            return false;
        } else {
            // Check required columns
            $requiredColumns = [
                'status' => "VARCHAR(50) DEFAULT 'pending'",
                'notes' => "TEXT DEFAULT ''",
                'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
            ];
            
            $columnChecks = [];
            $missingColumns = [];
            
            // Check each required column
            foreach ($requiredColumns as $column => $definition) {
                $columnCheck = "SELECT EXISTS (
                    SELECT FROM information_schema.columns 
                    WHERE table_name = 'job_applications' 
                    AND column_name = '$column'
                )";
                
                $columnStmt = $conn->prepare($columnCheck);
                $columnStmt->execute();
                $hasColumn = $columnStmt->fetchColumn();
                
                $columnChecks[$column] = $hasColumn;
                
                if (!$hasColumn) {
                    $missingColumns[$column] = $definition;
                }
            }
            
            // Add any missing columns
            foreach ($missingColumns as $column => $definition) {
                try {
                    error_log("Adding $column column to job_applications table");
                    $conn->exec("ALTER TABLE job_applications ADD COLUMN $column $definition");
                } catch (PDOException $e) {
                    error_log("Could not add $column column: " . $e->getMessage());
                }
            }
            
            return true;
        }
    } catch (PDOException $e) {
        error_log("Error checking job_applications table: " . $e->getMessage());
        return false;
    }
}

// Handle application details request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'get_application_details') {
    if (!isset($_POST['application_id']) || empty($_POST['application_id'])) {
        echo json_encode(['success' => false, 'message' => 'Application ID is required']);
        exit;
    }
    
    $applicationId = $_POST['application_id'];
    
    try {
        $conn = getDbConnection();
        $sql = "SELECT * FROM job_applications WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$applicationId]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$application) {
            echo json_encode(['success' => false, 'message' => 'Application not found']);
            exit;
        }
        
        // Process resume path to create full URL if available
        if (!empty($application['resume_path'])) {
            $resumePath = $application['resume_path'];
            
            // Check if resume_path already contains a URL
            if (strpos($resumePath, 'http') === 0) {
                $application['resume_url'] = $resumePath;
            } else {
                // Extract just the filename
                $fileName = basename($resumePath);
                $application['resume_url'] = getSupabaseStorageUrl('resumes', $fileName);
            }
        }
        
        echo json_encode(['success' => true, 'application' => $application]);
    } catch (PDOException $e) {
        error_log("Error fetching application details: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle view resume inline request
if (isset($_GET['action']) && $_GET['action'] == 'view_resume_inline' && isset($_GET['id'])) {
    try {
        $conn = getDbConnection();
        $sql = "SELECT resume_path, name FROM job_applications WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$_GET['id']]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$application || empty($application['resume_path'])) {
            echo "No resume found for this application.";
            exit;
        }
        
        $resumePath = $application['resume_path'];
        $applicantName = $application['name'];
        
        // Check if resume_path already contains a URL
        if (strpos($resumePath, 'http') === 0) {
            $resumeUrl = $resumePath;
        } else {
            // Extract just the filename if it's a path
            $fileName = basename($resumePath);
            $resumeUrl = getSupabaseStorageUrl('resumes', $fileName);
        }
        
        // Create a proxy URL for the PDF to ensure proper embedding
        $proxyUrl = createPdfProxyUrl($resumeUrl);
        
        // Log the URL for debugging
        error_log("Creating PDF proxy for URL: " . $resumeUrl);
        
        // Output proper Content-Type header
        header('Content-Type: text/html; charset=utf-8');
        
        // Create a simple HTML wrapper that embeds the PDF in an iframe
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Resume: '.htmlspecialchars($applicantName).'</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body, html { margin: 0; padding: 0; height: 100%; overflow: hidden; }
                .container { display: flex; flex-direction: column; height: 100%; }
                .toolbar { background: #2c3e50; color: white; padding: 10px; display: flex; justify-content: space-between; align-items: center; }
                .title { margin: 0; font-size: 18px; }
                .actions { display: flex; gap: 10px; }
                .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; color: white; font-size: 14px; }
                .btn-download { background-color: #27ae60; }
                .btn-back { background-color: #3498db; }
                iframe { flex: 1; width: 100%; border: none; }
                @media (max-width: 768px) {
                    .toolbar { flex-direction: column; gap: 10px; }
                    .actions { width: 100%; justify-content: center; }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="toolbar">
                    <h1 class="title">Resume: '.htmlspecialchars($applicantName).'</h1>
                    <div class="actions">
                        <a href="admin.php?action=download_resume&id='.$_GET['id'].'" class="btn btn-download">Download</a>
                        <a href="admin.php" class="btn btn-back">Back to Admin</a>
                    </div>
                </div>
                <iframe src="'.$proxyUrl.'" width="100%" height="100%"></iframe>
            </div>
        </body>
        </html>';
        exit;
    } catch (PDOException $e) {
        echo "Error retrieving resume: " . $e->getMessage();
        exit;
    }
}

// Handle get resume URL request
if (isset($_GET['action']) && $_GET['action'] == 'get_resume_url' && isset($_GET['id'])) {
    try {
        $conn = getDbConnection();
        $sql = "SELECT resume_path, name FROM job_applications WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$_GET['id']]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$application || empty($application['resume_path'])) {
            echo json_encode(['success' => false, 'message' => 'No resume found']);
            exit;
        }
        
        $resumePath = $application['resume_path'];
        
        // Check if resume_path already contains a URL
        if (strpos($resumePath, 'http') === 0) {
            $resumeUrl = $resumePath;
        } else {
            // Extract just the filename
            $fileName = basename($resumePath);
            $resumeUrl = getSupabaseStorageUrl('resumes', $fileName);
        }
        
        echo json_encode(['success' => true, 'resume_url' => $resumeUrl, 'applicant_name' => $application['name']]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle download resume request
if (isset($_GET['action']) && $_GET['action'] == 'download_resume' && isset($_GET['id'])) {
    try {
        $conn = getDbConnection();
        $sql = "SELECT resume_path, name FROM job_applications WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$_GET['id']]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$application || empty($application['resume_path'])) {
            echo "No resume found for this application.";
            exit;
        }
        
        $resumePath = $application['resume_path'];
        $applicantName = preg_replace('/[^a-zA-Z0-9]/', '_', $application['name']);
        $resumeFilename = $applicantName . '_resume.pdf';
        
        // Check if resume_path already contains a URL
        if (strpos($resumePath, 'http') === 0) {
            $resumeUrl = $resumePath;
        } else {
            // Extract just the filename
            $fileName = basename($resumePath);
            $resumeUrl = getSupabaseStorageUrl('resumes', $fileName);
        }
        
        // For direct download, we'll redirect to the URL with attachment disposition
        header('Content-Disposition: attachment; filename="' . $resumeFilename . '"');
        header("Location: $resumeUrl");
        exit;
    } catch (PDOException $e) {
        echo "Error retrieving resume: " . $e->getMessage();
        exit;
    }
}

// Handle delete resume request
if (isset($_GET['action']) && $_GET['action'] == 'delete_resume' && isset($_GET['id'])) {
    try {
        $conn = getDbConnection();
        
        // First retrieve the application to check if it has a resume
        $sql = "SELECT resume_path FROM job_applications WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$_GET['id']]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$application || empty($application['resume_path'])) {
            echo json_encode(['success' => false, 'message' => 'No resume found for this application.']);
            exit;
        }
        
        // Update the job application to remove the resume reference
        $updateSql = "UPDATE job_applications SET resume_path = NULL WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([$_GET['id']]);
        
        echo json_encode(['success' => true, 'message' => 'Resume deleted successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting resume: ' . $e->getMessage()]);
    }
    exit;
}

// Handle delete application request
if (isset($_GET['action']) && $_GET['action'] == 'delete_application' && isset($_GET['id'])) {
    try {
        $conn = getDbConnection();
        
        // Delete the job application record
        $sql = "DELETE FROM job_applications WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$_GET['id']]);
        
        // Check if any rows were affected
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Application deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Application not found or already deleted.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting application: ' . $e->getMessage()]);
    }
    exit;
}

// Add JavaScript to handle viewing resumes
function addResumeViewingFunctionality() {
?>
<script>
function viewResume(applicationId) {
    // Open the resume in a new tab/window
    window.open('admin.php?action=view_resume_inline&id=' + applicationId, '_blank');
}

function downloadResume(applicationId) {
    // Directly download the resume
    fetch('admin.php?action=get_resume_url&id=' + applicationId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.resume_url) {
                const link = document.createElement('a');
                link.href = data.resume_url;
                link.download = (data.applicant_name || 'resume') + '.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } else {
                alert('No resume found or unable to retrieve resume URL.');
            }
        })
        .catch(error => {
            console.error('Error fetching resume URL:', error);
            alert('Error retrieving resume URL. Please try again later.');
        });
}

function viewResumeModal(applicationId) {
    // First get the resume URL
    fetch('admin.php?action=get_resume_url&id=' + applicationId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.resume_url) {
                // Create a modal to display the resume
                const modal = document.createElement('div');
                modal.className = 'resume-modal';
                
                // Add header with buttons
                const header = document.createElement('div');
                header.className = 'resume-modal-header';
                
                // Add title
                const title = document.createElement('h4');
                title.textContent = data.applicant_name ? `Resume: ${data.applicant_name}` : 'Resume Preview';
                title.className = 'resume-modal-title';
                
                // Add buttons container
                const btnContainer = document.createElement('div');
                
                // Add download button
                const downloadButton = document.createElement('button');
                downloadButton.textContent = 'Download';
                downloadButton.className = 'btn btn-success btn-sm me-2';
                downloadButton.onclick = function() {
                    downloadResume(applicationId);
                };
                
                // Add open in new tab button
                const openButton = document.createElement('button');
                openButton.textContent = 'Open in New Tab';
                openButton.className = 'btn btn-primary btn-sm me-2';
                openButton.onclick = function() {
                    viewResume(applicationId);
                };
                
                // Add delete button
                const deleteButton = document.createElement('button');
                deleteButton.textContent = 'Delete Resume';
                deleteButton.className = 'btn btn-danger btn-sm me-2';
                deleteButton.onclick = function() {
                    if (confirm('Are you sure you want to delete this resume? This action cannot be undone.')) {
                        deleteResume(applicationId, modal);
                    }
                };
                
                // Add close button
                const closeButton = document.createElement('button');
                closeButton.textContent = 'Close';
                closeButton.className = 'btn btn-secondary btn-sm';
                closeButton.onclick = function() {
                    document.body.removeChild(modal);
                };
                
                btnContainer.appendChild(downloadButton);
                btnContainer.appendChild(openButton);
                btnContainer.appendChild(deleteButton);
                btnContainer.appendChild(closeButton);
                header.appendChild(title);
                header.appendChild(btnContainer);
                
                // Create iframe container
                const iframeContainer = document.createElement('div');
                iframeContainer.className = 'resume-modal-iframe';
                
                // Create a proxy URL for the PDF to ensure proper embedding
                const proxyUrl = 'admin.php?action=pdf_proxy&url=' + encodeURIComponent(data.resume_url);
                
                // Create iframe with the proxy URL
                const iframe = document.createElement('iframe');
                iframe.src = proxyUrl;
                iframe.className = 'resume-iframe';
                
                iframeContainer.appendChild(iframe);
                modal.appendChild(header);
                modal.appendChild(iframeContainer);
                document.body.appendChild(modal);
                
                // Add keyboard event to close on escape
                document.addEventListener('keydown', function escapeHandler(e) {
                    if (e.key === 'Escape') {
                        document.body.removeChild(modal);
                        document.removeEventListener('keydown', escapeHandler);
                    }
                });
            } else {
                alert('No resume found or unable to retrieve resume URL.');
            }
        })
        .catch(error => {
            console.error('Error fetching resume URL:', error);
            alert('Error retrieving resume URL. Please try again later.');
        });
}

function deleteResume(applicationId, modalElement) {
    // Make the delete request
    fetch('admin.php?action=delete_resume&id=' + applicationId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showToast(data.message, 'success');
                
                // Remove modal if provided
                if (modalElement && document.body.contains(modalElement)) {
                    document.body.removeChild(modalElement);
                }
                
                // Update UI to reflect deleted resume
                const resumeButtons = document.querySelectorAll(`.view-resume-modal[data-id="${applicationId}"]`);
                const downloadButtons = document.querySelectorAll(`a[href="admin.php?action=download_resume&id=${applicationId}"]`);
                
                // Replace resume buttons with "No resume" text
                resumeButtons.forEach(button => {
                    const cell = button.closest('td');
                    if (cell) {
                        // Remove the button group
                        const btnGroup = button.closest('.btn-group');
                        if (btnGroup) {
                            cell.removeChild(btnGroup);
                        }
                        
                        // Add "No resume" text
                        const noResumeSpan = document.createElement('span');
                        noResumeSpan.className = 'text-muted';
                        noResumeSpan.textContent = 'No resume';
                        cell.appendChild(noResumeSpan);
                    }
                });
                
                // If we're in the resume review section, update that too
                const resumeItem = document.querySelector(`.resume-application-item[data-id="${applicationId}"]`);
                if (resumeItem) {
                    resumeItem.setAttribute('data-resume', '');
                    
                    // If this is the active item, update the viewer
                    if (resumeItem.classList.contains('active')) {
                        const viewer = document.getElementById('resume-viewer');
                        if (viewer) {
                            viewer.innerHTML = `
                                <div class="text-center py-5">
                                    <i class="bx bx-file" style="font-size: 3rem;"></i>
                                    <p class="mt-3">No resume available for this application</p>
                                </div>
                            `;
                        }
                    }
                }
                
                // Reload the page after a delay to ensure everything is updated
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // Show error message
                showToast(data.message || 'Failed to delete resume', 'danger');
            }
        })
        .catch(error => {
            console.error('Error deleting resume:', error);
            showToast('Error deleting resume. Please try again later.', 'danger');
        });
}

function deleteApplication(applicationId, modalElement) {
    if (!confirm('Are you sure you want to delete this application? This will remove all application data permanently and cannot be undone.')) {
        return;
    }
    
    // Make the delete request
    fetch('admin.php?action=delete_application&id=' + applicationId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showToast(data.message, 'success');
                
                // Close the modal if provided
                if (modalElement) {
                    const bsModal = bootstrap.Modal.getInstance(modalElement);
                    if (bsModal) {
                        bsModal.hide();
                    }
                }
                
                // Remove the application row from the table
                const applicationRow = document.querySelector(`tr .application-checkbox[value="${applicationId}"]`)?.closest('tr');
                if (applicationRow) {
                    applicationRow.style.backgroundColor = '#ffdddd';
                    applicationRow.style.opacity = '0.5';
                    applicationRow.style.transition = 'all 0.5s ease';
                    
                    // After a short delay, remove the row completely
                    setTimeout(() => {
                        applicationRow.remove();
                        
                        // Check if there are no applications left
                        const tbody = document.getElementById('applications-list');
                        if (tbody && tbody.children.length === 0) {
                            const noApplicationsRow = document.createElement('tr');
                            noApplicationsRow.innerHTML = '<td colspan="8" class="text-center">No applications found</td>';
                            tbody.appendChild(noApplicationsRow);
                        }
                    }, 500);
                }
                
                // If we're in the resume review section, remove that item too
                const resumeItem = document.querySelector(`.resume-application-item[data-id="${applicationId}"]`);
                if (resumeItem) {
                    resumeItem.remove();
                    
                    // Check if there are no applications left in the resume list
                    const resumeList = document.getElementById('resume-applications-list');
                    if (resumeList && resumeList.children.length === 0) {
                        const noApplicationsItem = document.createElement('div');
                        noApplicationsItem.className = 'list-group-item text-center';
                        noApplicationsItem.innerHTML = '<p class="mb-0">No applications available</p>';
                        resumeList.appendChild(noApplicationsItem);
                    }
                }
                
                // Update application counts
                updateApplicationCounts();
                
                // Reload the page after a delay to ensure everything is updated
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // Show error message
                showToast(data.message || 'Failed to delete application', 'danger');
            }
        })
        .catch(error => {
            console.error('Error deleting application:', error);
            showToast('Error deleting application. Please try again later.', 'danger');
        });
}

// Helper function to update application counts after deletion
function updateApplicationCounts() {
    // Update total count in pagination info
    const showingInfo = document.querySelector('.showing-info');
    if (showingInfo) {
        const countText = showingInfo.textContent;
        const totalMatch = countText.match(/of\s+(\d+)\s+applications/);
        if (totalMatch && totalMatch[1]) {
            const currentTotal = parseInt(totalMatch[1]);
            const newTotal = Math.max(0, currentTotal - 1);
            showingInfo.textContent = countText.replace(/of\s+\d+\s+applications/, `of ${newTotal} applications`);
        }
    }
    
    // Update counts in the dashboard
    const statElements = document.querySelectorAll('.application-stat-count');
    statElements.forEach(el => {
        const category = el.getAttribute('data-category');
        if (category === 'total') {
            const currentCount = parseInt(el.textContent);
            if (!isNaN(currentCount)) {
                el.textContent = Math.max(0, currentCount - 1);
            }
        }
    });
}
</script>
<?php
}

// Add JavaScript for application view and status updates
function addApplicationScripts() {
?>
<script>
// View application details in modal
function viewApplicationDetails(applicationId) {
    // Show loading spinner
    document.getElementById('applicationDetailsModalBody').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('applicationDetailsModal'));
    modal.show();
    
    // Fetch application details from server
    const formData = new FormData();
    formData.append('action', 'get_application_details');
    formData.append('application_id', applicationId);
    
    fetch('admin.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("HTTP error! Status: " + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.application) {
            const app = data.application;
            
            // Update modal title
            document.getElementById('applicationDetailsModalLabel').textContent = 
                `Application: ${app.name || 'No Name'} - ${app.position || 'No Position'}`;
            
            // Create content for modal body with Bootstrap styling
            let content = `
            <div class="container-fluid p-0">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Personal Information</h5>
                        <p><strong>Name:</strong> ${app.name || 'Not provided'}</p>
                        <p><strong>Email:</strong> ${app.email || 'Not provided'}</p>
                        <p><strong>Phone:</strong> ${app.phone || 'Not provided'}</p>
                    </div>
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Application Details</h5>
                        <p><strong>Position:</strong> ${app.position || 'Not specified'}</p>
                        <p><strong>Status:</strong> <span class="badge ${getStatusBadgeClass(app.status || 'pending')}">${app.status || 'pending'}</span></p>
                        <p><strong>Date Applied:</strong> ${new Date(app.created_at).toLocaleDateString()}</p>
                    </div>
                </div>
            `;
            
            // Add resume if available
            if (app.resume_path || app.resume_url) {
                const resumeUrl = app.resume_url || app.resume_path;
                content += `
                <div class="row mt-3">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Resume</h5>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                            <button class="btn btn-primary" onclick="viewResumeModal('${applicationId}')">
                                <i class="bi bi-eye"></i> View Resume
                            </button>
                            <a href="admin.php?action=download_resume&id=${applicationId}" class="btn btn-success">
                                <i class="bi bi-download"></i> Download Resume
                            </a>
                            <button class="btn btn-danger" onclick="confirmDeleteResume('${applicationId}')">
                                <i class="bi bi-trash"></i> Delete Resume
                            </button>
                        </div>
                    </div>
                </div>
                `;
            }
            
            // Add notes section
            content += `
            <div class="row mt-3">
                <div class="col-12">
                    <h5 class="border-bottom pb-2">Notes</h5>
                    <form id="notesForm">
                        <input type="hidden" name="application_id" value="${applicationId}">
                        <div class="mb-3">
                            <textarea class="form-control" id="applicationNotes" rows="3">${app.notes || ''}</textarea>
                        </div>
                    </form>
                </div>
            </div>
            `;
            
            // Add status update buttons
            content += `
            <div class="row mt-3">
                <div class="col-12">
                    <h5 class="border-bottom pb-2">Update Status</h5>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button class="btn ${app.status === 'pending' ? 'btn-primary' : 'btn-outline-primary'}" 
                                onclick="updateApplicationStatus('${applicationId}', 'pending')">
                            Pending
                        </button>
                        <button class="btn ${app.status === 'reviewed' ? 'btn-primary' : 'btn-outline-primary'}" 
                                onclick="updateApplicationStatus('${applicationId}', 'reviewed')">
                            Reviewed
                        </button>
                        <button class="btn ${app.status === 'interview' ? 'btn-primary' : 'btn-outline-primary'}" 
                                onclick="updateApplicationStatus('${applicationId}', 'interview')">
                            Interview
                        </button>
                        <button class="btn ${app.status === 'hired' ? 'btn-success' : 'btn-outline-success'}" 
                                onclick="updateApplicationStatus('${applicationId}', 'hired')">
                            Hired
                        </button>
                        <button class="btn ${app.status === 'rejected' ? 'btn-danger' : 'btn-outline-danger'}" 
                                onclick="updateApplicationStatus('${applicationId}', 'rejected')">
                            Rejected
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Delete Application Button -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="border-top pt-3 d-flex justify-content-end">
                        <button class="btn btn-outline-danger" 
                                onclick="deleteApplication('${applicationId}', document.getElementById('applicationDetailsModal'))">
                            <i class="bi bi-trash"></i> Delete Application
                        </button>
                    </div>
                </div>
            </div>
            </div>
            `;
            
            // Update modal content
            document.getElementById('applicationDetailsModalBody').innerHTML = content;
            
        } else {
            document.getElementById('applicationDetailsModalBody').innerHTML = 
                '<div class="alert alert-danger">Failed to load application details: ' + 
                (data.message || 'Unknown error') + '</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('applicationDetailsModalBody').innerHTML = 
            '<div class="alert alert-danger">Error loading application details. Please try again later.</div>';
    });
}

// Helper function for confirming resume deletion
function confirmDeleteResume(applicationId) {
    if (confirm('Are you sure you want to delete this resume? This action cannot be undone.')) {
        // Close the application details modal
        const appModal = bootstrap.Modal.getInstance(document.getElementById('applicationDetailsModal'));
        if (appModal) {
            appModal.hide();
        }
        
        // Delete the resume
        deleteResume(applicationId);
    }
}

// Helper function for status badge styling
function getStatusBadgeClass(status) {
    switch(status.toLowerCase()) {
        case 'pending': return 'bg-secondary';
        case 'reviewed': return 'bg-info';
        case 'interview': return 'bg-primary';
        case 'hired': return 'bg-success';
        case 'rejected': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

// Update application status
function updateApplicationStatus(applicationId, status) {
    const notes = document.getElementById('applicationNotes')?.value || '';
    
    // Show loading spinner
    const modalBody = document.getElementById('applicationDetailsModalBody');
    const originalContent = modalBody?.innerHTML || '';
    
    if (modalBody) {
        modalBody.innerHTML = '<div class="text-center mt-4 mb-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Updating status...</p></div>';
    }
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'update_application_status');
    formData.append('application_id', applicationId);
    formData.append('status', status);
    formData.append('notes', notes);
    
    fetch('admin.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showToast('Status updated successfully!', 'success');
            
            // Refresh the application list
            if (typeof loadApplications === 'function') {
                loadApplications();
            } else {
                // Fallback to reload page
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
            
            if (modalBody) {
                // Show success message
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success';
                successAlert.innerHTML = 'Status updated successfully!';
                
                // Insert alert before the content
                modalBody.innerHTML = '';
                modalBody.appendChild(successAlert);
                modalBody.innerHTML += originalContent;
                
                // Update the status buttons in the modal
                const buttons = modalBody.querySelectorAll('button[onclick^="updateApplicationStatus"]');
                buttons.forEach(button => {
                    const buttonStatus = button.onclick.toString().match(/updateApplicationStatus\('.+?', '(.+?)'\)/)[1];
                    if (buttonStatus === status) {
                        button.classList.remove('btn-outline-primary', 'btn-outline-success', 'btn-outline-danger');
                        if (status === 'hired') {
                            button.classList.add('btn-success');
                        } else if (status === 'rejected') {
                            button.classList.add('btn-danger');
                        } else {
                            button.classList.add('btn-primary');
                        }
                    } else {
                        button.classList.remove('btn-primary', 'btn-success', 'btn-danger');
                        if (buttonStatus === 'hired') {
                            button.classList.add('btn-outline-success');
                        } else if (buttonStatus === 'rejected') {
                            button.classList.add('btn-outline-danger');
                        } else {
                            button.classList.add('btn-outline-primary');
                        }
                    }
                });
                
                // Update status badge
                const statusBadge = modalBody.querySelector('span.badge');
                if (statusBadge) {
                    statusBadge.className = 'badge ' + getStatusBadgeClass(status);
                    statusBadge.textContent = status;
                }
                
                // Hide modal after success
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('applicationDetailsModal'));
                    if (modal) modal.hide();
                }, 1500);
            }
        } else {
            // Show error message
            showToast('Error updating status: ' + (data.message || 'Unknown error'), 'danger');
            
            if (modalBody) {
                modalBody.innerHTML = originalContent;
                
                const errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-danger';
                errorAlert.innerHTML = 'Error updating status: ' + (data.message || 'Unknown error');
                
                modalBody.prepend(errorAlert);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating status. Please try again.', 'danger');
        
        if (modalBody) {
            modalBody.innerHTML = originalContent;
            
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger';
            errorAlert.innerHTML = 'Error updating status. Please try again.';
            
            modalBody.prepend(errorAlert);
        }
    });
}
</script>
<?php
}

// Add all required UI components to the page before closing PHP
function addApplicationModalUi() {
?>
<!-- Application Details Modal -->
<div class="modal fade" id="applicationDetailsModal" tabindex="-1" aria-labelledby="applicationDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="applicationDetailsModalLabel">Application Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="applicationDetailsModalBody">
        <div class="text-center">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php
}

/**
 * Renders the contact form submissions in a table
 */
function renderContactSubmissions($pdo) {
    $sql = "SELECT * FROM contact_forms ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<div class="container mt-4">';
    // echo '<h2 class="mb-4">Contact Form Submissions</h2>';
    
    if (count($contacts) === 0) {
        echo '<div class="alert alert-info">No contact form submissions found.</div>';
    } else {
        echo '<div class="table-responsive">';
        echo '<table class="table table-hover align-middle">';
        echo '<thead class="table-light"><tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 12%;">Name</th>
                <th style="width: 15%;">Email</th>
                <th style="width: 10%;">Phone</th>
                <th style="width: 12%;">Company</th>
                <th style="width: 10%;">Industry</th>
                <th style="width: 12%;">Date</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 14%;">Actions</th>
              </tr></thead>';
        echo '<tbody>';
        
        foreach ($contacts as $contact) {
            $date = new DateTime($contact['created_at']);
            $formattedDate = $date->format('M d, Y g:i A');
            
            $statusClass = '';
            switch ($contact['status']) {
                case 'new':
                    $statusClass = 'bg-primary';
                    break;
                case 'in-progress':
                    $statusClass = 'bg-warning text-dark';
                    break;
                case 'completed':
                    $statusClass = 'bg-success';
                    break;
                default:
                    $statusClass = 'bg-secondary';
            }
            
            echo '<tr>';
            echo '<td>' . htmlspecialchars($contact['id']) . '</td>';
            echo '<td>' . htmlspecialchars($contact['name']) . '</td>';
            echo '<td><small>' . htmlspecialchars($contact['email']) . '</small></td>';
            echo '<td>' . htmlspecialchars($contact['phone']) . '</td>';
            echo '<td>' . htmlspecialchars($contact['company_name']) . '</td>';
            echo '<td>' . htmlspecialchars($contact['industry']) . '</td>';
            echo '<td><small>' . $formattedDate . '</small></td>';
            echo '<td><span class="badge ' . $statusClass . '">' . htmlspecialchars($contact['status']) . '</span></td>';
            echo '<td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-info view-contact" data-id="' . $contact['id'] . '" title="View Details">
                            <i class="bx bx-show"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="statusDropdown' . $contact['id'] . '" data-bs-toggle="dropdown" aria-expanded="false" title="Update Status">
                            <i class="bx bx-edit"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="statusDropdown' . $contact['id'] . '">
                            <li><a class="dropdown-item update-status" data-id="' . $contact['id'] . '" data-status="new" href="#"><i class="bx bx-circle text-primary me-2"></i>Mark as New</a></li>
                            <li><a class="dropdown-item update-status" data-id="' . $contact['id'] . '" data-status="in-progress" href="#"><i class="bx bx-time text-warning me-2"></i>Mark as In Progress</a></li>
                            <li><a class="dropdown-item update-status" data-id="' . $contact['id'] . '" data-status="completed" href="#"><i class="bx bx-check-circle text-success me-2"></i>Mark as Completed</a></li>
                        </ul>
                    </div>
                  </td>';
            echo '</tr>';
        }
        
        echo '</tbody></table></div>';
    }
    echo '</div>';

    // Modal for viewing contact details
    echo '
    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalLabel">Contact Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contactModalBody">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>';

    // JavaScript for contact actions
    echo '
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // View contact details
        document.querySelectorAll(".view-contact").forEach(button => {
            button.addEventListener("click", function() {
                const contactId = this.getAttribute("data-id");
                const modal = new bootstrap.Modal(document.getElementById("contactModal"));
                modal.show();
                
                fetch("admin.php?action=view_contact_details&id=" + contactId)
                    .then(response => {
                        // Check if the response is ok
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        // Parse as JSON
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            document.getElementById("contactModalBody").innerHTML = data.html;
                        } else {
                            document.getElementById("contactModalBody").innerHTML = 
                                "<div class=\"alert alert-danger\">" + (data.message || "Unknown error") + "</div>";
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching contact details:", error);
                        document.getElementById("contactModalBody").innerHTML = 
                            "<div class=\"alert alert-danger\">Error loading contact details: " + error.message + "</div>";
                    });
            });
        });
        
        // Update contact status
        document.querySelectorAll(".update-status").forEach(link => {
            link.addEventListener("click", function(e) {
                e.preventDefault();
                const contactId = this.getAttribute("data-id");
                const newStatus = this.getAttribute("data-status");
                
                fetch("admin.php?action=update_contact_status", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: "id=" + contactId + "&status=" + newStatus
                })
                .then(response => {
                    // Check if the response is ok
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    // Parse as JSON
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Reload the page to show updated status
                        location.reload();
                    } else {
                        alert("Error updating status: " + (data.message || "Unknown error"));
                    }
                })
                .catch(error => {
                    console.error("Error updating status:", error);
                    alert("Error: " + error.message);
                });
            });
        });
    });
    </script>';
}

/**
 * Ensure the contact_forms table exists in the database
 */
function ensureContactFormsTableExists() {
    try {
        $conn = getDbConnection();
        
        // Check if table exists
        $sql = "SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_name = 'contact_forms'
        )";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $tableExists = $stmt->fetchColumn();
        
        if (!$tableExists) {
            // Create contact_forms table
            $createTableSql = "
                CREATE TABLE contact_forms (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    phone VARCHAR(50),
                    message TEXT NOT NULL,
                    company_name VARCHAR(255),
                    industry VARCHAR(50),
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                    status VARCHAR(20) DEFAULT 'new'
                )
            ";
            $conn->exec($createTableSql);
            error_log("Created contact_forms table");
        } else {
            // Check if status column exists
            $columnSql = "SELECT EXISTS (
                SELECT FROM information_schema.columns
                WHERE table_name = 'contact_forms' AND column_name = 'status'
            )";
            $stmt = $conn->prepare($columnSql);
            $stmt->execute();
            $statusColumnExists = $stmt->fetchColumn();
            
            if (!$statusColumnExists) {
                // Add status column
                $alterTableSql = "ALTER TABLE contact_forms ADD COLUMN status VARCHAR(20) DEFAULT 'new'";
                $conn->exec($alterTableSql);
                error_log("Added status column to contact_forms table");
            }
            
            // Check if ip_address and user_agent columns exist
            $ipColumnSql = "SELECT EXISTS (
                SELECT FROM information_schema.columns
                WHERE table_name = 'contact_forms' AND column_name = 'ip_address'
            )";
            $stmt = $conn->prepare($ipColumnSql);
            $stmt->execute();
            $ipColumnExists = $stmt->fetchColumn();
            
            if (!$ipColumnExists) {
                // Add ip_address column
                $alterTableSql = "ALTER TABLE contact_forms ADD COLUMN ip_address VARCHAR(45)";
                $conn->exec($alterTableSql);
                error_log("Added ip_address column to contact_forms table");
            }
            
            $userAgentColumnSql = "SELECT EXISTS (
                SELECT FROM information_schema.columns
                WHERE table_name = 'contact_forms' AND column_name = 'user_agent'
            )";
            $stmt = $conn->prepare($userAgentColumnSql);
            $stmt->execute();
            $userAgentColumnExists = $stmt->fetchColumn();
            
            if (!$userAgentColumnExists) {
                // Add user_agent column
                $alterTableSql = "ALTER TABLE contact_forms ADD COLUMN user_agent TEXT";
                $conn->exec($alterTableSql);
                error_log("Added user_agent column to contact_forms table");
            }
        }
    } catch (PDOException $e) {
        error_log("Error ensuring contact_forms table: " . $e->getMessage());
    }
}

// Add all the UI components and scripts to the page
function renderAdminPage() {
    // Note: Table existence checks (ensureApplicationTableExists, ensureContactFormsTableExists)
    // have been disabled from running on every page load to massively speed up performance.
    
    // Add resume viewing functionality
    addResumeViewingFunctionality();
    
    // Add application scripts
    addApplicationScripts();
    
    // Add modal UI components
    addApplicationModalUi();
}

// Call the main function
renderAdminPage();

// Process AJAX requests
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        // ... existing cases ...
        
        case 'view_contact_details':
            if (isset($_GET['id'])) {
                $contactId = $_GET['id'];
                try {
                    // Clean any previous output to ensure clean JSON response
                    ob_clean();
                    
                    // Set the content type header to JSON
                    header('Content-Type: application/json');
                    
                    $conn = getDbConnection();
                    $sql = "SELECT * FROM contact_forms WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':id', $contactId, PDO::PARAM_INT);
                    $stmt->execute();
                    $contact = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($contact) {
                        $date = new DateTime($contact['created_at']);
                        $formattedDate = $date->format('M d, Y g:i A');
                        
                        $html = '<div class="contact-details">';
                        $html .= '<div class="row">';
                        $html .= '<div class="col-md-6"><p><strong>Name:</strong> ' . htmlspecialchars($contact['name']) . '</p></div>';
                        $html .= '<div class="col-md-6"><p><strong>Email:</strong> <a href="mailto:' . htmlspecialchars($contact['email']) . '">' . htmlspecialchars($contact['email']) . '</a></p></div>';
                        $html .= '</div>';
                        
                        $html .= '<div class="row">';
                        $html .= '<div class="col-md-6"><p><strong>Phone:</strong> ' . htmlspecialchars($contact['phone']) . '</p></div>';
                        $html .= '<div class="col-md-6"><p><strong>Submitted:</strong> ' . $formattedDate . '</p></div>';
                        $html .= '</div>';
                        
                        $html .= '<div class="row">';
                        $html .= '<div class="col-md-6"><p><strong>Company:</strong> ' . htmlspecialchars($contact['company_name']) . '</p></div>';
                        $html .= '<div class="col-md-6"><p><strong>Industry:</strong> ' . htmlspecialchars($contact['industry']) . '</p></div>';
                        $html .= '</div>';
                        
                        if (!empty($contact['ip_address'])) {
                            $html .= '<div class="row">';
                            $html .= '<div class="col-md-6"><p><strong>IP Address:</strong> ' . htmlspecialchars($contact['ip_address']) . '</p></div>';
                            $html .= '</div>';
                        }
                        
                        $html .= '<div class="row mt-3">';
                        $html .= '<div class="col-12">';
                        $html .= '<div class="card">';
                        $html .= '<div class="card-header"><strong>Message</strong></div>';
                        $html .= '<div class="card-body">' . nl2br(htmlspecialchars($contact['message'])) . '</div>';
                        $html .= '</div>';
                        $html .= '</div>';
                        $html .= '</div>';
                        
                        $html .= '</div>';
                        
                        echo json_encode(['success' => true, 'html' => $html]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Contact not found']);
                    }
                } catch (PDOException $e) {
                    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                }
            } else {
                // Clean any previous output to ensure clean JSON response
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'No contact ID provided']);
            }
            exit;

        case 'update_contact_status':
            // Clean any previous output to ensure clean JSON response
            ob_clean();
            // Set the content type header to JSON
            header('Content-Type: application/json');
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['status'])) {
                $contactId = $_POST['id'];
                $status = $_POST['status'];
                
                // Validate status
                $validStatuses = ['new', 'in-progress', 'completed'];
                if (!in_array($status, $validStatuses)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid status']);
                    exit;
                }
                
                try {
                    $conn = getDbConnection();
                    $sql = "UPDATE contact_forms SET status = :status WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
                    $stmt->bindParam(':id', $contactId, PDO::PARAM_INT);
                    $result = $stmt->execute();
                    
                    if ($result) {
                        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
                    }
                } catch (PDOException $e) {
                    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid request']);
            }
            exit;
            
        // ... existing default case ...
    }
}
// ... existing code ...
// ?>

// <?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

// Start session to store flash messages
// session_start();

// Initialize variables - check for flash message from session
$status = $_SESSION['email_status'] ?? null;
$statusType = $_SESSION['email_status_type'] ?? null;

// Clear flash messages after reading
unset($_SESSION['email_status']);
unset($_SESSION['email_status_type']);

// Handle form submission
if(isset($_POST['submit_form'])) { 
    $to = $_POST['to']; 
    $subject = $_POST['subject']; 
    $msg = $_POST['msg']; 
    $name = $_POST['name'];
    
    $mail = new PHPMailer(true);

    try {
        //Server settings
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME');
        $mail->Password   = $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD');
        $mail->SMTPSecure = 'tls';
        $mail->Port       = $_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?? 587;

        $mail->setFrom($_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME'), 'Admin');
        $mail->addAddress($to,$name);
        $mail->addReplyTo($_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME'), 'Admin');

        // Handle file attachment
        if (!empty($_FILES['attachment']['name']) && is_uploaded_file($_FILES['attachment']['tmp_name'])) {
            $allowedTypes = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg'];
            $fileExt = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            $fileSize = $_FILES['attachment']['size'];
            
            // Validate file type and size (5MB max)
            if (!in_array($fileExt, $allowedTypes)) {
                throw new Exception('Invalid file type. Allowed: PDF, DOC, DOCX, PNG, JPG, JPEG');
            }
            
            if ($fileSize > 5 * 1024 * 1024) {
                throw new Exception('File size too large. Maximum 5MB allowed');
            }
            
            $mail->addAttachment($_FILES['attachment']['tmp_name'], $_FILES['attachment']['name']);
        }

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $msg;

        $mail->send();
        
        // Store success message in session
        $_SESSION['email_status'] = 'Message has been sent successfully!';
        $_SESSION['email_status_type'] = 'success';
        
        // Redirect to prevent form resubmission on refresh (POST/Redirect/GET pattern)
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
        
    } catch (Exception $e) {
        // Store error message in session
        $_SESSION['email_status'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        $_SESSION['email_status_type'] = 'error';
        
        // Redirect to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Release session lock to speed up parallel requests (like AJAX calls from the dashboard)
session_write_close();
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Application Management</title>
    <!-- Preconnect to CDNs for faster DNS resolution -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.0/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
            <button class="close-sidebar d-md-none" id="closeSidebar"><i class="bx bx-x"></i></button>
        </div>
        <ul>
            <li class="active" data-section="dashboard">
                <i class="bx bx-home"></i>
                <span>Dashboard</span>
            </li>
            <li data-section="applications">
                <i class="bx bx-file"></i>
                <span>Applications</span>
            </li>
            <li data-section="resumes">
                <i class="bx bx-file-find"></i>
                <span>Resume Review</span>
            </li>
            <li data-section="contacts">
                <i class="bx bx-envelope"></i>
                <span>Contact Forms</span>
            </li>
            <li data-section="inform">
                <i class="bx bx-envelope"></i>
                <span>Inform</span>
            </li>
            <li>
                <a href="admin_logout.php" style="text-decoration: none; color: inherit; display: flex; align-items: center;">
                    <i class="bx bx-log-out"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Toast Notifications -->
    <div class="toast-container"></div>

    <!-- Dashboard Section -->
    <section id="dashboard" class="content">
        <div class="section-header">
            <div class="d-flex align-items-center">
                <button class="mobile-toggle d-md-none me-3" id="mobileToggle">
                    <i class="bx bx-menu"></i>
                </button>
                <h2 class="mb-0">Application Dashboard</h2>
            </div>
            <div>
                <span class="badge bg-secondary">Last updated: <?php echo date('M d, Y H:i'); ?></span>
                <button id="refresh-dashboard" class="btn btn-sm btn-outline-primary ms-2">
                    <i class="bx bx-refresh"></i> Refresh
                </button>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card stat-card stat-total position-relative">
                    <i class="bx bx-file"></i>
                    <h2><?php echo $stats['total']; ?></h2>
                    <p>Total Applications</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card stat-card stat-pending position-relative">
                    <i class="bx bx-time"></i>
                    <h2><?php echo $stats['pending']; ?></h2>
                    <p>Pending Review</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card stat-card stat-interview position-relative">
                    <i class="bx bx-calendar-check"></i>
                    <h2><?php echo $stats['interview']; ?></h2>
                    <p>Interview Stage</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card stat-card stat-hired position-relative">
                    <i class="bx bx-check-circle"></i>
                    <h2><?php echo $stats['hired']; ?></h2>
                    <p>Candidates Hired</p>
                </div>
            </div>
        </div>
        
        <!-- Recent Applications -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Applications</h5>
                <a href="#" class="btn btn-sm btn-primary" data-section="applications">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Date Applied</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($applications) > 0): ?>
                                <?php foreach (array_slice($applications, 0, 5) as $application): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($application['name']); ?></td>
                                        <td><?php echo htmlspecialchars($application['job_title']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($application['created_at'])); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($application['status']); ?>">
                                                <?php echo ucfirst($application['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary view-application" 
                                                data-id="<?php echo $application['id']; ?>">
                                            <i class="bx bx-show"></i>
                                        </button>
                                        <?php if (!empty($application['resume_url'])): ?>
                                        <button class="btn btn-sm btn-outline-info view-resume" 
                                                data-id="<?php echo $application['id']; ?>"
                                                data-resume="<?php echo htmlspecialchars($application['resume_url']); ?>">
                                            <i class="bx bx-file"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-resume-btn"
                                                data-id="<?php echo $application['id']; ?>">
                                            <i class="bx bx-trash-alt"></i>
                                        </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-outline-danger delete-application-btn"
                                                data-id="<?php echo $application['id']; ?>">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No applications found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <?php if (count($applications) === 0): ?>
        <div class="alert alert-info">
            <p>No applications found in the database. Applications submitted through the hiring form will appear here.</p>
            <p>Check that your Supabase database connection is properly configured in config.php.</p>
        </div>
        <?php endif; ?>
    </section>

    <!-- Applications Management Section -->
    <section id="applications" class="content d-none">
        <div class="section-header">
            <h2>Application Management</h2>
            <div>
                <span id="application-count" class="badge bg-secondary me-2">
                    <?php echo $totalApplications; ?> Applications
                </span>
                <button id="refresh-applications" class="btn btn-sm btn-outline-primary">
                    <i class="bx bx-refresh"></i> Refresh
                </button>
            </div>
        </div>
        
        <!-- Bulk Actions -->
        <div class="d-flex justify-content-between mb-3">
            <div class="d-flex">
                <select class="form-select me-2" id="bulk-action">
                    <option value="">Bulk Actions</option>
                    <option value="pending">Mark as Pending</option>
                    <option value="reviewed">Mark as Reviewed</option>
                    <option value="interview">Move to Interview</option>
                    <option value="hired">Mark as Hired</option>
                    <option value="rejected">Reject Applications</option>
                </select>
                <button class="btn btn-primary" id="apply-bulk-action" disabled>
                    <i class="bx bx-check"></i> Apply
                </button>
            </div>
            <div>
                <span id="selected-count" class="me-2">0 selected</span>
                <button class="btn btn-sm btn-outline-secondary" id="clear-selection">Clear</button>
            </div>
        </div>
        
        <!-- Applications List -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="40">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all-applications">
                                    </div>
                                </th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Email</th>
                                <th>Date Applied</th>
                                <th>Status</th>
                                <th>Resume</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="applications-list">
                            <?php if (count($applications) > 0): ?>
                                <?php foreach ($applications as $application): ?>
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input application-checkbox" type="checkbox" 
                                                       value="<?php echo $application['id']; ?>">
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($application['name']); ?></td>
                                        <td><?php echo htmlspecialchars($application['job_title']); ?></td>
                                        <td><?php echo htmlspecialchars($application['email']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($application['created_at'])); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($application['status']); ?>">
                                                <?php echo ucfirst($application['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($application['resume_url']) || !empty($application['resume_path'])): ?>
                                            <div class="btn-group">
                                                <a href="admin.php?action=download_resume&id=<?php echo $application['id']; ?>" class="btn btn-sm btn-success">
                                                    <i class="bx bx-download"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger delete-resume-btn"
                                                       data-id="<?php echo $application['id']; ?>">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                            <?php else: ?>
                                            <span class="text-muted">No resume</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary view-application"
                                                   data-id="<?php echo $application['id']; ?>">
                                                <i class="bx bx-show"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-application-btn"
                                                   data-id="<?php echo $application['id']; ?>">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No applications found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalApplications > $limit): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="showing-info">
                            Showing <?php echo min(($currentPage - 1) * $limit + 1, $totalApplications); ?> to 
                            <?php echo min($currentPage * $limit, $totalApplications); ?> of 
                            <?php echo $totalApplications; ?> applications
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm" id="applications-pagination">
                                <?php 
                                    $totalPages = ceil($totalApplications / $limit);
                                    $prevDisabled = $currentPage <= 1 ? 'disabled' : '';
                                    $nextDisabled = $currentPage >= $totalPages ? 'disabled' : '';
                                ?>
                                <li class="page-item <?php echo $prevDisabled; ?>">
                                    <a class="page-link" href="#" data-page="<?php echo $currentPage - 1; ?>">&laquo;</a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $currentPage == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="#" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $nextDisabled; ?>">
                                    <a class="page-link" href="#" data-page="<?php echo $currentPage + 1; ?>">&raquo;</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <!-- Resume Review Section -->
    <section id="resumes" class="content d-none">
        <div class="section-header">
            <h2>Resume Review</h2>
        </div>
        
        <div class="row">
            <div class="col-md-5">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Applications</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" id="resume-applications-list">
                            <?php if (count($applications) > 0): ?>
                                <?php foreach ($applications as $index => $application): ?>
                                    <a href="#" class="list-group-item list-group-item-action resume-application-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                                       data-id="<?php echo $application['id']; ?>"
                                       data-resume="<?php echo htmlspecialchars($application['resume_url'] ?? ''); ?>">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($application['name']); ?></h6>
                                            <small class="text-muted"><?php echo date('M d', strtotime($application['created_at'])); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars($application['job_title']); ?></p>
                                        <div>
                                            <span class="status-badge status-<?php echo strtolower($application['status'] ?? 'pending'); ?>">
                                                <?php echo ucfirst($application['status'] ?? 'pending'); ?>
                                            </span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="list-group-item text-center">
                                    <p class="mb-0">No applications available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($totalApplications > count($applications)): ?>
                        <div class="card-footer text-center">
                            <button id="load-more-resumes" class="btn btn-sm btn-outline-primary">
                                Load More
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Application Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <form id="quick-status-form">
                            <input type="hidden" id="quick-application-id">
                            <div class="mb-3">
                                <label for="quick-status" class="form-label">Update Status</label>
                                <select class="form-select" id="quick-status">
                                    <option value="pending">Pending</option>
                                    <option value="reviewed">Reviewed</option>
                                    <option value="interview">Interview</option>
                                    <option value="hired">Hired</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="quick-notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="quick-notes" rows="3"></textarea>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <button type="button" class="btn btn-outline-secondary" id="view-full-application">
                                    View Full Application
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" id="resume-title">Resume Viewer</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-primary open-resume-new-tab">
                                <i class="bx bx-link-external"></i> Open in New Tab
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="resume-viewer" class="d-flex justify-content-center align-items-center bg-light" style="height: 600px;">
                            <div class="text-center py-5">
                                <i class="bx bx-file" style="font-size: 3rem;"></i>
                                <p class="mt-3">Select an application to view the resume</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Add a Contacts Section after Resumes Section -->
    <section id="contacts" class="content" style="display: none;">
        <div class="section-header">
            <h2>Contact Form Submissions</h2>
            <div>
                <span class="badge bg-secondary">Last updated: <?php echo date('M d, Y H:i'); ?></span>
                <button id="refresh-contacts" class="btn btn-sm btn-outline-primary ms-2">
                    <i class="bx bx-refresh"></i> Refresh
                </button>
            </div>
        </div>

        <?php
        try {
            $conn = getDbConnection();
            renderContactSubmissions($conn);
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">Error connecting to database: ' . $e->getMessage() . '</div>';
        }   
        ?>
    </section>

    <section id="inform" class="content" style="display: none;">
        <div class="section-header">
            <h2>Inform Section</h2>
        </div>
        <div class="page-header">
		<h1>
			<i class="bi bi-envelope-paper-fill"></i>
			Send Email
		</h1>
		
	</div>

	<div class="container">
		<!-- Success message with timer -->
		<?php if ($status): ?>
			<div id="statusMessage" class="<?php echo $statusType === 'success' ? 'success' : 'error'; ?>">
				<?php echo $status; ?>
			</div>
			<?php if ($statusType === 'success'): ?>
			<script>
				setTimeout(function() {
					const statusMessage = document.getElementById('statusMessage');
					if (statusMessage) {
						statusMessage.style.transition = 'opacity 0.5s ease';
						statusMessage.style.opacity = '0';
						setTimeout(() => statusMessage.remove(), 500);
					}
				}, 10000); // 10 seconds
			</script>
			<?php endif; ?>
		<?php endif; ?>

		<div class="email-card">
			<div class="email-card-header">
				<h2>
					<i class="bi bi-send-fill"></i>
					Compose Email
				</h2>
				<p>Fill in the details below to send an email</p>
			</div>

			<div class="email-card-body">
				<form action="" method="POST" enctype="multipart/form-data">
					<!-- Recipient Section -->
					<div class="form-section">
						<div class="form-section-title">
							<i class="bi bi-person-circle"></i>
							Recipient Information
						</div>
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">
									<i class="bi bi-envelope"></i>
									To Email
								</label>
								<input type="email" name="to" class="form-control" placeholder="user@example.com" required>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">
									<i class="bi bi-person"></i>
									Recipient Name
								</label>
								<input type="text" name="name" class="form-control" placeholder="">
							</div>
						</div>
					</div>

					<!-- Subject Section -->
					<div class="form-section">
						<div class="form-section-title">
							<i class="bi bi-text-left"></i>
							Subject
						</div>
						<div class="mb-3">
							<label class="form-label">
								<i class="bi bi-pencil-square"></i>
								Email Subject
							</label>
							<input type="text" name="subject" class="form-control" placeholder="Enter email subject" required>
						</div>
					</div>

					<!-- Message Section -->
					<div class="form-section">
						<div class="form-section-title">
							<i class="bi bi-chat-left-text"></i>
							Message Body
						</div>
						<div class="mb-3">
							<label class="form-label">
								<i class="bi bi-file-text"></i>
								Email Message
							</label>
							<textarea name="msg" class="form-control" rows="8" placeholder="Write your message here..." required></textarea>
						</div>
					</div>

					<!-- Attachment Section -->
					<div class="form-section">
						<div class="form-section-title">
							<i class="bi bi-paperclip"></i>
							Attachment
						</div>
						<div class="mb-3">
							<label class="form-label">
								<i class="bi bi-file-earmark-arrow-up"></i>
								Upload File (Optional)
							</label>
							<input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
							<small class="text-muted d-block mt-2">
								<i class="bi bi-info-circle"></i>
								Max 5MB • Supported: PDF, DOC, DOCX, PNG, JPG, JPEG
							</small>
						</div>
					</div>

					<div class="text-end">
						<button type="submit" name="submit_form" class="btn btn-primary">
							<i class="bi bi-send-fill"></i>
							Send Email
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>


        
    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mobile Sidebar Toggle Logic
        document.addEventListener('DOMContentLoaded', function() {
            const mobileToggle = document.getElementById('mobileToggle');
            const closeSidebar = document.getElementById('closeSidebar');
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            function toggleSidebar() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            }
            
            if (mobileToggle) mobileToggle.addEventListener('click', toggleSidebar);
            if (closeSidebar) closeSidebar.addEventListener('click', toggleSidebar);
            if (overlay) overlay.addEventListener('click', toggleSidebar);
            
            // Close sidebar when clicking a nav item on mobile
            const navItems = document.querySelectorAll('.sidebar ul li[data-section]');
            navItems.forEach(item => {
                item.addEventListener('click', () => {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('active');
                        overlay.classList.remove('active');
                    }
                });
            });
        });

        // Navigation logic
        document.addEventListener('DOMContentLoaded', function() {
            // Show toast notifications if available
            document.querySelectorAll('.toast').forEach(toastEl => {
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
            });
            
            // Removed search spinner code
            
            // Initialize all view resume modal buttons
            document.querySelectorAll('.view-resume-modal').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const applicationId = this.getAttribute('data-id');
                    viewResumeModal(applicationId);
                });
            });
            
            // Initialize all view application buttons
            document.querySelectorAll('.view-application').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const applicationId = this.getAttribute('data-id');
                    viewApplicationDetails(applicationId);
                });
            });
            
            // Show loading toast
            function showToast(message, type = 'info') {
                const toastContainer = document.querySelector('.toast-container');
                const toastId = `toast-${Date.now()}`;
                
                const toastHTML = `
                    <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;
                
                toastContainer.insertAdjacentHTML('beforeend', toastHTML);
                
                const toastElement = document.getElementById(toastId);
                const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 5000 });
                toast.show();
                
                // Remove the element after it's hidden
                toastElement.addEventListener('hidden.bs.toast', function() {
                    this.remove();
                });
            }
            
            // Make showToast available globally
            window.showToast = showToast;
            
            // Navigation
            document.querySelectorAll('.sidebar ul li[data-section]').forEach(item => {
                item.addEventListener('click', function() {
                    showSection(this.getAttribute('data-section'));
                });
            });
            
            document.querySelectorAll('a[data-section]').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    showSection(this.getAttribute('data-section'));
                });
            });
            
            function showSection(sectionId) {
                // Update nav
                document.querySelectorAll('.sidebar ul li').forEach(item => {
                    item.classList.remove('active');
                });
                document.querySelector(`.sidebar ul li[data-section="${sectionId}"]`)?.classList.add('active');
                
                // Show/hide sections
                document.querySelectorAll('section.content').forEach(section => {
                    section.classList.add('d-none');
                });
                document.getElementById(sectionId)?.classList.remove('d-none');
                
                // If we're showing resume section, initialize the first resume if one exists
                if (sectionId === 'resumes') {
                    const firstItem = document.querySelector('.resume-application-item.active');
                    if (firstItem) {
                        const resumeUrl = firstItem.getAttribute('data-resume');
                        const appId = firstItem.getAttribute('data-id');
                        
                        if (resumeUrl && resumeUrl !== 'undefined' && resumeUrl !== '') {
                            displayResume(resumeUrl);
                            populateQuickActions(appId);
                        }
                    }
                }
            }
            
            // Function to display a resume in the viewer
            function displayResume(resumeUrl) {
                const viewer = document.getElementById('resume-viewer');
                
                // Remove any existing content
                viewer.innerHTML = '';
                
                if (!resumeUrl || resumeUrl === 'undefined' || resumeUrl === 'null' || resumeUrl === '') {
                    viewer.innerHTML = `
                        <div class="text-center py-5">
                            <i class="bx bx-file" style="font-size: 3rem;"></i>
                            <p class="mt-3">No resume available for this application</p>
                        </div>
                    `;
                    // Disable the open in new tab button
                    const openButton = document.querySelector('.open-resume-new-tab');
                    if (openButton) {
                        openButton.disabled = true;
                    }
                    return;
                }
                
                // Enable the open in new tab button
                const openButton = document.querySelector('.open-resume-new-tab');
                if (openButton) {
                    openButton.disabled = false;
                    openButton.onclick = function() {
                        window.open(resumeUrl, '_blank');
                    };
                }
                
                // Check file extension to determine how to display
                const fileExt = resumeUrl.split('.').pop().toLowerCase();
                
                if (fileExt === 'pdf') {
                    // Create a proxy URL for the PDF to ensure proper embedding
                    const proxyUrl = 'admin.php?action=pdf_proxy&url=' + encodeURIComponent(resumeUrl);
                    
                    // For PDF files, embed using our proxy
                    const iframe = document.createElement('iframe');
                    iframe.src = proxyUrl;
                    iframe.className = 'resume-iframe';
                    
                    viewer.appendChild(iframe);
                } else if (fileExt === 'doc' || fileExt === 'docx') {
                    // For Word documents, we need to use a fallback as they can't be embedded directly
                    viewer.innerHTML = `
                        <div class="text-center p-5">
                            <div class="alert alert-info">
                                <i class="bx bx-file-doc fs-1"></i>
                                <p>This is a Microsoft Word document that cannot be previewed directly.</p>
                                <button class="btn btn-primary mt-3" onclick="window.open('${resumeUrl}', '_blank')">
                                    Download Document
                                </button>
                            </div>
                        </div>
                    `;
                } else {
                    // For other file types
                    viewer.innerHTML = `
                        <div class="text-center p-5">
                            <div class="alert alert-warning">
                                <i class="bx bx-error-circle fs-1"></i>
                                <p>Unsupported file format. Please click the button below to open in a new tab.</p>
                                <button class="btn btn-primary mt-3" onclick="window.open('${resumeUrl}', '_blank')">
                                    Open File in New Tab
                                </button>
                            </div>
                        </div>
                    `;
                }
            }
            
            // Populate quick actions form with application data
            function populateQuickActions(applicationId) {
                if (!applicationId) {
                    console.error('No application ID provided to populateQuickActions');
                    return;
                }
                
                document.getElementById('quick-application-id').value = applicationId;
                
                // Show loading state in the form
                const statusSelect = document.getElementById('quick-status');
                const notesTextarea = document.getElementById('quick-notes');
                const submitBtn = document.querySelector('#quick-status-form button[type="submit"]');
                
                statusSelect.disabled = true;
                notesTextarea.disabled = true;
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
                }
                
                // Fetch application details
                const formData = new FormData();
                formData.append('action', 'get_application_details');
                formData.append('application_id', applicationId);
                
                fetch('admin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.application) {
                        const app = data.application;
                        
                        // Set the status dropdown
                        statusSelect.value = app.status || 'pending';
                        
                        // Set the notes
                        notesTextarea.value = app.notes || '';
                        
                        // Update resume title with applicant name
                        const resumeTitle = document.getElementById('resume-title');
                        if (resumeTitle) {
                            resumeTitle.textContent = `Resume: ${app.name || 'Applicant'} - ${app.position || 'Position'}`;
                        }
                    } else {
                        showToast('Error loading application details: ' + (data.message || 'Unknown error'), 'danger');
                    }
                    
                    // Re-enable form controls
                    statusSelect.disabled = false;
                    notesTextarea.disabled = false;
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Save Changes';
                    }
                })
                .catch(error => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error updating status', 'danger');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            }
            
            // Handle "View Full Application" button
            document.getElementById('view-full-application')?.addEventListener('click', function() {
                const applicationId = document.getElementById('quick-application-id').value;
                if (applicationId) {
                    viewApplicationDetails(applicationId);
                } else {
                    showToast('No application selected', 'warning');
                }
            });
            
            // Set up bulk action management
            document.querySelectorAll('.application-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSelectedCount();
                });
            });
            
            document.getElementById('select-all-applications')?.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.application-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectedCount();
            });
            
            document.getElementById('clear-selection')?.addEventListener('click', function() {
                document.querySelectorAll('.application-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                document.getElementById('select-all-applications').checked = false;
                updateSelectedCount();
            });
            
            function updateSelectedCount() {
                const selected = document.querySelectorAll('.application-checkbox:checked').length;
                document.getElementById('selected-count').textContent = `${selected} selected`;
                document.getElementById('apply-bulk-action').disabled = selected === 0;
            }
            
            document.getElementById('apply-bulk-action')?.addEventListener('click', function() {
                const action = document.getElementById('bulk-action').value;
                if (!action) {
                    showToast('Please select an action', 'warning');
                    return;
                }
                
                const selectedIds = Array.from(document.querySelectorAll('.application-checkbox:checked'))
                    .map(checkbox => checkbox.value);
                
                if (selectedIds.length === 0) {
                    showToast('No applications selected', 'warning');
                    return;
                }
                
                if (confirm(`Are you sure you want to mark ${selectedIds.length} application(s) as "${action}"?`)) {
                    // Create form data
                    const formData = new FormData();
                    formData.append('action', 'bulk_update_applications');
                    formData.append('status', action);
                    selectedIds.forEach(id => {
                        formData.append('application_ids[]', id);
                    });
                    
                    // Show loading
                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Applying...';
                    
                    // Send request
                    fetch('admin.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message || 'Status updated successfully', 'success');
                            // Reload page after short delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            showToast(data.message || 'Failed to update status', 'danger');
                            this.disabled = false;
                            this.innerHTML = '<i class="bx bx-check"></i> Apply';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred', 'danger');
                        this.disabled = false;
                        this.innerHTML = '<i class="bx bx-check"></i> Apply';
                    });
                }
            });
            
            // Pagination
            document.querySelectorAll('#applications-pagination .page-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = this.getAttribute('data-page');
                    
                    // Simple URL with just the page parameter
                    window.location.href = `admin.php?page=${page}`;
                });
            });
            
            // Removed application filter form event listener
            
            // Function to load applications (called by updateApplicationStatus)
            window.loadApplications = function() {
                // In a real app, would fetch dynamically
                // Here we'll just reload the page
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            };
            
            // Set up buttons to view applications
            document.querySelectorAll('.view-application').forEach(btn => {
                btn.addEventListener('click', function() {
                    const appId = this.getAttribute('data-id');
                    if (appId) {
                        viewApplicationDetails(appId);
                    }
                });
            });
            
            // Set up view resume buttons
            document.querySelectorAll('.view-resume').forEach(btn => {
                btn.addEventListener('click', function() {
                    const applicationId = this.getAttribute('data-id');
                    if (applicationId) {
                        // Use the viewResume function which goes through the proper PHP handler
                        viewResume(applicationId);
                    } else {
                        // Fallback to direct URL if no ID is available
                        const resumeUrl = this.getAttribute('data-resume');
                        if (resumeUrl) {
                            window.open(resumeUrl, '_blank');
                        }
                    }
                });
            });
            
            // Set up Resume tab functionality
            document.querySelectorAll('.resume-application-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Update active state
                    document.querySelectorAll('.resume-application-item').forEach(el => {
                        el.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    // Get resume URL and ID
                    const resumeUrl = this.getAttribute('data-resume');
                    const appId = this.getAttribute('data-id');
                    
                    // Display resume and populate quick actions
                    displayResume(resumeUrl);
                    populateQuickActions(appId);
                });
            });
            
            // Handle quick status form submission
            document.getElementById('quick-status-form')?.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const applicationId = document.getElementById('quick-application-id').value;
                const status = document.getElementById('quick-status').value;
                const notes = document.getElementById('quick-notes').value;
                
                if (!applicationId) {
                    showToast('No application selected', 'warning');
                    return;
                }
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                submitBtn.disabled = true;
                
                // Prepare form data
                const formData = new FormData();
                formData.append('action', 'update_application_status');
                formData.append('application_id', applicationId);
                formData.append('status', status);
                formData.append('notes', notes);
                
                fetch('admin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Status updated successfully', 'success');
                        
                        // Update the status badge on the list item
                        const listItem = document.querySelector(`.resume-application-item[data-id="${applicationId}"]`);
                        if (listItem) {
                            const badge = listItem.querySelector('.status-badge');
                            if (badge) {
                                badge.className = `status-badge status-${status}`;
                                badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                            }
                        }
                        
                        // Reload the page after a delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showToast(data.message || 'Error updating status', 'danger');
                    }
                    
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error updating status', 'danger');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            });
            
            // View resume modal buttons
            document.querySelectorAll('.view-resume-modal').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const applicationId = this.getAttribute('data-id');
                    viewResumeModal(applicationId);
                });
            });
            
            // View application buttons
            document.querySelectorAll('.view-application').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const applicationId = this.getAttribute('data-id');
                    viewApplicationDetails(applicationId);
                });
            });
            
            // Delete resume buttons
            document.querySelectorAll('.delete-resume-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const applicationId = this.getAttribute('data-id');
                    if (confirm('Are you sure you want to delete this resume? This action cannot be undone.')) {
                        deleteResume(applicationId);
                    }
                });
            });
            
            // Delete application buttons
            document.querySelectorAll('.delete-application-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const applicationId = this.getAttribute('data-id');
                    deleteApplication(applicationId);
                });
            });
            
            // Add any other event handlers here
            
            // Ensure all modal close buttons work properly
            document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(button => {
                button.addEventListener('click', function() {
                    const modalElement = this.closest('.modal');
                    if (modalElement) {
                        const modalInstance = bootstrap.Modal.getInstance(modalElement);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                    }
                });
            });
            
            // Handle sidebar navigation
            document.querySelectorAll('.sidebar li[data-section]').forEach(item => {
                item.addEventListener('click', function() {
                    const section = this.getAttribute('data-section');
                    
                    // Hide all sections
                    document.querySelectorAll('.content').forEach(content => {
                        content.style.display = 'none';
                    });
                    
                    // Show selected section
                    document.getElementById(section).style.display = 'block';
                    
                    // Update active class
                    document.querySelectorAll('.sidebar li').forEach(li => {
                        li.classList.remove('active');
                    });
                    this.classList.add('active');
                });
            });
            
            // Refresh contacts button
            document.getElementById('refresh-contacts')?.addEventListener('click', function() {
                location.reload();
            });
        });
    </script>
</body>
</html>
