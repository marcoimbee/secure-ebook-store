<?php

namespace App\Dao;

use App\Utils\Logger;
use PDOException;


class IPBlockerDao {
    private $readerPDO;
    private $writerPDO;
    private $defaultAttempts;

    public function __construct(\PDO $readerPDO, \PDO $writerPDO, $defaultAttempts) {
        $this->readerPDO = $readerPDO;
        $this->writerPDO = $writerPDO;
        $this->defaultAttempts = $defaultAttempts;
    }

    // Check if the given IP address can change the password.
    public function checkIPAddressChangePassword($ipAddress) {
        $ipAlreadyRegistered = $this->isIpAddressRegistered($ipAddress);
        if ($ipAlreadyRegistered) {
            $remainingAttempts = $this->getRemainingAttempts($ipAddress);
            if ($remainingAttempts >= 1) {
                $this->updateRemainingAttempts($ipAddress);
                return true;
            } else {
                $this->blockIpAddress($ipAddress);
                return false;
            }
        } else {
            $this->addIpAddress($ipAddress);
            return true;
        }
    }

    // Check if the given IP address can perform a login attempt in a brute-force scenario.
    public function checkIPAddressLoginBruteforce($ipAddress) {
        $ipAlreadyRegistered = $this->isIpAddressRegistered($ipAddress);
        if ($ipAlreadyRegistered) {
            $remainingAttempts = $this->getRemainingAttempts($ipAddress);
            if ($remainingAttempts == 1) {
                $this->updateRemainingAttempts($ipAddress);
                $this->blockIpAddress($ipAddress);
                return false;
            } else {
                $this->updateRemainingAttempts($ipAddress);
                return true;
            }
        } else {
            $this->addIpAddress($ipAddress);
            return true;
        }
    }

    // Add the given IP address to the database.
    public function addIpAddress($ipAddress) {
        try {
            $stmtAddIp = $this->writerPDO->prepare('INSERT INTO IPController(IPAddress, RemainingAttempts, Blocked) VALUES (:ipAddress, :remainingAttempts, 0)');
            $stmtAddIp->bindParam(':ipAddress', $ipAddress, \PDO::PARAM_STR);
            $defaultAttemptsValue = $this->defaultAttempts - 1;
            $stmtAddIp->bindParam(':remainingAttempts', $defaultAttemptsValue, \PDO::PARAM_INT);
            $stmtAddIp->execute();
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    // Block the given IP address.
    public function blockIpAddress($ipAddress) {
        try {
            $stmtUpdateBlocked = $this->writerPDO->prepare('UPDATE IPController SET Blocked = 1 WHERE IPAddress = :ipAddress');
            $stmtUpdateBlocked->bindParam(':ipAddress', $ipAddress, \PDO::PARAM_STR);
            $stmtUpdateBlocked->execute();
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    // Update the remaining login attempts for the given IP address.
    public function updateRemainingAttempts($ipAddress) {
        try {
            $stmtUpdateAttempts = $this->writerPDO->prepare('UPDATE IPController SET RemainingAttempts = RemainingAttempts - 1 WHERE IPAddress = :ipAddress');
            $stmtUpdateAttempts->bindParam(':ipAddress', $ipAddress, \PDO::PARAM_STR);
            $stmtUpdateAttempts->execute();
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    // Restore the remaining login attempts after an error for the given IP address.
    public function restoreRemainingAttemptsAfterError($ipAddress) {
        try {
            $stmtRestore = $this->writerPDO->prepare('UPDATE IPController SET RemainingAttempts = RemainingAttempts + 1 WHERE IPAddress = :ipAddress');
            $stmtRestore->bindParam(':ipAddress', $ipAddress, \PDO::PARAM_STR);
            $stmtRestore->execute();
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    // Check if the given IP address is registered in the database.
    public function isIpAddressRegistered($ipAddress) {
        try {
            $stmtIpRegistered = $this->readerPDO->prepare('SELECT * FROM IPController WHERE IPAddress = :ipAddress');
            $stmtIpRegistered->bindParam(':ipAddress', $ipAddress, \PDO::PARAM_STR);
            $stmtIpRegistered->execute();
            return $stmtIpRegistered->rowCount();
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    // Remove the given IP address after a successful login.
    public function removeIpAfterSuccessfulLogin($ipAddress) {
        try {
            $stmtDeleteIp = $this->writerPDO->prepare('DELETE FROM IPController WHERE IPAddress = :ipAddress');
            $stmtDeleteIp->bindParam(':ipAddress', $ipAddress, \PDO::PARAM_STR);
            $stmtDeleteIp->execute();
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    // Get the remaining login attempts for the given IP address.
    public function getRemainingAttempts($ipAddress) {
        try {
            $stmtGetAttempts = $this->readerPDO->prepare('SELECT RemainingAttempts FROM IPController WHERE IPAddress = :ipAddress');
            $stmtGetAttempts->bindParam(':ipAddress', $ipAddress, \PDO::PARAM_STR);
            $stmtGetAttempts->execute();

            while ($row = $stmtGetAttempts->fetch(\PDO::FETCH_ASSOC))
                $remainingAttempts = $row['RemainingAttempts'];

            return $remainingAttempts;
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    // Check if the given IP address is blocked.
    public function isIpBlocked($ipAddress) {
        try {
            if ($this->isIpAddressRegistered($ipAddress)) {
                $stmtGetBlocked = $this->readerPDO->prepare('SELECT Blocked FROM IPController WHERE IPAddress = :ipAddress');
                $stmtGetBlocked->bindParam(':ipAddress', $ipAddress, \PDO::PARAM_STR);
                $stmtGetBlocked->execute();

                while ($row = $stmtGetBlocked->fetch(\PDO::FETCH_ASSOC))
                    $blocked = $row['Blocked'];

                if ($blocked)
                    return true;

                return false;
            }

            return false;
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }
}
