<?php

namespace App\Controllers;

use App\Database\Database;
use App\Dao\UserDao;
use App\Utils\InputChecks;
use App\Models\User;
use App\Utils\IpUtils;
use App\Utils\Logger;
use App\Utils\EmailSender;


class ProfileController {
    private $userDao;
    private $errors = [];
    private $success = '';

    public function __construct(Database $database) {
        $this->userDao = new userDao($database->getReadConnection(), $database->getWriteConnection());
    }

    public function updateProfile() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $newPassword = $_POST['new-password'] ?? '';
        $email = $_POST['email'];

        // If the username is not present in the post or it's in wrong format, we return an error
        if(empty($username)) {
            $this->errors['username'] = 'Please enter your username.';
        } else {
            if (InputChecks::containsMaliciousScripts($username)) {
                // Log the security event with user_id if set
                $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
                Logger::security("Malicious script detected in profile page: " . $userToLog);
            }
            if (!preg_match('/^[a-zA-Z0-9_]+$/',$username)) { 
                $this->errors['username'] = 'Only letters, numbers and underscores are allowed';
            }
        }

        // Old password required in order to make changes, serves as defense to CSRF
        if(empty($password)){
            $this->errors['password'] = 'Your old password is necessary to apply changes.';
        } elseif (InputChecks::containsMaliciousScripts($password)) {
            // Log the security event with user_id if set
            $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
            Logger::security("Malicious script detected in profile page: " . $userToLog);
        }

        // If newPassword is present but not strong enough
        if(!empty($newPassword)) {
            if (InputChecks::containsMaliciousScripts($newPassword)) {
                // Log the security event with user_id if set
                $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
                Logger::security("Malicious script detected in profile page: " . $userToLog);
            }
            if(!InputChecks::isStrongPassword($newPassword)) {
                $this->errors['newPassword'] = 'The new password is not strong enough';
            }
        }

        // If there are any errors, we return them
        if(!empty($this->errors)) {
            $this->showProfile();
            exit;
        }

        // Fetch the user object
        $user = $this->userDao->getUserById($_SESSION['user_id']);

        // If the username is the same and new Password is empty OR the password is wrong, there's nothign to update
        if(($username !== $user->getUsername() || !empty($newPassword)) && password_verify($password, $user->getPassword())) {
            // Check for overlapping usernames
            $overlappingUser = ($username !== $user->getUsername()) ? $this->userDao->getUserByUsername($username) : null;
            if(!$overlappingUser){
                $user->setUsername($username);
                if(!empty($newPassword)) $user->setPassword(password_hash($newPassword, PASSWORD_DEFAULT));
                $this->userDao->updateUser($user);
                $this->success = 'Your profile has been successfully updated.';

                //send notification email to user to confirm profile modification (both for username change or pwd change)
                EmailSender::sendProfileUpdateNotification($email);
            } else {
                $this->errors['username'] = 'The user already exists.';
            }
        } else {
            $this->errors['newPassword'] = 'Nothing to modify or incorrect password.';
        }

        $this->showProfile($user);
    }

    public function showProfile(User $user = null) {    
        if(!$user) $user = $this->userDao->getUserById($_SESSION['user_id']);
        $orders = $this->userDao->getUserOrders($_SESSION['user_id']);
        include_once __DIR__ . '/../views/profile.php';
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getSuccess() {
        return $this->success;
    }
}

?>