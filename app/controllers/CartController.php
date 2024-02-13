<?php

namespace App\Controllers;

use App\Database\Database;
use App\Dao\CartDao;
use App\Utils\InputChecks;


class CartController {
    private $cartDao;
    private $errors;

    // Constructor that initializes the CartDao with read and write connections from the provided Database
    public function __construct(Database $database) {
        $this->cartDao = new CartDao($database->getReadConnection(), $database->getWriteConnection());
    }

    // Method to display the contents of the user's cart
    public function showCart() {
        if (!isset($_SESSION['user_id'])) { // anonymous user
            $sessionID = session_id(); // get the anonymous user's session ID
            $cart = $this->cartDao->getCart($sessionID, false); // false -> scan DB using sessionID
        } else { // logged user
            $cart = $this->cartDao->getCart($_SESSION['user_id'], true); // true -> scan DB using userID
        }

        // Display cart elements
        include_once __DIR__ . '/../views/cart.php';
    }

    // Private method to send a JSON response with appropriate HTTP status code
    private function sendJSONResponse($httpResponseCode, $arrKey, $arrValue, $returnedArr) {
        $result = array($arrKey => $arrValue);
        http_response_code($httpResponseCode);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    // Method to remove a product from the cart
    public function removeFromCart() {
        $submittedCSRFToken = getallheaders()['X-CSRF-TOKEN'] ?? ''; // token included in header
        $storedCSRFToken = $_SESSION['HTTP_X_CSRF_TOKEN'] ?? ''; // token stored on page (hidden)

        // Check for CSRF token mismatch
        if (!hash_equals($storedCSRFToken, $submittedCSRFToken)) {
            $this->sendJSONResponse(403, 'CSRFError', 'CSRF token mismatch', $this->errors);
        }

        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        // Check for JSON decoding error
        $removeProductTemplate = ["bookID" => "string"];
        if(!InputChecks::validateJsonAgainstTemplate($data, $removeProductTemplate)) {
            $this->sendJSONResponse(400, 'JSONError', 'Invalid JSON data', $this->errors);
        }

        $bookID = $data['bookID'];

        // Remove product from the cart based on user type (anonymous or logged)
        if (!isset($_SESSION['user_id'])) { // anonymous user
            $sessionID = session_id(); // get the anonymous user's session ID
            $newTotalPrice = $this->cartDao->removeProduct($sessionID,
             false, $bookID); // false -> scan DB using sessionID
        } else { // logged user
            $newTotalPrice = $this->cartDao->removeProduct($_SESSION['user_id'],
             true, $bookID); // true -> scan DB using userID
        }

        // Check for SQL execution error
        if (!$newTotalPrice && gettype($newTotalPrice) == 'boolean') {
            $this->sendJSONResponse(500, 'SQLError', 'Error while executing a DB request', $this->errors);
        }

        // Send success response with new total price
        $successArr = [];
        $this->sendJSONResponse(200, 'newTotalPrice', $newTotalPrice, $successArr);
    }

    // Method to add a product to the cart
    public function addToCart() {
        $submittedCSRFToken = getallheaders()['X-CSRF-TOKEN'] ?? ''; // token included in header
        $storedCSRFToken = $_SESSION['HTTP_X_CSRF_TOKEN'] ?? ''; // token stored on page (hidden)
        // Check for CSRF token mismatch
        if (!hash_equals($storedCSRFToken, $submittedCSRFToken)) {
            $this->sendJSONResponse(403, 'CSRFError',  "CSRF Token mismatch", $this->errors);
        }

        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        // Check for JSON schema validity
        $addToCartTemplate = ["bookID" => "string", "quantity" => "string"];
        if(!InputChecks::validateJsonAgainstTemplate($data, $addToCartTemplate)) {
            $this->sendJSONResponse(400, 'JSONError', 'Invalid JSON data', $this->errors);
        }

        $bookID = $data['bookID'];
        $quantity = $data['quantity'];


        // Add product to the cart based on user type (anonymous or logged)
        if (!isset($_SESSION['user_id'])) { // anonymous user
            $sessionID = session_id(); // get the anonymous user's session ID
            $insertOutcome = $this->cartDao->addProduct($sessionID,
             false, $bookID, $quantity); // false -> scan DB using sessionID
        } else { // logged user
            $insertOutcome = $this->cartDao->addProduct($_SESSION['user_id'],
             true, $bookID, $quantity); // true -> scan DB using userID
        }

        // Check for SQL execution error
        if (!$insertOutcome) {
            $this->sendJSONResponse(500, 'SQLError', 'Error while executing a DB request', $this->errors);
        }

        // Send success response
        $successArr = [];
        $this->sendJSONResponse(200, 'success', 'Request successfully processed', $successArr);
    }
}
