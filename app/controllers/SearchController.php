<?php

namespace App\Controllers;

use App\Database\Database;
use App\Dao\BookDao;
use App\Utils\InputChecks;
use App\Utils\Logger;
use App\Utils\IpUtils;

class SearchController{
    private $bookDao;

    // Constructor that initializes the SearchController with a Database instance
    public function __construct(Database $database){
        // Instantiate BookDao with read and write connections from the Database instance
        $this->bookDao = new BookDao($database->getReadConnection(), $database->getWriteConnection());
    }

    // Method for handling search functionality
    public function search(){
        // Initialize an empty array to store books
        $books = [];

        // Get the search keyword from the query parameters ($_GET), default to null
        $keyword = $_GET["keyword"] ?? null;

        // Check if a keyword is provided
        if ($keyword) {
            // Check for malicious scripts in the keyword
            if (InputChecks::containsMaliciousScripts($keyword)) {
                // Log the security event with user_id if set, otherwise use client IP
                $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
                Logger::security("Malicious script detected in search keyword in homepage: " . $userToLog);
            }

            // Get books matching the provided keyword using BookDao
            $books = $this->bookDao->getBooksByKeyword($keyword);
        } else {
            // If no keyword is provided, redirect to the home page and exit
            header("Location: /");
            exit;
        }

        // HTML-escape the keyword to prevent potential XSS attacks
        $keyword = htmlspecialchars($keyword);

        // Include the search view, passing the retrieved books and escaped keyword
        include_once __DIR__ . '/../views/search.php';
    }
}
