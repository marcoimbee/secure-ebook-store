<?php

namespace App\Dao;

use App\Models\User;
use App\Models\Order;
use App\Utils\Logger;
use PDOException;


class UserDao {
    private $readerPDO;
    private $writerPDO;

    public function __construct(\PDO $readerPDO, \PDO $writerPDO) {
        $this->readerPDO = $readerPDO;
        $this->writerPDO = $writerPDO;
    }

    public function getUserById($userId) {
        try {
            $stmt = $this->readerPDO->prepare("SELECT * FROM Users WHERE UserID = :userId");
            $stmt->execute(['userId' => $userId]);
            $userData = $stmt->fetch(\PDO::FETCH_ASSOC);
            return new User($userData['UserID'], $userData['Username'], $userData['PasswordHash'], $userData['Email']);
        } catch (PDOException $e) {
            Logger::error('An exception occured while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getUserByUsername($username) {
        try {
            $stmt = $this->readerPDO->prepare('SELECT * FROM Users WHERE Username = :username');
            $stmt->execute(['username' => $username]);
            $userData= $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($userData === false) {
                return null; 
            }
            return new User($userData['UserID'], $userData['Username'], $userData['PasswordHash'], $userData['Email']);
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getUserByEmail($email) {
        try {
            $stmt = $this->readerPDO->prepare('SELECT * FROM Users WHERE Email = :email');
            $stmt->execute([':email' => $email]);
            $userData = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($userData === false) {
                return null; 
            }
            return new User($userData['UserID'], $userData['Username'], $userData['PasswordHash'], $userData['Email']);
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createUser(User $user) {
        try {
            $stmt = $this->writerPDO->prepare("INSERT INTO users (username, passwordHash, email) VALUES (?, ?, ?)");
            $stmt->execute([$user->getUsername(), $user->getPassword(), $user->getEmail()]);
        } catch (PDOException $e) {
            Logger::error('An exception occured while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getUserByResetToken($resetToken) {
        try {
            $sql = "SELECT * FROM users WHERE ResetToken = :resetToken AND ResetTokenExpiration > NOW()";
            $stmt = $this->readerPDO->prepare($sql);
            $stmt->bindParam(':resetToken', $resetToken, \PDO::PARAM_STR);
            $stmt->execute();
    
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error('An exception occured while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function updatePassword($userId, $hashedPassword) {
        try {
            $sql = "UPDATE users SET PasswordHash = :hashedPassword WHERE UserID = :userId";
            $stmt = $this->writerPDO->prepare($sql);
            $stmt->bindParam(':hashedPassword', $hashedPassword, \PDO::PARAM_STR);
            $stmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
    
            return $stmt->execute();
        } catch (PDOException $e) {
            Logger::error('An exception occured while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function clearResetToken($userId) {
        try {
            $sql = "UPDATE users SET ResetToken = NULL, ResetTokenExpiration = NULL WHERE UserID = :userId";
            $stmt = $this->writerPDO->prepare($sql);
            $stmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
    
            return $stmt->execute();
        } catch (PDOException $e) {
            Logger::error('An exception occured while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    public function associateTokenWithEmail($userId, $token) {
        // Insert a new token record
        try{
            $this->writerPDO->beginTransaction();

            //check if a token already exists for the user
            $stmtCheckTokenExistence = $this->readerPDO->prepare('SELECT * FROM ResetPasswordTokens WHERE UserID = :userId');
            $stmtCheckTokenExistence->bindParam(':userId', $userId, \PDO::PARAM_INT);
            $stmtCheckTokenExistence->execute();

            $tokenExists = $stmtCheckTokenExistence->rowCount();

            if($tokenExists){
                $stmtDeleteExistingToken = $this->writerPDO->prepare('DELETE FROM ResetPasswordTokens WHERE UserID = :userId');
                $stmtDeleteExistingToken->bindParam(':userId', $userId, \PDO::PARAM_INT);
                $stmtDeleteExistingToken->execute();
            }

            // Set expiration time, e.g., 1 hour from now
            $expiresAt = (new \DateTime())->add(new \DateInterval('PT1H'));

            //if another token already exists for the user, replaces it with the new one, otherwise inserts it
            $insertStmt = $this->writerPDO->prepare('INSERT INTO ResetPasswordTokens(Token, UserID, ExpiresAt) VALUES (:token, :userId, :expiresAt)');
        
            $tokenValue = $token;
            $expiresAtValue = $expiresAt->format('Y-m-d H:i:s');

            $insertStmt->bindParam(':token', $tokenValue, \PDO::PARAM_STR);
            $insertStmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
            $insertStmt->bindParam(':expiresAt', $expiresAtValue, \PDO::PARAM_STR);

            $insertStmt->execute();
            
            $this->writerPDO->commit();
        } catch (PDOException $e) {
            Logger::error('An exception occured while executing a query: ' . $e->getMessage());
            $this->writerPDO->rollBack();
            throw $e;
        }
    }
    
    public function isValidToken($token) {
        $tokenData = $this->getTokenByTokenValue($token);

        if (!$tokenData)    // Token not found in the database
            return false;

        if ($this->isTokenExpired($tokenData['ExpiresAt']))     // Token has expired
            return false;

        return true;        // Token is valid
    }

    private function getTokenByTokenValue($token) {
        try {
            $query = 'SELECT * FROM resetPasswordTokens WHERE Token = :token LIMIT 1';
            $params = [':token' => $token];
    
            $stmt = $this->readerPDO->prepare($query);
            $stmt->execute($params);
    
            // Fetch the first row from the result set
            $tokenData = $stmt->fetch(\PDO::FETCH_ASSOC);
    
            return $tokenData ? $tokenData : null;
        } catch (PDOException $e) {
            Logger::error('An exception occured while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function isTokenExpired($expiresAt) {
            $expiresAtDateTime = new \DateTime($expiresAt);
            $currentDateTime = new \DateTime();
    
            return $currentDateTime > $expiresAtDateTime;
    }
    
    public function updatePasswordWithToken($token, $newPassword) {
        try {
            // Start a database transaction
            $this->writerPDO->beginTransaction();
    
            // Validate the token and get user information
            $tokenData = $this->getTokenByTokenValue($token);
    
            // Update the user's password
            $userId = $tokenData['UserID'];
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
            $stmt = $this->writerPDO->prepare('UPDATE Users SET PasswordHash = :password WHERE UserID = :userId');
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
    
            // Delete the token from the database
            $this->deleteToken($token);
    
            // Commit the transaction
            $this->writerPDO->commit();
        } catch (PDOException $e) {
            Logger::error('An exception occured while executing a query: ' . $e->getMessage());
            $this->writerPDO->rollBack();
            throw $e;
        }
    }
    
    private function deleteToken($token) {
        try {
            $stmt = $this->writerPDO->prepare('DELETE FROM ResetPasswordTokens WHERE Token = :token');
            $stmt->bindParam(':token', $token);
            $stmt->execute();
        } catch (PDOException $e) {
            Logger::error('An exception occured while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function getUserOrders($id) {
        try {
            $stmt = $this->readerPDO->prepare('SELECT * FROM Purchases WHERE UserID = :id ORDER BY PurchaseDate DESC');
            $stmt->bindParam(':id', $id, \PDO::PARAM_STR);
            $stmt->execute();
    
            $orders = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $order = new Order($row['PurchaseID'], $row['UserID'], $row['TotalAmount'], $row['PurchaseDate']);
                array_push($orders, $order);
            }
            return $orders;
        } catch (PDOException $e) {
            Logger::error('An exception occured while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateUser(User $user) {
        $password = $user->getPassword();
        $username = $user->getUsername();
        $userId = $user->getId();
        try {
            $stmt = $this->writerPDO->prepare('UPDATE Users SET Username = :username, PasswordHash = :passwordHash WHERE UserID = :userId');
            $stmt->bindParam(':username', $username, \PDO::PARAM_STR);
            $stmt->bindParam(':passwordHash', $password, \PDO::PARAM_STR);
            $stmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            Logger::error('An exception occured while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getEmailViaToken($token) {
        try {
            $stmt = $this->writerPDO->prepare('SELECT U.Email FROM Users U INNER JOIN ResetPasswordTokens R ON U.UserID = R.UserID WHERE R.Token = :token');
            $stmt->bindParam(':token', $token, \PDO::PARAM_STR);
            $stmt->execute();
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                return $row['Email'];
            }
        } catch (PDOException $e) {
            Logger::error('An exception occured while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getUserOrder(int $userId, int $purchaseId) {
        try {
            $stmt = $this->readerPDO->prepare("
                SELECT P.PurchaseID, P.UserID, P.TotalAmount, P.PurchaseDate, PD.Quantity, PD.BookID, B.Title, B.Author, DT.Token
                FROM Purchases P
                    INNER JOIN PurchaseDetails PD ON (P.PurchaseID = PD.PurchaseID)
                    INNER JOIN Books B ON (PD.BookID = B.BookID) 
                    INNER JOIN DownloadTokens DT ON (B.BookId = DT.BookId AND P.UserID = DT.UserID)
                WHERE P.UserID = :userId AND P.PurchaseID = :purchaseId
            ");
            $stmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
            $stmt->bindParam(':purchaseId', $purchaseId, \PDO::PARAM_INT);
            $stmt->execute();

            $orderDetails = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $orderDetail = [
                    $row['PurchaseID'],
                    $row['UserID'],
                    $row['TotalAmount'],
                    $row['PurchaseDate'],
                    $row['Quantity'],
                    $row['BookID'],
                    $row['Title'],
                    $row['Author'],
                    $row['Token']
                ];
                array_push($orderDetails, $row);
            }
            return $orderDetails;
        } catch (PDOException $e) {
            Logger::error('An exception occured while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }
}
