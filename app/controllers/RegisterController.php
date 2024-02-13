<?php

namespace App\Controllers;

use App\Dao\UserDao;
use App\Models\User;
use App\Utils\InputChecks;
use App\Database\Database;
use App\Utils\AuthUtils;
use App\Utils\IpUtils;
use App\Utils\Logger;


class RegisterController {
    private $userDao;
    private $errors = [];

    public function __construct(Database $database) {
        $this->userDao = new UserDAO($database->getReadConnection(), $database->getWriteConnection());
    }

    public function showRegisterForm() {
        AuthUtils::redirectIfUserLogged();
        include_once __DIR__ . '/../views/register.php';
    }

    public function processRegistration() {
        AuthUtils::redirectIfUserLogged();
        // Get the user input from the registration form and validate it
        if (empty($_POST["username"])) {
            $this->errors['username'] = "Username is required.";
        } else {
            $username = $_POST["username"];
            if (InputChecks::containsMaliciousScripts($username)) {
                // Log the security event with user_id if set
                $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
                Logger::security("Malicious script detected in register page: " . $userToLog);
            }
            // check if username only contains letters and whitespace
            if (!preg_match('/^[a-zA-Z0-9_]+$/',$username))
                $this->errors['username'] = "Only letters, numbers and underscores are allowed.";
        }

        if (empty($_POST["email"])) {
            $this->errors['email'] = "Email is required.";
        } else {
            $email = $_POST["email"];
            if (InputChecks::containsMaliciousScripts($email)) {
                // Log the security event with user_id if set
                $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
                Logger::security("Malicious script detected in register page: " . $userToLog);
            }
            $emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
            // check if e-mail address is well-formed
            if (!preg_match($emailPattern, $email))
                $this->errors['email'] = "Invalid email format.";
        }
 
        if (empty($_POST["password"])) {
            $this->errors['password'] = "Password is required.";
        } else {
            if (InputChecks::containsMaliciousScripts($_POST["password"])) {
                // Log the security event with user_id if set
                $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
                Logger::security("Malicious script detected in register page: " . $userToLog);
            }
            // check if password is strong
            if(!InputChecks::isStrongPassword($_POST["password"]))
                $this->errors['password'] = "The password is not strong enough.";
        }

        if (empty($_POST["confirm_password"])) {
            $this->errors['confirmPassword'] = "You must confirm the password.";
        } else {
            if (InputChecks::containsMaliciousScripts($_POST["confirm_password"])) {
                // Log the security event with user_id if set
                $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
                Logger::security("Malicious script detected in register page: " . $userToLog);
            }
            // check if the passwords are equal
            if ($_POST["password"] !== $_POST["confirm_password"])
                $this->errors['confirmPassword'] = "Passwords do not match.";
        }

        // Check if the username is already taken
        if ($this->userDao->getUserByUsername($username))
            $this->errors['alreadyTaken'] = "Username already taken. Please choose another one.";

        if ($this->userDao->getUserByEmail($email))
            $this->errors['email'] = "Email already taken. Please choose another one.";

        // If there are errors, show them in the form
        if (!empty($this->errors)) {
            $this->showRegisterForm();
            return;
        }

        // Hash the password securely
        $hashedPassword = password_hash($_POST["password"], PASSWORD_DEFAULT);

        // Create a new user object
        // Adjust the User class based on your implementation
        $user = new User(0, $username, $hashedPassword, $email);

        // Insert the user into the database
        $this->userDao->createUser($user);

        // Optionally, you can redirect the user to the login page after registration
        header("Location: login");
        exit;

    }

    public function getErrors() {
        return $this->errors;
    }
}
