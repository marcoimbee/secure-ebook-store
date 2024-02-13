<?php
//generating and setting a CSRF token
$_SESSION['HTTP_X_CSRF_TOKEN'] = bin2hex(random_bytes(32));        //256 bits CSRF secure token

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/navbar.css" />
    <link rel="stylesheet" href="/css/bookDetails.css" />
    <title><?php echo htmlspecialchars($book['Title'], ENT_QUOTES, 'UTF-8'); ?> - Book Details</title>
    <script type="text/javascript" src="/js/addToCart.js"></script>
</head>
<body>
    <?php include_once 'navbar.php'; ?>

    <div class="book-container">
        <div class="book-title">
            <h1><?php echo htmlspecialchars($book['Title'], ENT_QUOTES, 'UTF-8'); ?></h1>
        </div>
        <div class="book-author">
            <p>By <?php echo htmlspecialchars($book['Author'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <div class="columnar-content">
            <div class="column" id="book-cover">
                <img src="<?php echo htmlspecialchars($book['CoverImage'], ENT_QUOTES, 'UTF-8'); ?>"
                    alt="<?php echo htmlspecialchars($book['Title'], ENT_QUOTES, 'UTF-8'); ?> Cover">
            </div>
            <div class="column" id="book-description">
                <p><strong>Description:</strong></p>
                <p><?php echo htmlspecialchars($book['Description'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>
        <div class="columnar-content" id="end-of-container">
            <div class="column">
                <p><strong>Price:</strong> $<?php echo htmlspecialchars($book['Price'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="column">
                <input type="hidden" name="bookId" value="<?php echo htmlspecialchars($book['BookID'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="HTTP_X_CSRF_TOKEN" value="<?= $_SESSION['HTTP_X_CSRF_TOKEN'] ?>">

                <label for="quantity">Quantity:</label>
                <select name="quantity" id="quantity">
                    <?php
                    for ($i = 1; $i <= 10; $i++) {
                        echo "<option value=\"" . htmlspecialchars($i, ENT_QUOTES, 'UTF-8') . "\">"
                        . htmlspecialchars($i, ENT_QUOTES, 'UTF-8') . "</option>";
                    }
                    ?>
                </select>

                <button id="addToCartButton">Add to Cart</button>
            </div>
        </div>
    </div>
</body>
</html>
