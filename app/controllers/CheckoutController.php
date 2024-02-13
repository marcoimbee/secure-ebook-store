<?php

namespace App\Controllers;

use App\Dao\CheckoutDao;
use App\Database\Database;
use App\Utils\InputChecks;
use App\Utils\IpUtils;
use App\Utils\Logger;


class CheckoutController {
    private $checkOutDao;
    private $errors = [];

    public function __construct(Database $database) {
        $this->checkOutDao = new CheckoutDao($database->getReadConnection(), $database->getWriteConnection());
    }

    // Method to check if the shipment is completed and redirect accordingly
    public function checkShipmentCompleted() {
        $step = $_SESSION["shipmentCompleted"] ?? null;

        if(!$step) {
            if($_SESSION["cartEmpty"]) {
                header("Location: /");
            } else {
                header("Location: /shipment");
            }
            exit;
        }
    }

    // Method to display the checkout form
    public function showCheckoutForm() {
        $this->checkShipmentCompleted();
        include_once __DIR__ . "/../views/checkout.php";
    }

    // Method to process the checkout form
    public function processCheckout() {
        $this->checkShipmentCompleted();
        $submittedCSRFToken = $_POST['HTTP_X_CSRF_TOKEN'] ?? ''; // token included in header
        $storedCSRFToken = $_SESSION['HTTP_X_CSRF_TOKEN'] ?? ''; // token stored on page (hidden)

        // Check for CSRF token mismatch
        if (!hash_equals($storedCSRFToken, $submittedCSRFToken)) {
            $this->errors['CSRF'] = "CSRF tokens mismatch";
            $this->showCheckoutForm();
            exit;
        }
        // Retrieve and sanitize user input
        $creditCardNumber = isset($_POST['creditCardNumber']) ? $_POST['creditCardNumber'] : '';
        $cvv = isset($_POST['cvv']) ? $_POST['cvv'] : '';
        $expirationDate = isset($_POST['expirationDate']) ? $_POST['expirationDate'] : '';
        $cardHolder = isset($_POST['cardHolder']) ? $_POST['cardHolder'] : '';

        if(InputChecks::containsMaliciousScripts($creditCardNumber) || InputChecks::containsMaliciousScripts($cvv) ||
        InputChecks::containsMaliciousScripts($expirationDate) || InputChecks::containsMaliciousScripts($cardHolder)) {
            // Log the security event with user_id if set
            $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
            Logger::security("Malicious script detected in checkout page: " . $userToLog);
        }

        $creditCardNumber = InputChecks::testInput($creditCardNumber);
        $cvv = InputChecks::testInput($cvv);
        $expirationDate = InputChecks::testInput($expirationDate);
        $cardHolder = InputChecks::testInput($cardHolder);

        // Validate the checkout form
        $this->validateCheckoutForm($creditCardNumber, $cvv, $expirationDate, $cardHolder);

        // If there are errors, display the checkout form again
        if (!empty($this->errors)) {
            $this->showCheckoutForm();
            exit;
        }

        // Process user checkout
        $this->processUserCheckout();
    }

    // Private method to validate the checkout form
    private function validateCheckoutForm($creditCardNumber, $cvv, $expirationDate, $cardHolder) {
        // Validate the creditCardNumber
        if (empty($creditCardNumber)) {
            $this->errors['creditCardNumber'] = "Please enter your credit card number.";
        } elseif (!preg_match('/^[0-9]{16}$/', $creditCardNumber)) {
            $this->errors['creditCardNumber'] = 'The credit card number must be a 16 digit number.';
        }

        // Validate the cvv
        if (empty($cvv)) {
            $this->errors['cvv'] = "Please enter your cvv.";
        } elseif (!preg_match('/^[0-9]{3}$/', $cvv)) {
            $this->errors['cvv'] = 'The CVV must be a 3 digit number';
        }

        // Validate the cardHolder
        if (empty($cardHolder)) {
            $this->errors['cardHolder'] = "Please enter the card holder.";
        } elseif (!preg_match("/^[a-zA-Z\s'-]+$/", $cardHolder)) {
            $this->errors['cardHolder'] = 'The card holder must only contain alphabetic characters,
            apostrophes and hyphens.';
        }

        // Validate the expirationDate
        if (empty($expirationDate)) {
            $this->errors['cardHolder'] = "Please enter the expiration date of your card.";
        } elseif (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expirationDate)) {
            $this->errors["expirationDate"] = 'Invalid format. Format must be MM/YY.';
        }

        // If there are errors, return
        if (!empty($this->errors)) {
            return;
        }

        // Split the date into month and year
        list($expMonth, $expYear) = explode('/', $expirationDate);

        // Convert year to four digits (assuming the card won't be from before 2000)
        $expYear = 2000 + intval($expYear);
    
        // Get the current date
        $currentYear = intval(date('Y'));
        $currentMonth = intval(date('m'));
    
        // Check if the card is expired
        if ($expYear < $currentYear || ($expYear == $currentYear && $expMonth < $currentMonth)) {
            $this->errors["expirationDate"] = 'Card is expired.';
        }
    }

    // Private method to process user checkout
    private function processUserCheckout() {
        try {
            // Finalize the checkout and get the purchase ID
            $purchaseId = $this->checkOutDao->finalizeCheckout($_SESSION["user_id"]);
        } catch (\PDOException $e) {
            $this->errors["generic"] = "Something went wrong while finalizing your checkout, please try again.";
            $this->showCheckoutForm();
            exit;
        }

        // Order is completed, unset the shipment completed value and set cartEmpty to true
        unset($_SESSION["shipmentCompleted"]);
        $_SESSION["cartEmpty"] = true;

        // Redirect to the order confirmation page with the purchase ID
        header("Location: /order?id=" . $purchaseId);
    }

    // Method to get validation errors
    public function getErrors() {
        return $this->errors;
    }
}
