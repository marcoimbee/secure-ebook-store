<?php

namespace App\Dao;

use App\Utils\Logger;
use PDOException;


class UsernameBlockerDao {
    private $readerPDO;
    private $writerPDO;
    private $defaultAttempts;

    public function __construct(\PDO $readerPDO, \PDO $writerPDO, $defaultAttempts) {
        $this->readerPDO = $readerPDO;
        $this->writerPDO = $writerPDO;
        $this->defaultAttempts = $defaultAttempts;
    }

    // Check if the given username can perform a login attempt in a brute-force scenario.
    public function checkUsernameLoginBruteforce($username) {
        $usernameAlreadyRegistered = $this->isUsernameRegistered($username);
        if ($usernameAlreadyRegistered) {
            $remainingAttempts = $this->getRemainingAttempts($username);
            if ($remainingAttempts == 1) {
                $this->updateRemainingAttempts($username);
                $this->blockUsername($username);
                return false;
            } else {
                $this->updateRemainingAttempts($username);
                return true;
            }
        } else {
            $this->addUsername($username);
            return true;
        }
    }

    // Add the given username to the database.
    public function addUsername($username) {
        try {
            $stmtAddUsername = $this->writerPDO->prepare('INSERT INTO UsernameController(Username, RemainingAttempts, Blocked) VALUES (:username, :remainingAttempts, 0)');
            $stmtAddUsername->bindParam(':username', $username, \PDO::PARAM_STR);
            $defaultAttemptsValue = $this->defaultAttempts - 1;
            $stmtAddUsername->bindParam(':remainingAttempts', $defaultAttemptsValue, \PDO::PARAM_INT);
            $stmtAddUsername->execute();
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    // Block the given username.
    public function blockUsername($username) {
        try {
            $stmtUpdateBlocked = $this->writerPDO->prepare('UPDATE UsernameController SET Blocked = 1 WHERE Username = :username');
            $stmtUpdateBlocked->bindParam(':username', $username, \PDO::PARAM_STR);
            $stmtUpdateBlocked->execute();
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    // Update the remaining login attempts for the given username.
    public function updateRemainingAttempts($username) {
        try {
            $stmtUpdateAttempts = $this->writerPDO->prepare('UPDATE UsernameController SET RemainingAttempts = RemainingAttempts - 1 WHERE Username = :username');
            $stmtUpdateAttempts->bindParam(':username', $username, \PDO::PARAM_STR);
            $stmtUpdateAttempts->execute();
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    // Restore the remaining login attempts after an error for the given username.
    public function restoreRemainingAttemptsAfterError($username) {
        try {
            $stmtRestore = $this->writerPDO->prepare('UPDATE UsernameController SET RemainingAttempts = RemainingAttempts + 1 WHERE Username = :username');
            $stmtRestore->bindParam(':username', $username, \PDO::PARAM_STR);
            $stmtRestore->execute();
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    // Check if the given username is registered in the database.
    public function isUsernameRegistered($username) {
        try {
            $stmtUsernameRegistered = $this->readerPDO->prepare('SELECT * FROM UsernameController WHERE Username = :username');
            $stmtUsernameRegistered->bindParam(':username', $username, \PDO::PARAM_STR);
            $stmtUsernameRegistered->execute();
            return $stmtUsernameRegistered->rowCount();
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    // Remove the given username after a successful login.
    public function removeUsernameAfterSuccessfulLogin($username) {
        try {
            $stmtDeleteUsername = $this->writerPDO->prepare('DELETE FROM UsernameController WHERE Username = :username');
            $stmtDeleteUsername->bindParam(':username', $username, \PDO::PARAM_STR);
            $stmtDeleteUsername->execute();
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    // Get the remaining login attempts for the given username.
    public function getRemainingAttempts($username) {
        try {
            $stmtGetAttempts = $this->readerPDO->prepare('SELECT RemainingAttempts FROM UsernameController WHERE Username = :username');
            $stmtGetAttempts->bindParam(':username', $username, \PDO::PARAM_STR);
            $stmtGetAttempts->execute();

            while ($row = $stmtGetAttempts->fetch(\PDO::FETCH_ASSOC))
                $remainingAttempts = $row['RemainingAttempts'];

            return $remainingAttempts;
        } catch (PDOException $e) {
            Logger::error('An exception occurred while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    // Check if the given usernames is blocked.
    public function isUsernameBlocked($username) {
        try {
            if ($this->isUsernameRegistered($username)) {
                $stmtGetBlocked = $this->readerPDO->prepare('SELECT Blocked FROM UsernameController WHERE Username = :username');
                $stmtGetBlocked->bindParam(':username', $username, \PDO::PARAM_STR);
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
