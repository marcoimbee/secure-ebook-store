DROP DATABASE IF EXISTS ebook_store;
CREATE DATABASE IF NOT EXISTS ebook_store
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE ebook_store;

-- Read-Only User
CREATE USER IF NOT EXISTS 'ebook_readonly'@'localhost' IDENTIFIED BY 'root';
GRANT SELECT ON ebook_store.* TO 'ebook_readonly'@'localhost';

-- Read-Write User
CREATE USER IF NOT EXISTS 'ebook_readwrite'@'localhost' IDENTIFIED BY 'root';
GRANT SELECT, INSERT, UPDATE, DELETE ON ebook_store.* TO 'ebook_readwrite'@'localhost';

-- Admin User 
CREATE USER IF NOT EXISTS 'ebook_admin'@'localhost' IDENTIFIED BY 'root';
GRANT ALL PRIVILEGES ON ebook_store.* TO 'ebook_admin'@'localhost';


-- Users Table
CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Email VARCHAR(100) NOT NULL UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL,
    IsAdmin BOOLEAN NOT NULL DEFAULT FALSE,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Books Table
CREATE TABLE Books (
    BookID INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(100) NOT NULL,
    Author VARCHAR(100),
    Description TEXT,
    Price DECIMAL(10, 2) NOT NULL,
    CoverImage VARCHAR(255),  
    EbookPath VARCHAR(255) NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Purchases Table
CREATE TABLE Purchases (
    PurchaseID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    TotalAmount DECIMAL(10, 2) NOT NULL,
    PurchaseDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- PurchaseDetails Table
CREATE TABLE PurchaseDetails (
    PurchaseDetailID INT AUTO_INCREMENT PRIMARY KEY,
    PurchaseID INT NOT NULL,
    BookID INT NOT NULL,
    Quantity INT NOT NULL,
    PriceAtPurchase DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (PurchaseID) REFERENCES Purchases(PurchaseID),
    FOREIGN KEY (BookID) REFERENCES Books(BookID)
);

-- CartItems Table
CREATE TABLE CartItems (
    CartItemID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT,  -- Can be NULL for anonymous users
    GuestToken VARCHAR(255),  -- For identifying carts of anonymous users
    BookID INT NOT NULL,
    Quantity INT NOT NULL,
    FOREIGN KEY (BookID) REFERENCES Books(BookID),
    CONSTRAINT unique_user_book UNIQUE (UserID, BookID),
    CONSTRAINT unique_guest_book UNIQUE (GuestToken, BookID)
);

-- DownloadTokens Table
CREATE TABLE DownloadTokens (
    TokenID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    BookID INT NOT NULL,
    Token VARCHAR(255) NOT NULL UNIQUE,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID),
    FOREIGN KEY (BookID) REFERENCES Books(BookID)
);

-- ResetPasswordTokens Table
CREATE TABLE ResetPasswordTokens (
	TokenID INT AUTO_INCREMENT PRIMARY KEY,
    Token VARCHAR(255) NOT NULL UNIQUE,
    UserID INT NOT NULL,
    ExpiresAt TIMESTAMP NOT NULL,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- IPController Table
CREATE TABLE IPController (
    IPControllerID INT AUTO_INCREMENT PRIMARY KEY,
    IPAddress VARCHAR(15) NOT NULL UNIQUE,
    RemainingAttempts INT NOT NULL,
    Blocked BOOLEAN,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- UsernameController Table
CREATE TABLE UsernameController (
    UsernameControllerID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(15) NOT NULL UNIQUE,
    RemainingAttempts INT NOT NULL,
    Blocked BOOLEAN,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


SET GLOBAL event_scheduler = ON;

-- Event to clear up UsernameController table
CREATE EVENT IF NOT EXISTS clean_blocked_username
ON SCHEDULE EVERY 1 HOUR
DO
    DELETE FROM UsernameController WHERE CreatedAt < DATE_SUB(NOW(), INTERVAL 1 HOUR);

-- Event to clear up IPController table
CREATE EVENT IF NOT EXISTS clean_blocked_ip
ON SCHEDULE EVERY 1 HOUR
DO
    DELETE FROM IPController WHERE CreatedAt < DATE_SUB(NOW(), INTERVAL 1 HOUR);

-- Event to clear up ResetPasswordTokens table
CREATE EVENT IF NOT EXISTS flush_expired_tokens
ON SCHEDULE EVERY 1 DAY STARTS TIMESTAMP(CURRENT_DATE, '00:00:00')
DO
    DELETE FROM ResetPasswordTokens WHERE ExpiresAt < NOW();


-- Apply changes
FLUSH PRIVILEGES;
