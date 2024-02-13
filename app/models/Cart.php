<?php

namespace App\Models;


class Cart {
    private $userID;           
    private $totElements;
    private $cartElements = array();

    public function __construct($userID) {
        $this->userID = $userID;
        $this->totElements = 0;
        $this->cartElements = [];
    }

    public function getUserID(){
        return $this->userID;
    }

    public function setTotElements($totElements) {
        $this->totElements = $totElements;
    }

    public function getTotElements() {
        return $this->totElements;
    }

    public function getCartElements() {
        return $this->cartElements;
    }

    public function setCartElement($element, $quantity){
        $pushValue = [
            'book' => $element,        
            'quantity' => $quantity
        ];
        array_push($this->cartElements, $pushValue);
    }
}
