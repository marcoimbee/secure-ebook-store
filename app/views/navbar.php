<?php
// Check if $showSearchBar is not set, then assign the default value false
$showSearchBar = isset($showSearchBar) ? $showSearchBar : false;
?>

<nav class="navbar">
    <div class="nav-left">
        <a href="/" class="nav-link">ALL BOOKS</a>
        <?php if ($showSearchBar): ?>
            <a class="nav-link" id="search">SEARCH</a>
        <?php endif; ?>
    </div>
    <div class="nav-right">
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="profile" class="nav-link">PROFILE</a>
            <a href="logout" class="nav-link">LOG OUT</a>
        <?php else: ?>
            <a href="login" class="nav-link">LOG IN</a>
            <a href="register" class="nav-link">SIGN UP</a>
        <?php endif; ?>
        <a href="cart" class="nav-link">CART</a>
    </div>
</nav>

<?php if ($showSearchBar): ?>
    <div id="searchBarContainer">
        <form action="/search" method="GET">
            <input type="text" name="keyword" id="searchInput" placeholder="Search for books..." autofocus>
        </form>
    </div>
<?php endif; ?>
