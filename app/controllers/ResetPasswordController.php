<?php

namespace App\Controllers;

use App\Dao\UserDao;
use App\Database\Database;
use App\Utils\InputChecks;
use App\Utils\AuthUtils;
use App\Utils\IpUtils;
use App\Utils\Logger;
use App\Utils\EmailSender;


class ResetPasswordController {
    private $userDao;
    private $errors = [];

    public function __construct(Database $database) {
        $this->userDao = new UserDao($database->getReadConnection(), $database->getWriteConnection());
    }

    public function showResetForm() {
        header('Referrer-Policy: no-referrer');
        AuthUtils::redirectIfUserLogged();
        $token = isset($_GET['token']) ? $_GET['token'] : null;
        $hashedToken = hash('sha256', $token);
        // Validate the token (check if it's valid and not expired)
        if (!$this->userDao->isValidToken($hashedToken)) {
            // Token is invalid, show an error message or redirect to an error page
            include_once __DIR__ . '/../views/resetPasswordError.php';
        } else {
            // Token is valid, show the reset password form
            include_once __DIR__ . '/../views/resetPasswordForm.php';
        }
    }

    public function showResetFormAfterValidation() {
        // Token is valid, show the reset password form
        include_once __DIR__ . '/../views/resetPasswordForm.php';
    }

    public function processResetPassword() {
        header('Referrer-Policy: no-referrer');
        AuthUtils::redirectIfUserLogged();
        // Token is valid, process the password reset
        $newPassword = isset($_POST['newPassword']) ? $_POST['newPassword'] : '';
        if (InputChecks::containsMaliciousScripts($newPassword)) {
            // Log the security event with user_id if set
            $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
            Logger::security('Malicious script detected in reset password page: ' . $userToLog);
        }
        $newPassword = InputChecks::testInput($newPassword);
        $confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';
        if (InputChecks::containsMaliciousScripts($confirmPassword)) {
            // Log the security event with user_id if set
            $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
            Logger::security('Malicious script detected in reset password page: ' . $userToLog);
        }
        $confirmPassword = InputChecks::testInput($confirmPassword);
        $token = isset($_POST['token']) ? $_POST['token'] : '';
        if (InputChecks::containsMaliciousScripts($token)) {
            // Log the security event with user_id if set
            $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
            Logger::security('Malicious script detected in reset password page: ' . $userToLog);
        }

        // Validate the new password
        $this->validateResetForm($newPassword, $confirmPassword);

        // If there are errors, show them in the form
        if (!empty($this->errors)) {
            $this->showResetFormAfterValidation();
            return;
        }

        // Process reset
        $this->processPasswordReset($token, $newPassword);
    }

    private function validateResetForm($newPassword, $confirmPassword) {
        // Validate the username
        if (empty($newPassword)) {
            $this->errors['username'] = 'Please enter your username.';
        } else {
            // check if password is strong
            if(!InputChecks::isStrongPassword($newPassword)) {
                $this->errors['password'] = 'The password is not strong enough.';
            }
        }

        // check if the passwords are equal
        if ($newPassword !== $confirmPassword) {
            $this->errors['passwordMismatch'] = 'Passwords do not match.';
        }
    }

    // Update the user's password
    private function processPasswordReset($token, $newPassword) {
        
        $hashedToken = hash('sha256', $token);

        //get email to which we sent the pwd reset link
        $email = $this->userDao->getEmailViaToken($hashedToken);

        $this->userDao->updatePasswordWithToken($hashedToken, $newPassword);

        //send email telling the user his passwrod has been successfully reset
        EmailSender::sendPasswordChangedConfirmation($email);

        // Show a confirmation message or redirect to a login page
        header("Location: /login");
        exit; // Make sure to exit after sending the header
    }
    
    private function getErrors() {
        return $this->errors;
    }
}
