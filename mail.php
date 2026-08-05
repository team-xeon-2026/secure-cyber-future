<?php use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

// Start session to store flash messages
session_start();

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
?> 
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Send Email - PHPMailer</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
	<style>
		* { margin: 0; padding: 0; box-sizing: border-box; }
		
		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			min-height: 100vh;
			padding: 20px;
		}

		.page-header {
			text-align: center;
			color: white;
			padding: 2rem 0 1rem;
			margin-bottom: 2rem;
		}

		.page-header h1 {
			font-size: 2rem;
			font-weight: 700;
			margin-bottom: 0.5rem;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 0.75rem;
		}

		.page-header p {
			font-size: 1rem;
			opacity: 0.95;
		}

		.container {
			max-width: 1100px;
			margin: 0 auto;
		}

		.email-card {
			background: white;
			border-radius: 16px;
			box-shadow: 0 8px 32px rgba(0,0,0,0.15);
			overflow: hidden;
		}

		.email-card-header {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
			padding: 1.75rem 2rem;
		}

		.email-card-header h2 {
			font-size: 1.35rem;
			font-weight: 700;
			margin: 0 0 0.4rem;
			display: flex;
			align-items: center;
			gap: 0.75rem;
		}

		.email-card-header p {
			margin: 0;
			opacity: 0.95;
			font-size: 0.9rem;
		}

		.email-card-body {
			padding: 2rem;
		}

		.form-section {
			background: #f8f9fa;
			border-radius: 12px;
			padding: 1.5rem;
			margin-bottom: 1.5rem;
		}

		.form-section-title {
			font-size: 1rem;
			font-weight: 700;
			color: #667eea;
			margin-bottom: 1rem;
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}

		.form-label {
			font-weight: 600;
			font-size: 0.9rem;
			color: #374151;
			margin-bottom: 0.5rem;
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}

		.form-label i {
			color: #667eea;
			font-size: 1rem;
		}

		.form-control {
			border: 1px solid #d1d5db;
			border-radius: 8px;
			padding: 0.7rem 1rem;
			font-size: 0.9rem;
			transition: all 0.2s ease;
		}

		.form-control:focus {
			border-color: #667eea;
			box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
			outline: none;
		}

		textarea.form-control {
			resize: vertical;
			min-height: 180px;
			line-height: 1.6;
		}

		.btn-primary {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
			border: none;
			border-radius: 8px;
			padding: 0.7rem 1.75rem;
			font-weight: 600;
			font-size: 0.9rem;
			transition: all 0.2s ease;
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
			box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
		}

		.btn-primary:hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
		}

		.success {
			background: #ecfdf5;
			color: #065f46;
			border-left: 4px solid #10b981;
			border-radius: 12px;
			padding: 1rem 1.25rem;
			margin-bottom: 1.5rem;
			display: flex;
			align-items: center;
			gap: 0.75rem;
			font-size: 0.9rem;
			font-weight: 500;
		}

		.success::before {
			content: '✓';
			width: 24px;
			height: 24px;
			background: #10b981;
			color: white;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: bold;
			flex-shrink: 0;
		}

		.error {
			background: #fef2f2;
			color: #991b1b;
			border-left: 4px solid #ef4444;
			border-radius: 12px;
			padding: 1rem 1.25rem;
			margin-bottom: 1.5rem;
			display: flex;
			align-items: center;
			gap: 0.75rem;
			font-size: 0.9rem;
			font-weight: 500;
		}

		.error::before {
			content: '✕';
			width: 24px;
			height: 24px;
			background: #ef4444;
			color: white;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: bold;
			flex-shrink: 0;
		}

		@media (max-width: 768px) {
			.page-header h1 { font-size: 1.5rem; }
			.email-card-header, .email-card-body { padding: 1.5rem; }
			.form-section { padding: 1rem; }
		}
	</style>
</head>
<body>
	<div class="page-header">
		<h1>
			<i class="bi bi-envelope-paper-fill"></i>
			Send Email
		</h1>
		<p>Send emails directly using PHPMailer</p>
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

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
