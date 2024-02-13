<?php
//generating and setting a CSRF token
$_SESSION['HTTP_X_CSRF_TOKEN'] = bin2hex(random_bytes(32));        //256 bits CSRF secure token
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cart</title>
    <link rel="stylesheet" href="/css/navbar.css" />
    <link rel="stylesheet" href="/css/cart.css" />
    <link rel="stylesheet" href="/css/common.css" />
    <script defer type="text/javascript" src="/js/cartController.js"></script>
</head>
<body>
    <?php include_once 'navbar.php'; ?>

    <div class="custom-header">
        <div class="text-section">
            <span>■ CART</span>
        </div>
    </div>
    <div class="main-container">
        <?php
        if($cart):
            $cartElements = $cart->getCartElements();
            $count = count($cartElements);

            for ($i = 0; $i < $count; $i++) {
                $cartElement = $cartElements[$i];
                ?>
                    <input type="hidden" name="HTTP_X_CSRF_TOKEN" value="<?= $_SESSION['HTTP_X_CSRF_TOKEN'] ?>">
                    <div class="product-info" id="<?php echo $i ?>">
                        <div hidden class="book-id">
                            <p hidden id="bookId"><?php echo $cartElement['book']->getId(); ?></p>
                        </div>
                        <div class="book-title">
                            <p class="book-title"><?php echo $cartElement['book']->getTitle(); ?></p>
                        </div>
                        <div class="author-name">
                            <p class="author-name"><?php echo $cartElement['book']->getAuthor(); ?></p> 
                        </div>
                        <div class="book-price">
                            <p class="book-price">$<?php echo $cartElement['book']->getPrice(); ?></p>
                        </div>
                        <div class="quantity">
                            <p class="quantity"><?php echo $cartElement['quantity']; ?></p>
                        </div>
                        <div class="remove-button">
                            <button class="remove-from-cart-button">Remove</button>
                        </div>            
                    </div>
            <?php } 

            $totalPrice = 0;
            for($i = 0; $i < $count; $i++){
                $cartElement = $cartElements[$i];
                $totalPrice += ($cartElement['book']->getPrice() * $cartElement['quantity']);
            }
        endif; ?>
        <div class="cart-footer">
            <div>
                <?php
                if($cart): ?>
                    <p id="total-price"><strong>Total: </strong><?php echo $totalPrice ?></p>
                <?php else: ?>
                    <p id="total-price"><strong>Total: </strong>0</p>
                <?php endif; ?>
            </div>
            <div id="checkout-container">
                <button id="checkoutButton" class="checkout-button">Checkout</button>
            </div>
        </div>
    </div>
</body>
</html>
