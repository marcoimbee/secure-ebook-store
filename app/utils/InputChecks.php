<?php

namespace App\Utils;


class InputChecks {

    // Function to sanitize user input
    public static function testInput($data) {
        $data = trim($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    public static function isStrongPassword($password) {
        // Minimum length requirement
        if (strlen($password) < 8) {
            return false;
        }
    
        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
    
        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
    
        // Check for at least one digit
        if (!preg_match('/\d/', $password)) {
            return false;
        }
    
        // Check for at least one special character
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return false;
        }
    
        // If all requirements are met, the password is considered strong
        return true;
    }

    public static function containsMaliciousScripts($input){
        // Check for common patterns of malicious scripts
        $maliciousPatterns = [
            '#<script\b[^>]*>(.*?)</script>#is',
            '#javascript:#i',
            '#onmouseover=#',
            '#onerror=#',
            '#onload=#',
            '#onfocus=#',
            '#onblur=#',
            '#onchange=#',
            '#onclick=#',
            '#ondblclick=#',
            '#onkeydown=#',
            '#onkeypress=#',
            '#onkeyup=#',
            '#onmousedown=#',
            '#onmousemove=#',
            '#onmouseout=#',
            '#onmouseup=#',
            '#onselect=#',
            '#onsubmit=#',
            '#onreset=#',
            '#onresize=#',
            '#ondrag=#',
            '#ondragend=#',
            '#ondragenter=#',
            '#ondragleave=#',
            '#ondragover=#',
            '#ondragstart=#',
            '#ondrop=#',
            '#onunload=#',
            '#onhashchange=#',
            '#onpageshow=#',
            '#onpagehide=#',
            '#onpopstate=#',
            '#onbeforeunload=#',
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true; // Malicious script detected
            }
        }

        return false; // Input is clean
    }
}
