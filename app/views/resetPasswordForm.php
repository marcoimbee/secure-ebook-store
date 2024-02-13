<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="/css/navbar.css" />
    <link rel="stylesheet" href="/css/common.css" />
</head>
<body>
    <?php include_once 'navbar.php'; ?>

    <div class="custom-header">
        <div class="text-section">
            <span>■ RESET PASSWORD</span>
        </div>
    </div>
    <div class="form-container">
        <form action="/resetPassword" method="post">
        <input type="hidden" name="token" value="<?php echo $token; ?>">
            <div class="form-field">
                <label for="newPassword">Enter your new password:</label>
                <input type="password" id="newPassword" name="newPassword" required>
                <?php if (isset($this->getErrors()['password'])): ?>
                <span style="color: red;"><?php echo $this->getErrors()['password']; ?></span>
                <?php endif; ?>
            </div>
            <div class="form-field">
                <label for="confirmPassword">Confirm your new password:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
            
                <?php if (isset($this->getErrors()['passwordMismatch'])): ?>
                    <span style="color: red;"><?php echo $this->getErrors()['passwordMismatch']; ?></span>
                <?php endif; ?>
            </div>
            <button type="submit" class="custom-submit-button">RESET PASSWORD</button>
        </form>
    </div>
    
</body>
</html>
