<?php

namespace App\Controllers;

use App\Dao\UserDao;
use App\Dao\CartDao;
use App\Utils\AuthUtils;
use App\Database\Database;
use App\Dao\UsernameBlockerDao;
use App\Utils\Logger;
use App\Utils\IpUtils;
use App\Utils\InputChecks;


class LoginController {
    private $userDao;
    private $cartDao;
    private $blockerDao;
    private $errors = [];

    public function __construct(Database $database) {
        $defaultAttempts = 10;
        $this->userDao = new UserDao($database->getReadConnection(), $database->getWriteConnection());
        $this->cartDao = new CartDao($database->getReadConnection(), $database->getWriteConnection());
        $this->blockerDao = new UsernameBlockerDao($database->getReadConnection(), $database->getWriteConnection(), $defaultAttempts);
    }

    public function showLoginForm() {
        AuthUtils::redirectIfUserLogged();
        include_once __DIR__ . '/../views/login.php';
    }

    public function processLogin() {
        AuthUtils::redirectIfUserLogged();

        // Get the username and password from the form
        $username = isset($_POST['username']) ? $_POST['username'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        $usernameBlocked = $this->blockerDao->isUsernameBlocked($username);      //checking first if the IP is blocked already
        if($usernameBlocked){             //the IP is already blocked
            http_response_code(429);            //status code for too many requests
            $this->errors['username_blocked'] = 'Your username is blocked for security reasons.';
            Logger::warning('User ' . $username . ' attempted login but is already blocked.');         //logging the safety-critical event
        }else{              //the IP hasn't been blocked yet
            $this->validateLoginForm($username, $password);         //first validate the login form
            if(empty($this->errors)){               //if there are no errors, the login procedure is OK 
                if($this->blockerDao->isUsernameRegistered($username)){         //if the client IP address is registered (i.e. bc the client already performed some attempts)
                    $this->blockerDao->removeUsernameAfterSuccessfulLogin($usernameBlocked);      //remove the IP from the DB since the use knows his credentials
                }
                $this->processUserLogin($username);             //proceed to process the login
            }else{          //the user made an error during login
                $checkOutcome = $this->blockerDao->checkUsernameLoginBruteforce($username);     //check # or remaining attempts, update it accordingly, if necessary block the IP
                if($checkOutcome == false){         //ip has been blocked during the last operation
                    http_response_code(429);        //status code for too many requests
                    $this->errors['username_blocked'] = 'Due to too many failed login attempts, your username has been blocked for security reasons.';
                    Logger::security('User ' . $username . ' blocked: too many failed login attempts.');  //logging the safety-critical event
                }
            }
        }

        if (!empty($this->errors)) {          //show again the login form with the errors (if some are done)
            $this->showLoginForm();
            exit;
        }
    }

    private function validateLoginForm($username, $password) {
        // Validate the username
        if (empty($username)) {
            $this->errors['username'] = "Please enter your username.";
        } elseif (InputChecks::containsMaliciousScripts($username)) {
                // Log the security event with user_id if set
                $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
                Logger::security("Malicious script detected in login page: " . $userToLog);
        }

        // Validate the password
        if (empty($password)) {
            $this->errors['password'] = "Please enter your password.";
        } elseif (InputChecks::containsMaliciousScripts($password)) {
            // Log the security event with user_id if set
            $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
            Logger::security("Malicious script detected in login page: " . $userToLog);
        }
 
        // If there are errors, return
        if (!empty($this->errors)) {
            return;
        }

        // Retrieve user data from the database based on the username
        $user = $this->userDao->getUserByUsername($username);

        // Check if the user exists and the password is correct
        if (!$user || !password_verify($password, $user->getPassword())) {          // password_verify() is rubust to timing attacks (always checks in a constant time)
            $this->errors['invalidCredentials'] = "Invalid username or password.";
        }
    }

    private function processUserLogin($username) {
        // Set session variables, redirect, or perform other actions
        $user = $this->userDao->getUserByUsername($username);
        //$_SESSION['oldUserID'] = session_id();          //needed to transfer the cart

        //check for elements in the cart and update it accordingly
        $this->cartDao->updateCartAtLogin($user->getId());

        // setting userID and refreshing sessionID
        $_SESSION['user_id'] = $user->getId();
        session_regenerate_id(true);
        $_SESSION['CREATED'] = time();

        // Redirect to the homepage
        header("Location: /");
        exit; // Make sure to exit after sending the header
    }

    public function getErrors() {
        return $this->errors;
    }
}
