<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="/css/navbar.css" />
    <link rel="stylesheet" href="/css/common.css" />
</head>
<body>
    <?php include_once 'navbar.php'; ?>

    <div class="custom-header">
        <div class="text-section">
            <span>■ FORGOT PASSWORD</span>
        </div>
    </div>

    <div class="form-container">
        <form action="forgotPassword" method="post" id="custom-form">
            <div class="form-field">
                <label for="email">E-Mail:</label>
                <input type="email" id="email" name="email" required/>
            </div>
            <?php if (isset($this->getErrors()['email'])): ?>
                <span class="error-msg"><?php echo $this->getErrors()['email']; ?></span>
            <?php endif; ?>
            <?php if (isset($this->getErrors()['ip_blocked'])): ?>
                <span style="color: red;"><?php echo $this->getErrors()['ip_blocked']; ?></span>
            <?php endif; ?>
            <button type="submit" class="custom-submit-button">SUBMIT</button>
        </form>
    </div>
</body>
</html>
