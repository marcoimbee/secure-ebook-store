<?php

namespace App\Dao;

use App\Models\Book;
use App\Utils\Logger;
use PDOException;


class BookDao {
    private $readerPDO;
    private $writerPDO;

    public function __construct(\PDO $readerPDO, \PDO $writerPDO) {
        $this->readerPDO = $readerPDO;
        $this->writerPDO = $writerPDO;
    }

    public function getAllBooks() {
        try {
            $stmt = $this->readerPDO->prepare("SELECT * FROM Books");
            $stmt->execute();

            $books = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $books[] = new Book($row['BookID'], $row['Title'], $row['Author'], $row['Description'], 
                $row['Price'], $row['CoverImage'], $row['EbookPath'], $row['CreatedAt'], $row['UpdatedAt']);
            }
            return $books;
        } catch (PDOException $e) {
            Logger::error('An exception occured while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getBookById($bookId) {
        try {
            $query = "SELECT * FROM Books WHERE BookID = :bookId";
            $statement = $this->readerPDO->prepare($query);
            $statement->bindParam(':bookId', $bookId, \PDO::PARAM_INT);

            if ($statement->execute()) {
                return $statement->fetch(\PDO::FETCH_ASSOC);
            }

            return null; // Return null if the query fails or book is not found
        } catch (PDOException $e) {
            Logger::error('An exception occured while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getBooksByKeyword($keyword) {
        try {
            $searchTerm = '%' . $keyword . '%';

            $stmt = $this->readerPDO->prepare("SELECT * FROM Books WHERE Author LIKE :keyword OR Title LIKE :keyword");
            $stmt->bindParam(':keyword', $searchTerm, \PDO::PARAM_STR);
            $stmt->execute();
            
            $books = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $books[] = new Book($row['BookID'], $row['Title'], $row['Author'], $row['Description'], 
                $row['Price'], $row['CoverImage'], $row['EbookPath'], $row['CreatedAt'], $row['UpdatedAt']);
            }
            return $books;
        } catch (PDOException $e) {
            Logger::error('An exception occured while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getEbookDetailsByDownloadToken($token) {
        try {
            $stmt = $this->readerPDO->prepare("
                SELECT b.EbookPath, b.Title, dt.UserID
                FROM DownloadTokens dt
                JOIN Books b ON dt.BookID = b.BookID
                WHERE dt.Token = :token
            ");
            $stmt->bindParam(':token', $token, \PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error('An exception occured while executing a query: ' . $e->getMessage());
            throw $e;
        }
    }
}
