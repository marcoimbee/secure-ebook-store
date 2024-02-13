<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profile</title>
    <link rel="stylesheet" href="/css/navbar.css" />
    <link rel="stylesheet" href="/css/common.css" />
    <link rel="stylesheet" href="/css/profile.css" />
    <script src="/js/profileValidation.js"></script>
</head>
<body>
    <?php include_once 'navbar.php'; ?>

    <div class="custom-header">
        <div class="text-section">
            <span>■ PROFILE</span>
        </div>
    </div>
    <div class="form-container">
        <form action="profile" method="post" id="custom-form">
            <div class="form-field">
                <label for="email">E-Mail:</label>
                <input type="email" id="email" name="email" value="<?php echo $user->getEmail();?>" readonly/>
            </div>
            <div class="form-field">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo $user->getUsername(); ?>" required/>
                <?php if (isset($this->getErrors()['username'])): ?>
                    <span class="error-msg"><?php echo $this->getErrors()['username']; ?></span>
                <?php endif; ?>
            </div>
            <div class="form-field">
                <label for="password">Old Password:</label>
                <input type="password" id="password" name="password" required/>
                <?php if (isset($this->getErrors()['password'])): ?>
                    <span class="error-msg"><?php echo $this->getErrors()['password']; ?></span>
                <?php endif; ?>
            </div>
            <div class="form-field">
                <label for="new-password">New Password:</label>
                <input type="password" id="new-password" name="new-password" />
                <?php if (isset($this->getErrors()['newPassword'])): ?>
                    <span class="error-msg"><?php echo $this->getErrors()['newPassword']; ?></span>
                <?php endif; ?>
                <?php if (isset($this->getErrors()['csrf'])): ?>
                    <span class="error-msg"><?php echo $this->getErrors()['csrf']; ?></span>
                <?php endif; ?>
                <?php if (!empty($this->getSuccess())): ?>
                    <span class="success-msg"><?php echo $this->getSuccess(); ?></span>
                <?php endif; ?>
            </div>
            <button type="submit" class="custom-submit-button">UPDATE PROFILE</button>
        </form>
    </div>
    <div class="custom-header" id="order-header">
        <div class="text-section">
            <span>■ ORDERS</span>
        </div>
    </div>
    <div class="order-container">
        <?php if (empty($orders)): ?>
            <div class="no-orders">You have no orders</div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <a href="order?id=<?php echo $order->getId(); ?>">
                    <div class="order-card">
                        <div class="order-id">Order #<?php echo $order->getId(); ?></div>
                        <div>Total $<?php echo $order->getTotalAmount(); ?></div>
                        <div>Your order was placed on <?php echo date('d F Y', strtotime($order->getDateTime())); ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
