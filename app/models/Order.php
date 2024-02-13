<?php

namespace App\Models;


class Order {
    private $dateTime;
    private $orderId;
    private $totalAmount;
    private $userId;

    public function __construct($orderId, $userId, $totalAmount, $dateTime) {
        $this->dateTime = $dateTime;
        $this->orderId = $orderId;
        $this->totalAmount = $totalAmount;
        $this->userId = $userId;
    }

    public function getId() {
        return $this->orderId;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getTotalAmount() {
        return $this->totalAmount;
    }

    public function getDateTime() {
        return $this->dateTime;
    }
}
