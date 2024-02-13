<?php

namespace App\Dao;

use App\Models\Cart;
use App\Models\Book;
use App\Utils\Logger;
use PDOException;


class CartDao {
    private $readerPDO;
    private $writerPDO;

    public function __construct(\PDO $readerPDO, \PDO $writerPDO) {
        $this->readerPDO = $readerPDO;
        $this->writerPDO = $writerPDO;
    }

    public function addProduct($userID, $logged, $bookID, $quantity){
        try{
            $userIdentifierColumn = $logged ? 'UserID' : 'GuestToken';
            $userParamType = $logged ? \PDO::PARAM_INT : \PDO::PARAM_STR;
        
            // Prepare the INSERT ... ON DUPLICATE KEY UPDATE statement
            $stmtInsertOrUpdate = $this->writerPDO->prepare("
                INSERT INTO CartItems($userIdentifierColumn, BookID, Quantity) 
                VALUES(:userID, :bookID, :quantity)
                ON DUPLICATE KEY UPDATE 
                Quantity = Quantity + VALUES(Quantity)
            ");
        
            $stmtInsertOrUpdate->bindParam(':userID', $userID, $userParamType);
            $stmtInsertOrUpdate->bindParam(':bookID', $bookID, \PDO::PARAM_INT);
            $stmtInsertOrUpdate->bindParam(':quantity', $quantity, \PDO::PARAM_INT);
        
            $stmtInsertOrUpdate->execute();
        
            $_SESSION['cartEmpty'] = false;
            return true;
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            return false;
        }
    }
    
    public function getTotCartItems($userID, $logged){
        try{
            $userIdentifier = $logged ? 'UserID' : 'GuestToken';
            $userParamType = $logged ? \PDO::PARAM_INT : \PDO::PARAM_STR;

            $stmtTotCartElements = $this->readerPDO->prepare("SELECT * FROM CartItems WHERE $userIdentifier = :userID");
            $stmtTotCartElements->bindParam(':userID', $userID, $userParamType);

            $stmtTotCartElements->execute();

            return $stmtTotCartElements->rowCount();
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    } 

    public function getCart($userID, $logged) {
        try{
            $userIdentifier = $logged ? 'UserID' : 'GuestToken';
            $userParamType = $logged ? \PDO::PARAM_INT : \PDO::PARAM_STR;

            $stmtCartElements = $this->readerPDO->prepare("SELECT * FROM CartItems CT INNER JOIN Books B ON CT.bookId = B.bookId WHERE $userIdentifier = :userID");
            $stmtCartElements->bindParam(':userID', $userID, $userParamType);
            $stmtCartElements->execute();

            $cart = new Cart($userID);

            $totElementsInCart = $stmtCartElements->rowCount();

            if($totElementsInCart != 0){
                $totElements = 0;
                while ($row = $stmtCartElements->fetch(\PDO::FETCH_ASSOC)) {
                    $book = new Book(
                        $row['BookID'], 
                        $row['Title'], 
                        $row['Author'], 
                        $row['Description'], 
                        $row['Price'], 
                        $row['CoverImage'], 
                        $row['EbookPath'], 
                        $row['CreatedAt'], 
                        $row['UpdatedAt']
                    );
                    $cart->setCartElement($book, $row['Quantity']);
                    $totElements++;
                }
                $cart->setTotElements($totElements);
            }
            
            return $cart;
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteCart($userID, $logged){
        try{
            $userIdentifier = $logged ? 'UserID' : 'GuestToken';
            $userParamType = $logged ? \PDO::PARAM_INT : \PDO::PARAM_STR;

            $stmtDelete = $this->writerPDO->prepare("DELETE FROM CartItems WHERE $userIdentifier = :userID");
            $stmtDelete->bindParam(':userID', $userID, $userParamType);
            $stmtDelete->execute();
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    public function removeProduct($userID, $logged, $bookID){
        try{
            $userIdentifier = $logged ? 'UserID' : 'GuestToken';
            $userParamType = $logged ? \PDO::PARAM_INT : \PDO::PARAM_STR;

            $stmtDelete = $this->writerPDO->prepare("DELETE FROM CartItems WHERE $userIdentifier = :userID AND BookID = :bookID");
            $stmtDelete->bindParam(':userID', $userID, $userParamType);
            $stmtDelete->bindParam(':bookID', $bookID, \PDO::PARAM_INT);
            $stmtDelete->execute();
            if ($this->getTotCartItems($userID, $logged) == 0) {
                $_SESSION['cartEmpty'] = true;
            }
            return $this->getTotalPrice($logged, $userID);
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getTotalPrice($logged, $userID){
        try{
            $userIdentifier = $logged ? 'UserID' : 'GuestToken';
            $userParamType = $logged ? \PDO::PARAM_INT : \PDO::PARAM_STR;

            $stmtGetTotalPrice = $this->readerPDO->prepare("SELECT SUM(C.Quantity * B.Price) AS Total FROM Books B INNER JOIN CartItems C ON B.BookID = C.BookID WHERE C.$userIdentifier = :userID");
            $stmtGetTotalPrice->bindParam(':userID', $userID, $userParamType);
            $stmtGetTotalPrice->execute();

            $totalPrice = 0;
            if($row = $stmtGetTotalPrice->fetch(\PDO::FETCH_ASSOC))
                $totalPrice = $row['Total'];

            return $totalPrice ?: 0;
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    public function transferAnonymousCart($guestToken, $userID){
        try{
            $stmtUpdateCart = $this->writerPDO->prepare("UPDATE CartItems SET UserID = :userID, GuestToken = NULL WHERE GuestToken = :guestToken");
            $stmtUpdateCart->bindParam(':userID', $userID, \PDO::PARAM_INT);
            $stmtUpdateCart->bindParam(':guestToken', $guestToken, \PDO::PARAM_STR);
            $stmtUpdateCart->execute();
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function updateCartAtLogin($userID){
        try{
            $guestToken = session_id();
            $anonCart = $this->getCart($guestToken, false); // Get anon cart
        
            if($anonCart->getTotElements() == 0) {
                return;
            } else {
                $this->transferAnonymousCart($guestToken, $userID);
                $this->deleteCart($guestToken, false);
            }
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }
    
}
