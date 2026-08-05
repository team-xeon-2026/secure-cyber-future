# Google API Client Installation Instructions

## Option 1: Using Composer (Recommended)

1. **Install Composer** (if not already installed)
   - Download Composer from https://getcomposer.org/download/
   - Run the installer and follow the instructions

2. **Update your project dependencies**
   - Open Command Prompt or PowerShell
   - Navigate to your project directory: `cd C:\xampp\htdocs\xeon`
   - Run: `composer update`

## Option 2: Manual Installation

If you can't use Composer, follow these steps to manually install the Google API Client:

1. **Download the Google API Client Library**
   - Go to https://github.com/googleapis/google-api-php-client/releases
   - Download the latest version (e.g., `google-api-php-client-2.15.0.zip`)

2. **Extract the files**
   - Extract the downloaded ZIP file
   - Copy the entire `src` directory to your project's `vendor/google` directory
   - Make sure the path structure looks like: `vendor/google/Client.php` and other files

3. **Update the autoloader**
   - You may need to manually update the autoloader to load the Google API classes

## After Installation

Once the Google API Client is installed, you should be able to access the website without the "Class 'Google\Client' not found" error.

## Troubleshooting

If you're still having issues after installing the Google API Client:

1. **Check your PHP error logs** 
   - Look in `C:\xampp\php\logs` or your Apache error logs

2. **Verify namespaces**
   - Make sure all class references in your code use the correct namespaces: `Google\Client` and `Google\Service\Oauth2`

3. **Try a simple test script**
   - Create a file named `test_google_api.php` with the following content:
   ```php
   <?php
   require_once 'vendor/autoload.php';
   
   use Google\Client;
   
   try {
       $client = new Client();
       echo "Google API Client loaded successfully!";
   } catch (Exception $e) {
       echo "Error: " . $e->getMessage();
   }
   ```
   - Access this file in your browser to see if the Google API Client is properly loaded 