<?php

namespace App\Controllers;


class LogoutController {
    
    public function logout() {
        session_unset(); // Unset all session variables
        session_destroy(); // Destroy the session

        // Redirect to the login page
        header("Location: /");
        exit;
    }
}
