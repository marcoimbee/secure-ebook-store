<?php

namespace App\Controllers;

use App\Database\Database;
use App\Dao\BookDao;
use App\Utils\InputChecks;
use App\Utils\IpUtils;
use App\Utils\Logger;

class DownloadController {
    private $booksDao;

    public function __construct(Database $database) {
        $this->booksDao = new BookDao($database->getReadConnection(), $database->getWriteConnection());
    }

    // Method to process the download request
    public function processDownload() {

        // Get the download token from the GET parameters
        $token = $_GET['token'] ?? null;

        // Check if the token is missing
        if (!$token) {
            // Redirect to 404 page
            include_once __DIR__ . "/../views/404.php";
            exit;
        } else {
            // Check for malicious scripts in the token
            if (InputChecks::containsMaliciousScripts($token)) {
                // Log the security event with user_id if set
                $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
                Logger::security("Malicious script detected in download token: " . $userToLog);
            }
        }

        // Retrieve ebook details by download token
        $ebookDetails = $this->booksDao->getEbookDetailsByDownloadToken($token);
        $ebookPath = $ebookDetails ? __DIR__ . "/../.." . $ebookDetails["EbookPath"] : "";

        // Check if the ebook details are not found or if the file doesn't exist
        if (!$ebookDetails || !file_exists($ebookPath)) {
            // Redirect to 404 page
            include_once __DIR__ . "/../views/404.php";
            exit;
        }

        // Check if the user doesn't have permission
        if ($_SESSION['user_id'] != $ebookDetails['UserID']) {
            // Redirect to 401 page (Unauthorized)
            include_once __DIR__ . "/../views/401.php";
            Logger::security("Unauthorized ebook download attempt - User ID: {$_SESSION['user_id']}, Ebook ID: {$ebookDetails['EbookID']}");
            exit;
        }

       // Set headers for file download
       header('Content-Type: application/octet-stream');
       header('Content-Disposition: attachment; filename="' . basename($ebookDetails['Title']) . '.epub"');
       header('Content-Length: ' . filesize($ebookPath));

       // Output the file content
       readfile($ebookPath);
       exit;
    }
}
