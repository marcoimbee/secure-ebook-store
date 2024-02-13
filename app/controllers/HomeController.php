<?php

namespace App\Controllers;

use App\Database\Database;
use App\Dao\BookDao;


class HomeController {
    private $bookDao;

    public function __construct(Database $database) {
        $this->bookDao = new BookDao($database->getReadConnection(), $database->getWriteConnection());
    }

    // Method to handle the homepage and display a list of all books
    public function index() {
        
        // Retrieve all books from the BookDao
        $books = $this->bookDao->getAllBooks();

        // Include the homepage view and pass the list of books to it
        include_once __DIR__ . '/../views/homepage.php';
    }
}
