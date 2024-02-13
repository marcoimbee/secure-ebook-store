<?php

namespace App\Models;


class Book {
    private $bookID;
    private $title;
    private $author;
    private $description;
    private $price;
    private $coverImage;
    private $ebookPath;
    private $createdAt;
    private $updatedAt;

    public function __construct($bookID, $title, $author, $description, $price, $coverImage, $ebookPath, $createdAt, $updatedAt) {
        $this->bookID = $bookID;
        $this->title = $title;
        $this->author = $author;
        $this->description = $description;
        $this->price = $price;
        $this->coverImage = $coverImage;
        $this->ebookPath = $ebookPath;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId() {
        return $this->bookID;
    }
    
    public function getTitle() {
        return $this->title;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getCoverImage() {
        return $this->coverImage;
    }

    public function getEbookPath() {
        return $this->ebookPath;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setAuthor($author) {
        $this->author = $author;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setPrice($price) {
        $this->price = $price;
    }

    public function setCoverImage($coverImage) {
        $this->coverImage = $coverImage;
    }

    public function setEbookPath($path) {
        $this->ebookPath = $path;
    }

}
