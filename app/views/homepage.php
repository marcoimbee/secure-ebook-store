<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Homepage</title>
    <link rel="stylesheet" href="/css/navbar.css" />
    <link rel="stylesheet" href="/css/homepage.css" />
    <script src="/js/search.js"></script>
</head>
<body>
    <?php $showSearchBar = true;
    include_once 'navbar.php'; ?>
    <header class="products-header">
        <div class="filter-section">
            <span>■ <?php echo count($books) . ' BOOKS FOUND'; ?></span>
        </div>
        <div class="sort-section">
        </div>
    </header>
    <div class="product-grid">
        <?php foreach($books as $book): ?>
            <div class="product-card">
                <?php
                    $paramName = 'id'; 
                    $paramValue = $book->getId();
                    $URL = 'bookDetails?' . urlencode($paramName) . '=' . urlencode($paramValue);
                ?>
                <a href="<?php echo $URL; ?>">
                    <img src="<?php echo $book->getCoverImage(); ?>" alt="Book Image" class="product-image"/>
                </a>
                <div class="product-info">
                    <a href="<?php echo $URL; ?>">
                        <p class="author-name"><?php echo htmlspecialchars($book->getAuthor()); ?></p>
                        <p class="book-title"><?php echo htmlspecialchars($book->getTitle()); ?></p>
                    </a>
                    <p class="book-price">$<?php echo htmlspecialchars($book->getPrice()); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
