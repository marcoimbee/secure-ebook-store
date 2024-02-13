<?php

namespace App\Controllers;

use App\Utils\InputChecks;
use App\Utils\ValidProvinces;
use App\Utils\IpUtils;
use App\Utils\Logger;


class ShipmentController {
    private $errors = [];

    public function processShipment() {     // localhost/shipment
        if($_SESSION['cartEmpty']) {
            header("Location: /");
            exit;
        }
        $submittedCSRFToken = $_POST['HTTP_X_CSRF_TOKEN'] ?? ''; // token included in header
        $storedCSRFToken = $_SESSION['HTTP_X_CSRF_TOKEN'] ?? ''; // token stored on page (hidden)

        // Check for CSRF token mismatch
        if (!hash_equals($storedCSRFToken, $submittedCSRFToken)) {
            $this->errors['CSRF'] = "CSRF tokens mismatch";
            $this->showShipment();
            exit;
        }
        // Input sanitization and trimming
        $address = $_POST['address'];
        if (InputChecks::containsMaliciousScripts($address)) {
            // Log the security event with user_id if set
            $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
            Logger::security("Malicious script detected in shipment page: " . $userToLog);
        }
        $address =  InputChecks::testInput($address);
        $houseNumber =  $_POST['houseNumber'];
        if (InputChecks::containsMaliciousScripts($houseNumber)) {
            // Log the security event with user_id if set
            $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
            Logger::security("Malicious script detected in shipment page: " . $userToLog);
        }
        $houseNumber =  InputChecks::testInput($houseNumber);
        $zipCode =  $_POST['zipCode'];
        if (InputChecks::containsMaliciousScripts($zipCode)) {
            // Log the security event with user_id if set
            $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
            Logger::security("Malicious script detected in shipment page: " . $userToLog);
        }
        $city =  $_POST['city'];
        if (InputChecks::containsMaliciousScripts($city)) {
            // Log the security event with user_id if set
            $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
            Logger::security("Malicious script detected in shipment page: " . $userToLog);
        }
        $city =  InputChecks::testInput($city);
        $province = $_POST['province'];
        if (InputChecks::containsMaliciousScripts($province)) {
            // Log the security event with user_id if set
            $userToLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : IpUtils::getClientIp();
            Logger::security("Malicious script detected in shipment page: " . $userToLog);
        }
    
        if (strlen($address) > 60)
            $this->errors['address'] = 'Address must not exceed 60 characters.';
    
        if (strlen($houseNumber) > 10 &&  (!preg_match('/^[a-zA-Z0-9]+$/',$houseNumber)))
            $this->errors['houseNumber'] = 'House number must be alphanumeric and not exceed 10 characters.';
    
        if (!preg_match('/^[0-9]{5}$/', $zipCode))
            $this->errors['zipCode'] = 'CAP must be a valid Italian ZIP code (5 digits).';
    
        if (strlen($city) > 30 || !preg_match('/^[a-zA-Z\s]+$/', $city))
            $this->errors['city'] = 'City name must not exceed 30 characters and contain only letters.';
    
        if (!in_array($province, ValidProvinces::$italianProvinces))
            $this->errors['province'] = 'Province must be a valid Italian province.';

        if (!empty($this->errors)) {
            $this->showShipment();
            exit;
        }

        $_SESSION["address"] = $address;
        $_SESSION["houseNumber"] = $houseNumber;
        $_SESSION["zipCode"] = $zipCode;
        $_SESSION["city"] = $city;
        $_SESSION["province"] = $province;
        $_SESSION["shipmentCompleted"] = true;

        header("Location: checkout");
    }

    public function showShipment() {
        if($_SESSION['cartEmpty']) {
            header("Location: /");
            exit;
        }
        include_once __DIR__ . "/../views/shipment.php";
    }

    public function getErrors() {
        return $this->errors;
    }
}