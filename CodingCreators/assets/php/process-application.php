<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Admin email address
$admin_email = 'careers@codingcreators.in';
$admin_name = 'CodingCreators';

// Initialize response
$response = ['success' => false, 'message' => ''];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validate required fields
    $required_fields = ['fullName', 'email', 'phone', 'city', 'experience', 'position', 'resumeLink'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }
    }

    // Set timezone for correct date/time
    date_default_timezone_set('Asia/Kolkata');
    
    // Get form data
    $fullName = htmlspecialchars(strip_tags($_POST['fullName']));
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $phone = htmlspecialchars(strip_tags($_POST['phone']));
    $city = htmlspecialchars(strip_tags($_POST['city']));
    $experience = intval($_POST['experience']);
    $position = htmlspecialchars(strip_tags($_POST['position']));
    $resumeLink = $_POST['resumeLink'];
    $applicationDate = date('d-m-Y h:i:s A');

    // Validate email
    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
        exit;
    }

    // Validate resume link
    if (!filter_var($resumeLink, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid resume link format.']);
        exit;
    }

    // Validate it's a Google Drive link
    if (strpos($resumeLink, 'drive.google.com') === false) {
        echo json_encode(['success' => false, 'message' => 'Resume link must be from Google Drive.']);
        exit;
    }

    // Prepare email content
    $subject = "New Job Application - " . $position;
    
    $html_message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #007bff; color: white; padding: 15px; border-radius: 5px; }
            .content { background-color: #f5f5f5; padding: 20px; margin-top: 10px; border-radius: 5px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #333; }
            .value { color: #666; }
            .resume-link { background-color: #e7f3ff; padding: 10px; border-left: 4px solid #007bff; }
            hr { border: 1px solid #ddd; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Job Application Received</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>Position Applied:</span><br>
                    <span class='value'>" . $position . "</span>
                </div>
                <hr>
                <div class='field'>
                    <span class='label'>Full Name:</span><br>
                    <span class='value'>" . $fullName . "</span>
                </div>
                <div class='field'>
                    <span class='label'>Email:</span><br>
                    <span class='value'><a href='mailto:" . $email . "'>" . $email . "</a></span>
                </div>
                <div class='field'>
                    <span class='label'>Phone:</span><br>
                    <span class='value'>" . $phone . "</span>
                </div>
                <div class='field'>
                    <span class='label'>City:</span><br>
                    <span class='value'>" . $city . "</span>
                </div>
                <div class='field'>
                    <span class='label'>Years of Experience:</span><br>
                    <span class='value'>" . $experience . " years</span>
                </div>
                <hr>
                <div class='field'>
                    <span class='label'>Resume Link:</span><br>
                    <div class='resume-link'>
                        <a href='" . htmlspecialchars($resumeLink) . "' target='_blank'>View Resume on Google Drive</a>
                    </div>
                </div>
                <div class='field'>
                    <span class='label'>Application Date:</span><br>
                    <span class='value'>" . $applicationDate . "</span>
                </div>
            </div>
        </div>
    </body>
    </html>";

    // Set email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= 'From: <noreply@codingcreators.in>' . "\r\n";
    $headers .= 'Reply-To: ' . $email . "\r\n";

    // Send email to admin
    $mail_sent = mail($admin_email, $subject, $html_message, $headers);

    // Also send confirmation email to applicant
    $confirmation_subject = "Application Received - CodingCreators";
    $confirmation_message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #28a745; color: white; padding: 15px; border-radius: 5px; }
            .content { background-color: #f5f5f5; padding: 20px; margin-top: 10px; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Application Received</h2>
            </div>
            <div class='content'>
                <p>Dear " . $fullName . ",</p>
                <p>Thank you for applying for the <strong>" . $position . "</strong> position at CodingCreators!</p>
                <p>We have received your application and resume link. Our team will review your application and contact you shortly if you match our requirements.</p>
                <p>We appreciate your interest in joining our team.</p>
                <p>Best regards,<br>
                <strong>CodingCreators Team</strong></p>
            </div>
        </div>
    </body>
    </html>";

    $confirmation_headers = "MIME-Version: 1.0" . "\r\n";
    $confirmation_headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $confirmation_headers .= 'From: <noreply@codingcreators.in>' . "\r\n";

    mail($email, $confirmation_subject, $confirmation_message, $confirmation_headers);

    if ($mail_sent) {
        $response['success'] = true;
        $response['message'] = 'Application submitted successfully!';
    } else {
        $response['success'] = true;
        $response['message'] = 'Application received! (Email notification pending)';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
