<?php

namespace App\Controllers;

use App\Database\Database;
use App\dao\UserDao;
use App\Utils\InputChecks;
use App\Utils\IpUtils;
use App\Utils\Logger;


class OrderController {
    private $errors = [];
    private $userDao;

    public function __construct(Database $database) {
        $this->userDao = new userDao($database->getReadConnection(), $database->getWriteConnection());
    }

    // Method to display the order details
    public function showOrder() {
        // Get purchase ID from GET parameters
        $purchaseId = $_GET["id"] ?? '';

        // Check for malicious scripts in the purchase ID
        if (!empty($purchaseId) && InputChecks::containsMaliciousScripts($purchaseId)) {
            // Log the security event with user_id if set
            $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
            Logger::security("Malicious script detected in order page: " . $userToLog);
        }

        if (!isset($_SESSION['user_id'])) {
            Logger::security("Unauthorized access to order page: " . IpUtils::getClientIp());
            http_response_code(401);
            include_once __DIR__ . "/../views/401.php";
            exit;
        }

        // Retrieve order details from the UserDao
        $orderDetails = $this->userDao->getUserOrder($_SESSION['user_id'], $purchaseId);

        // Check if the purchase ID or order details are empty
        if (empty($purchaseId) || empty($orderDetails)) {
            http_response_code(404);
            include_once __DIR__ . "/../views/404.php";
            exit;
        }

        // Include the order view and pass the order details to it
        include_once __DIR__ . "/../views/order.php";
    }

    // Method to get validation errors
    public function getErrors() {
        return $this->errors;
    }
}
