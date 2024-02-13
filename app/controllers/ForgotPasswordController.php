<?php

namespace App\Controllers;

use App\Dao\UserDao;
use App\Utils\TokenGenerator;
use App\Utils\EmailSender;
use App\Database\Database;
use App\Utils\InputChecks;
use App\Utils\AuthUtils;
use App\Dao\IPBlockerDao;
use App\Utils\Logger;
use App\Utils\IpUtils;


class ForgotPasswordController {
    private $userDao;
    private $blockerDao;
    private $errors = [];

    public function __construct(Database $database) {
        $defaultAttempts = 5;           //how many attempts the user can do before having his IP blocked
        $this->userDao = new UserDao($database->getReadConnection(), $database->getWriteConnection());
        $this->blockerDao = new IPBlockerDao($database->getReadConnection(), $database->getWriteConnection(), $defaultAttempts);
    }

    // Method to display the forgot password form
    public function showForgotPasswordForm() {
        AuthUtils::redirectIfUserLogged();
        include_once __DIR__ . '/../views/forgotPasswordForm.php';
    }

    // Method to process the forgot password request
    public function processForgotPassword() {
        AuthUtils::redirectIfUserLogged();

        // Get email from POST parameters
        $email = $_POST['email'] ?? '';

        // Validate email
        $this->validateEmail($email);

        // Check if the email exists
        $user = $this->userDao->getUserByEmail($email);
        if (!$user) {
            header('Location: /login');
            exit;
        }

        // If there are errors, show them in the form
        if (!empty($this->errors)) {
            $this->showForgotPasswordForm();
            return;
        }

        // Get client IP address
        $clientIpAddress = IpUtils::getClientIp();

        // Log IP and email information for password change request
        Logger::info('IP ' . $clientIpAddress . ' requested password change for email ' . $email);

        // Check if the IP is blocked
        $isIpBlocked = $this->blockerDao->isIpBlocked($clientIpAddress);
        if ($isIpBlocked) {
            http_response_code(429);
            $this->errors['ip_blocked'] = 'Your IP address is blocked for security reasons.';
            $this->showForgotPasswordForm();
        } else {
            // Check if IP should be blocked due to too many password change requests
            $checkOutcome = $this->blockerDao->checkIPAddressChangePassword($clientIpAddress);
            if (!$checkOutcome) {
                http_response_code(429);
                $this->errors['ip_blocked'] = 'Due to too many requests,
                    your IP address has been blocked for security reasons.';
                $this->showForgotPasswordForm();
                Logger::warning('IP ' . $clientIpAddress . ' blocked: too many password change requests.');
            } else {
                // Generate a unique token
                $token = TokenGenerator::generateToken();
                $hashedToken = hash('sha256', $token);
                // Associate the hashed token with the user account (save it to the database)
                // Token is hashed to prevent data leak in case of database breach
                $this->userDao->associateTokenWithEmail($user->getId(), $hashedToken);

                // Send a password reset email
                $emailSent = EmailSender::sendResetPasswordEmail($email, $token);
                Logger::info(getenv('gmail_email'));
                if ($emailSent) {
                    // Redirect to login page after successful email send
                    header('Location: /login');
                    exit;
                } else {
                    // Failed to send email, show error message
                    $this->errors['email'] = 'Failed to send e-mail, try again later.';
                    $this->blockerDao->restoreRemainingAttemptsAfterError($clientIpAddress);
                    $this->showForgotPasswordForm();
                }
            }
        }
    }

    // Method to validate the email
    public function validateEmail($email) {
        if (empty($email)) {
            $this->errors['email'] = 'E-mail is required.';
        } else {
            if (InputChecks::containsMaliciousScripts($email)) {
                // Log the security event with user_id if set
                $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
                Logger::security('Malicious script detected in forgot password page: ' . $userToLog);
            }
            $email = InputChecks::testInput($email);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Check if e-mail address is well-formed
                $this->errors['email'] = 'Invalid e-mail format.';
            }
        }
    }

    // Method to get validation errors
    public function getErrors() {
        return $this->errors;
    }
}
