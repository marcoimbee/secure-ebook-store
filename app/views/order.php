<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Order</title>
    <link rel="stylesheet" href="/css/navbar.css" />
    <link rel="stylesheet" href="/css/order.css" />
    <link rel="stylesheet" href="/css/common.css" />
    <script defer type="text/javascript" src="/js/download.js"></script>
</head>
<body>
    <?php include_once 'navbar.php'; ?>

    <div class="custom-header">
        <div class="text-section">
            <span>■ ORDER</span>
        </div>
    </div>
    <div class="main-container">
        <div class="order-message">
            <h2> 
                Your order was processed successfully! 
                You will receive your books shortly at your address. 
                In the meantime, you can download the e-book version!
            </h2>
        </div>
        <?php
        $count = count($orderDetails);

        for ($i = 0; $i < $count; $i++) {
            $orderElement = $orderDetails[$i];
            $totalAmount = $orderElement['TotalAmount'];
        ?>
            <div class="product-info" id="<?php echo $i ?>">
                <!-- Using htmlspecialchars in case an admin account gets compromised -->
                <div class="book-title">
                    <p class="book-title"><?php echo htmlspecialchars($orderElement['Title'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="author-name">
                    <p class="author-name"><?php echo htmlspecialchars($orderElement['Author'], ENT_QUOTES, 'UTF-8'); ?></p> 
                </div>
                <div class="quantity">
                    <p class="quantity"><?php echo htmlspecialchars($orderElement['Quantity'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <button class="download-ebook-button" data-token="<?php echo $orderElement['Token']; ?>">Download</button>
            </div>
        <?php } ?>
        <div class="order-footer">
            <div>
                <p id="total-amount"><strong>Total: </strong>$<?php echo htmlspecialchars($totalAmount, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>
    </div>
</body>
</html>
