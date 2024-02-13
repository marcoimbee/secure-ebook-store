<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="/css/navbar.css" />
    <link rel="stylesheet" href="/css/common.css" />
    <script src="/js/signUpValidation.js"></script>
</head>
<body>
<?php include_once 'navbar.php'; ?>

    <div class="custom-header">
        <div class="text-section">
            <span>■ USER SIGN UP</span>
        </div>
    </div>
    <div class="form-container">
        <form action="register" method="post" id="custom-form">
            <div class="form-field">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                <?php if (isset($this->getErrors()['username'])): ?>
                    <span class="error-msg"><?php echo $this->getErrors()['username']; ?></span>
                <?php endif; ?>
            </div>
            <div class="form-field">
                <label for="email">E-Mail:</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                <?php if (isset($this->getErrors()['email'])): ?>
                    <span class="error-msg"><?php echo $this->getErrors()['email']; ?></span>
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
            </div>
            <div class="form-field">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <?php if (isset($this->getErrors()['confirmPassword'])): ?>
                    <span class="error-msg"><?php echo $this->getErrors()['confirmPassword']; ?></span>
                <?php endif; ?>
            </div>
            <?php if (isset($this->getErrors()['alreadyTaken'])): ?>
                <p class="error-msg"><?php echo $this->getErrors()['alreadyTaken']; ?></p>
            <?php endif; ?>
            <button type="submit" class="custom-submit-button">REGISTER</button>
        </form>
        <div class="form-footer">
            <span>Already have an account? <a href="/login">Sign In</a></span>
        </div>
    </div>
</body>
</html>
