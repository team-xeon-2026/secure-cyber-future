<?php
/**
 * reCAPTCHA Helper Functions
 * This file contains functions related to Google reCAPTCHA integration.
 */

// Include the config file if not already included
if (!function_exists('getDbConnection')) {
    require_once 'config.php';
}

/**
 * Check if reCAPTCHA should be used on the current page
 * @return bool True if reCAPTCHA should be shown, false otherwise
 */
function shouldUseRecaptcha() {
    // Get the script path
    $script_name = basename($_SERVER['SCRIPT_NAME']);
    
    // Don't use reCAPTCHA for admin login
    if ($script_name === 'admin_login.php') {
        return false;
    }
    
    // Use reCAPTCHA for all other logins
    return true;
}

/**
 * Verify a reCAPTCHA response
 * @param string $recaptcha_response The g-recaptcha-response from the form
 * @return bool True if verification succeeded, false otherwise
 */
function verifyRecaptcha($recaptcha_response) {
    global $config;
    
    // If recaptcha isn't configured or shouldn't be used, return true
    if (!isset($config['recaptcha']) || !shouldUseRecaptcha()) {
        return true;
    }
    
    // Get the secret key
    $recaptcha_secret = $config['recaptcha']['secret_key'];
    
    // If no response provided, verification fails
    if (empty($recaptcha_response)) {
        return false;
    }
    
    // Make request to Google to verify the reCAPTCHA response
    $recaptcha_verify_url = $config['recaptcha']['verify_url'];
    $recaptcha_data = [
        'secret' => $recaptcha_secret,
        'response' => $recaptcha_response,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    
    $recaptcha_options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($recaptcha_data)
        ]
    ];
    
    $recaptcha_context = stream_context_create($recaptcha_options);
    $recaptcha_result = @file_get_contents($recaptcha_verify_url, false, $recaptcha_context);
    
    // If the API call failed, log it and return false
    if ($recaptcha_result === false) {
        error_log('reCAPTCHA API call failed');
        return false;
    }
    
    $recaptcha_json = json_decode($recaptcha_result, true);
    
    // Return the success status from the Google API
    return ($recaptcha_json && isset($recaptcha_json['success']) && $recaptcha_json['success'] === true);
}

/**
 * Output the necessary JavaScript for reCAPTCHA
 * @param string $container_id The ID of the container element for reCAPTCHA
 * @param string $button_id The ID of the button to enable after successful verification
 * @param string $message_id The ID of the element to display verification messages
 * @param bool $include_styles Whether to include the CSS styles for reCAPTCHA
 */
function outputRecaptchaScript($container_id = 'recaptcha-container', $button_id = 'submit-btn', $message_id = 'captcha-message', $include_styles = true) {
    global $config;
    
    // Include CSS styles if requested
    if ($include_styles) {
        echo '<link rel="stylesheet" href="recaptcha-style.css">';
    }
    
    if (!shouldUseRecaptcha()) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                var button = document.getElementById('$button_id');
                if (button) {
                    button.disabled = false;
                }
            });
        </script>";
        return;
    }
    
    echo '<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>';
    echo "<script>
        var onloadCallback = function() {
            grecaptcha.render('$container_id', {
                'sitekey': '{$config['recaptcha']['site_key']}',
                'callback': function(response) {
                    document.getElementById('$message_id').innerHTML = '<div class=\"captcha-success\">CAPTCHA verified successfully!</div>';
                    document.getElementById('$button_id').disabled = false;
                },
                'expired-callback': function() {
                    document.getElementById('$message_id').innerHTML = '<div class=\"captcha-error\">CAPTCHA expired, please verify again.</div>';
                    document.getElementById('$button_id').disabled = true;
                }
            });
        };
    </script>";
}

/**
 * Output the reCAPTCHA container HTML
 * @param string $container_id The ID of the container element for reCAPTCHA
 * @param string $message_id The ID of the element to display verification messages
 */
function outputRecaptchaHtml($container_id = 'recaptcha-container', $message_id = 'captcha-message') {
    if (!shouldUseRecaptcha()) {
        return;
    }
    
    echo '<div class="mb-3">
        <div class="d-flex justify-content-center">
            <div id="' . $container_id . '"></div>
        </div>
        <div id="' . $message_id . '" class="text-center mt-2"></div>
    </div>';
} 


