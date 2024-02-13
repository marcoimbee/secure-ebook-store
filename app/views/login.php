<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="/css/navbar.css" />
    <link rel="stylesheet" href="/css/common.css" />
</head>
<body>
    <?php include_once 'navbar.php';?>

    <div class="custom-header">
        <div class="text-section">
            <span>■ USER LOGIN</span>
        </div>
    </div>
    <div class="form-container">
        <form action="login" method="post">
            <div class="form-field">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo isset($_POST['username'])
                 ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                <?php if (isset($this->getErrors()['username'])): ?>
                    <span class="error-msg"><?php echo $this->getErrors()['username']; ?></span>
                <?php endif; ?>
            </div>
            <div class="form-field">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <?php if (isset($this->getErrors()['password'])): ?>
                    <span class="error-msg"><?php echo $this->getErrors()['password']; ?></span>
                <?php endif; ?>
                <?php if (isset($this->getErrors()['invalidCredentials'])): ?>
                    <span class="error-msg"><?php echo $this->getErrors()['invalidCredentials']; ?></span>
                <?php endif; ?>
                <?php if (isset($this->getErrors()['username_blocked'])): ?>
                    <span class="error-msg"><?php echo $this->getErrors()['username_blocked']; ?></span>
                <?php endif; ?>
            </div>
            <button type="submit" class="custom-submit-button">LOGIN</button>
        </form>
        <div class="form-footer">
            <a href="forgotPassword">Forgot your password?</a>
            <a href="register">Join us</a>
        </div>
        
    </div>
    
</body>
</html>
