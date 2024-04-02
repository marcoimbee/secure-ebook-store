<?php

require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\Database;
use App\Core\Router;
use App\Utils\Logger;

session_start();

$maxInactivityPeriod = 600;         // in seconds

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $maxInactivityPeriod)) {
    // last request was more than 10 minutes ago
    session_unset();
    session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} elseif (time() - $_SESSION['CREATED'] > $maxInactivityPeriod) {
    // session started more than 10 minutes ago
    session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
    $_SESSION['CREATED'] = time();  // update creation time
}

try {
    $database = new Database();
} catch(Exception $e) {
    Logger::error($e->getMessage());
    die("We are experiencing technical difficulties, please try again later.");
}


header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
header("Content-Security-Policy: default-src 'self'; object-src 'none'; frame-ancestors 'none'; base-uri 'self';");
header('X-Frame-Options: DENY');            // prevents our page to be embedded in iframes (anti UI Redress or clickjacking)

$router = new Router();

// Define routes
$router->addRoute('GET', '/', 'App\Controllers\HomeController@index');

$router->addRoute('GET', '/login', 'App\Controllers\LoginController@showLoginForm');
$router->addRoute('POST', '/login', 'App\Controllers\LoginController@processLogin');

$router->addRoute('GET', '/logout', 'App\Controllers\LogoutController@logout', true);

$router->addRoute('GET', '/register', 'App\Controllers\RegisterController@showRegisterForm');
$router->addRoute('POST', '/register', 'App\Controllers\RegisterController@processRegistration');

$router->addRoute('GET', '/profile', 'App\Controllers\ProfileController@showProfile', true);
$router->addRoute('POST', '/profile', 'App\Controllers\ProfileController@updateProfile', true);

$router->addRoute('GET', '/order', 'App\Controllers\OrderController@showOrder', true);        //when the order is processed successfully, show a summary page with the order data and a link for the book download

$router->addRoute('GET', '/search', 'App\Controllers\SearchController@search');

$router->addRoute('GET', '/forgotPassword', 'App\Controllers\ForgotPasswordController@showForgotPasswordForm');
$router->addRoute('POST', '/forgotPassword', 'App\Controllers\ForgotPasswordController@processForgotPassword');
$router->addRoute('GET', '/resetPassword', 'App\Controllers\ResetPasswordController@showResetForm');
$router->addRoute('POST', '/resetPassword', 'App\Controllers\ResetPasswordController@processResetPassword');

$router->addRoute('GET', '/cart', 'App\Controllers\CartController@showCart');
$router->addRoute('POST', '/cart', 'App\Controllers\CartController@addToCart');
$router->addRoute('DELETE', '/cart', 'App\Controllers\CartController@removeFromCart');

$router->addRoute('GET', '/shipment', 'App\Controllers\ShipmentController@showShipment', true);
$router->addRoute('POST', '/shipment', 'App\Controllers\ShipmentController@processShipment', true);

$router->addRoute('GET', '/checkout', 'App\Controllers\CheckoutController@showCheckoutForm', true);
$router->addRoute('POST', '/checkout', 'App\Controllers\CheckoutController@processCheckout', true);     //credit card info

$router->addRoute('GET', '/download', 'App\Controllers\DownloadController@processDownload', true);      //ebook version download

$router->addRoute('GET', '/bookDetails', 'App\Controllers\BookController@showBookDetails');

// Handle the request
try {
    //print($_SERVER['REQUEST_METHOD'] . $_SERVER['REQUEST_URI']);
    $router->handleRequest($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $database);
} catch (Exception $e) {
    Logger::error($e->getMessage());
    http_response_code(500);
    include_once __DIR__ . "/../app/views/error.php";
    exit;
}
