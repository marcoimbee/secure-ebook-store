<?php

namespace App\Controllers;

use App\Dao\BookDao;
use App\Database\Database;
use App\Utils\InputChecks;
use App\Utils\IpUtils;
use App\Utils\Logger;


class BookController {
    private $bookDao;

    // Constructor that initializes the BookDao with read and write connections from the provided Database
    public function __construct(Database $database) {
        $this->bookDao = new BookDao($database->getReadConnection(), $database->getWriteConnection());
    }

    // Method to show details of a book
    public function showBookDetails() {
        // Retrieve book ID from the GET parameters
        $bookId = isset($_GET['id']) ? $_GET['id'] : null;

        // Check for malicious scripts in the book ID
        if (InputChecks::containsMaliciousScripts($bookId)) {
            // Log the security event with user_id if set, otherwise use client IP
            $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
            Logger::security("Malicious script detected in book's details: " . $userToLog);
        }

        // Validate and sanitize the book ID if necessary
        $bookId = filter_var($bookId, FILTER_VALIDATE_INT);

        // Check if the book ID is valid
        if ($bookId === false || $bookId === null) {
            // Handle invalid book ID (e.g., redirect to a 404 page)
            http_response_code(404);
            include_once __DIR__ . '/../views/404.php';
            exit;
        }

        // Retrieve book information from the database
        $book = $this->bookDao->getBookById($bookId);

        // Check if the book is not found
        if (!$book) {
            // Handle not found (e.g., redirect to a 404 page)
            http_response_code(404);
            include_once __DIR__ . '/../views/404.php';
            exit;
        }

        // Load the view with book information
        $this->view('bookDetails', ['book' => $book]);
    }

    // Private method to load a view with provided data
    private function view($viewName, $data = []) {
        extract($data);
        include_once __DIR__ . "/../views/{$viewName}.php";
    }
}
