<?php
$_SESSION['HTTP_X_CSRF_TOKEN'] = bin2hex(random_bytes(32));        //256 bits CSRF secure token
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="/css/navbar.css" />
    <link rel="stylesheet" href="/css/common.css" />
    <script src="/js/checkoutValidation.js"></script>
</head>
<body>
<?php include_once 'navbar.php'; ?>

    <div class="custom-header">
        <div class="text-section">
            <span>■ CHECKOUT</span>
        </div>
    </div>
    <div class="form-container">
        <form method="post" action="/checkout" id="custom-form">
            <input type="hidden" name="HTTP_X_CSRF_TOKEN" value="<?= $_SESSION['HTTP_X_CSRF_TOKEN'] ?>">
            <?php if (!empty($this->getErrors()['CSRF'])) : ?>
                    <span class="error-msg"><?php echo $this->getErrors()['CSRF']; ?></span>
            <?php endif; ?>
            <div class="form-field">
                <label for="creditCardNumber">Credit Card Number:</label>
                <input type="text" id="creditCardNumber" name="creditCardNumber" required value="<?php echo isset($_POST['creditCardNumber']) ? htmlspecialchars($_POST['creditCardNumber']) : ''; ?>">
                <?php if (!empty($this->getErrors()['creditCardNumber'])) : ?>
                    <span class="error-msg"><?php echo $this->getErrors()['creditCardNumber']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="cvv">CVV:</label>
                <input type="text" id="cvv" name="cvv" required value="<?php echo isset($_POST['cvv']) ? htmlspecialchars($_POST['cvv']) : ''; ?>">
                <?php if (!empty($this->getErrors()['cvv'])) : ?>
                    <span class="error-msg"><?php echo $this->getErrors()['cvv']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="expirationDate">Expiration Date (MM/YY):</label>
                <input type="text" id="expirationDate" name="expirationDate" required value="<?php echo isset($_POST['expirationDate']) ? htmlspecialchars($_POST['expirationDate']) : ''; ?>">
                <?php if (!empty($this->getErrors()['expirationDate'])) : ?>
                    <span class="error-msg"><?php echo $this->getErrors()['expirationDate']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="cardHolder">Cardholder:</label>
                <input type="text" id="cardHolder" name="cardHolder" required value="<?php echo isset($_POST['cardHolder']) ? htmlspecialchars($_POST['cardHolder']) : ''; ?>">
                <?php if (!empty($this->getErrors()['cardHolder'])) : ?>
                    <span class="error-msg"><?php echo $this->getErrors()['cardHolder']; ?></span>
                <?php endif; ?>
            </div>

            <?php if (!empty($this->getErrors()['generic'])) : ?>
                <span class="error-msg"><?php echo $this->getErrors()['generic']; ?></span>
            <?php endif; ?>

            <button type="submit" class="custom-submit-button">SUBMIT</button>
        </form>
    </div>
</body>
</html>
