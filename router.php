<?php
session_start();
require_once __DIR__ . "/classes/Database.php"; // Ensure Database is loaded

// Retrieve the requested URI from the query parameter
$uri = isset($_GET['url']) ? trim($_GET['url'], '/') : 'home';

// Allow direct access to test pages inside "views/test/"
if (preg_match('/^test\//', $uri)) {
    $testFile = __DIR__ . "/views/" . $uri . ".php";
    if (file_exists($testFile)) {
        require_once $testFile;
        exit();
    }
}

// Allow direct access to static assets (CSS, JS, images)
if (file_exists(__DIR__ . "/public/" . $uri)) {
    return false; // Let Apache serve the static file
}

// Define allowed controllers
$allowedControllers = [
    "home" => "HomeController",
    "performance"=>"PerformanceController",
    "basket"=>"BasketController",
    "booking" => "BookingController",
    "payment" => "PaymentController",
    "review" => "ReviewController",
    "discount" => "DiscountController",
    "auth" => "AuthController",
    "admin" => "AdminController",
    "api"=>"ApiController"
];

$segments = explode("/", $uri);
$controllerKey = strtolower($segments[0]);
$controllerName = isset($allowedControllers[$controllerKey]) ? $allowedControllers[$controllerKey] : null;
$method = isset($segments[1]) ? $segments[1] : "index";

if ($controllerName) {
    $controllerFile = __DIR__ . "/controllers/" . $controllerName . ".php";

    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $controller = new $controllerName();

        if (method_exists($controller, $method)) {
            call_user_func_array([$controller, $method],array_slice($segments, 2));
            exit();
        } else {
            die("Method '$method' not found in $controllerName.");
        }
    } else {
        die("Controller '$controllerKey' not found.");
    }
}

// If no valid route is found, show a 404 page
http_response_code(404);
require_once __DIR__ . "/views/404.php";
exit();
