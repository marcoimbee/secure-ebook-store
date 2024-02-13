<?php

namespace App\Dao;

use App\Utils\TokenGenerator;
use App\Utils\Logger;
use PDOException;


class CheckoutDao {
    private $readerPDO;
    private $writerPDO;

    public function __construct(\PDO $readerPDO, \PDO $writerPDO) {
        $this->readerPDO = $readerPDO;
        $this->writerPDO = $writerPDO;
    }

    public function createDownloadToken($userId, $bookId) {
        try {
            $token = TokenGenerator::generateToken();

            $stmt = $this->readerPDO->prepare("SELECT * FROM DownloadTokens WHERE UserID = :userId AND BookID = :bookId");
            $stmt->bindParam(":userId", $userId);
            $stmt->bindParam(":bookId", $bookId);
            $stmt->execute();
            $tokenExists = $stmt->fetch(\PDO::FETCH_ASSOC);
            if($tokenExists) return; // token already present for that UserID-BookID pair

            $stmt = $this->writerPDO->prepare("INSERT INTO DownloadTokens (UserID, BookID, Token) VALUES (:userId, :bookId, :token)");
            $stmt->bindParam(":userId", $userId);
            $stmt->bindParam(":bookId", $bookId);
            $stmt->bindParam(":token", $token);
            $stmt->execute();
        } catch (PDOException $e) {
            Logger::error("An exception occurred while creating donwload token " . $e->getMessage());
            throw $e;
        } 
    }

    public function finalizeCheckout($userId) {
        try {
            $this->writerPDO->beginTransaction();

            // creazione dell'ordine
            // Call getCartItems function to retrieve cart items
            $cartItems = $this->getCartItems($userId);
            // Create a purchase record
            $purchaseId = $this->createPurchase($userId, $cartItems);
            // Transfer items to purchaseDetails
            $this->transferItemsToPurchaseDetails($purchaseId, $cartItems);

            // creazione dei download link
            foreach($cartItems as $item) {
                $this->createDownloadToken($userId, $item["BookID"]);
            }

            // pulizia del carrello (DB)
            $this->clearCart($userId);
            
            $this->writerPDO->commit();
            return $purchaseId;
       } catch (PDOException $e) {
            Logger::error("An exception occurred while finalizing checkout " . $e->getMessage());
            $this->writerPDO->rollBack();
            throw $e;
        } 
    }

    private function getCartItems($userId) {
        try {
            $query = "SELECT ci.BookID, ci.Quantity, b.Price
                    FROM CartItems ci
                    JOIN Books b ON ci.BookID = b.BookID
                    WHERE UserID = :userId";
            $stmt = $this->writerPDO->prepare($query);
            $stmt->bindParam(":userId", $userId, \PDO::PARAM_INT);
            $stmt->execute();
            $cartItems = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $stmt->closeCursor();

        
            return $cartItems;
        } catch (PDOException $e) {
            Logger::error("An exception occurred while fetching items by the user cart: " . $e->getMessage());
            throw $e;
        }
    }

    public function createPurchase($userId, $cartItems) {
        try {
            // Calculate total amount from purchase details
            $totalAmount = 0;

            foreach ($cartItems as $cartItem) {
                // Assuming $cartItem['Quantity'] and $cartItem['Price'] are present in each cart item
                $totalAmount += $cartItem['Quantity'] * $cartItem['Price'];
            }

            // Create a purchase record
            $query = "INSERT INTO Purchases (UserID, TotalAmount) VALUES (:id, :amount)";
            $stmt = $this->writerPDO->prepare($query);
            $stmt->bindParam(":id", $userId, \PDO::PARAM_INT);
            $stmt->bindParam(":amount", $totalAmount);
            $stmt->execute();

            // Get the last inserted ID (AutoIncrement primary key)
            return $this->writerPDO->lastInsertId();
        } catch (PDOException $e) {
            Logger::error("An exception occurred while creating the purchase: " . $e->getMessage());
            throw $e;
        }
    }

    public function transferItemsToPurchaseDetails($purchaseId, $cartItems) {
        try {
            // Transfer items to purchaseDetails
            $query = "INSERT INTO PurchaseDetails (PurchaseID, BookID, Quantity, PriceAtPurchase)
                    VALUES (:purchaseId, :bookId, :quantity, :price)";
            $stmt = $this->writerPDO->prepare($query);

            foreach ($cartItems as $cartItem) {
                $stmt->bindParam(":purchaseId", $purchaseId);
                $stmt->bindParam(":bookId", $cartItem['BookID']);
                $stmt->bindParam(":quantity", $cartItem['Quantity']);
                $stmt->bindParam(":price", $cartItem['Price']);
                $stmt->execute();
            }
        } catch (PDOException $e) {
            Logger::error("An exception occurred while transferring items to purchase: ".$e->getMessage());
            throw $e;
        }

    }

    public function clearCart($userId) {
        try {
            $deleteCartItemsQuery = "DELETE FROM CartItems  WHERE UserID = :userId";
            $deleteCartItemsStmt = $this->writerPDO->prepare($deleteCartItemsQuery);
            $deleteCartItemsStmt->bindParam(":userId", $userId, \PDO::PARAM_INT);
            $deleteCartItemsStmt->execute();

        } catch (PDOException $e) {
            Logger::error("An exception occurred while clearing cart: ".$e->getMessage());
            throw $e;
        }
    }
}
