<?php 
    use App\Utils\ValidProvinces; 
    $_SESSION['HTTP_X_CSRF_TOKEN'] = bin2hex(random_bytes(32));        //256 bits CSRF secure token
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping</title>
    <link rel="stylesheet" href="/css/navbar.css" />
    <link rel="stylesheet" href="/css/common.css" />
    <script src="/js/shippingValidation.js"></script>
</head>
<body>
    <?php include_once 'navbar.php'; ?>

    <div class="custom-header">
        <div class="text-section">
            <span>■ SHIPPING</span>
        </div>
    </div>
    <div class="form-container">
        <form action="shipment" method="post" id="custom-form">
            <input type="hidden" name="HTTP_X_CSRF_TOKEN" value="<?= $_SESSION['HTTP_X_CSRF_TOKEN'] ?>">
            <?php if (!empty($this->getErrors()['CSRF'])) : ?>
                    <span class="error-msg"><?php echo $this->getErrors()['CSRF']; ?></span>
            <?php endif; ?>
            <div class="form-field">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" required>
                <?php if (isset($this->getErrors()['address'])): ?>
                    <span class="error-msg"><?php echo $this->getErrors()['address']; ?></span>
                <?php endif; ?>
            </div>
            <div class="form-field">
                <label for="house_number">House number: </label>
                <input type="text" id="houseNumber" name="houseNumber" value="<?php echo isset($_POST['houseNumber']) ? htmlspecialchars($_POST['houseNumber']) : ''; ?>" required>
                <?php if (isset($this->getErrors()['houseNumber'])): ?>
                    <span class="error-msg"><?php echo $this->getErrors()['houseNumber']; ?></span>
                <?php endif; ?>
            </div>
            <div class="form-field">
                <label for="zipCode">ZIP Code:</label>
                <input type="text" id="zipCode" name="zipCode" value="<?php echo isset($_POST['zipCode']) ? htmlspecialchars($_POST['zipCode']) : ''; ?>" required>
                <?php if (isset($this->getErrors()['zipCode'])): ?>
                    <span class="error-msg"><?php echo $this->getErrors()['zipCode']; ?></span>
                <?php endif; ?>
            </div>
            <div class="form-field">
                <label for="city">City:</label>
                <input type="text" id="city" name="city" value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>" required>
                <?php if (isset($this->getErrors()['city'])): ?>
                    <span class="error-msg"><?php echo $this->getErrors()['city']; ?></span>
                <?php endif; ?>
            </div>
            <div class="form-field">
                <label for="province">Province:</label>
                <select id="province" name="province" required>
                    <?php
                    foreach (ValidProvinces::$italianProvinces as $option) {
                        // Check if the option is the default value
                        $isSelected = (isset($_POST['province'])) ? (($option == $_POST['province']) ? "selected" : "") : "";

                        echo "<option value=\"$option\" $isSelected>$option</option>";
                    }
                    ?>
                </select>
                <?php if (isset($this->getErrors()['province'])): ?>
                    <span class="error-msg"><?php echo $this->getErrors()['province']; ?></span>
                <?php endif; ?>
            </div>
            <button type="submit" class="custom-submit-button">SUBMIT</button>
        </form>
    </div>
</body>
</html>
