<?php
use Phalcon\Mvc\Router; // phalcon class of routing

// $router = $di->getRouter();

$router = new Router();

$server = '/niyogyugan-festival-2024-tabulation-phalcon/niyogyugan-festival-2024-tabulation-phalcon/';
// $server = 'https://niyogyugan.quezonsystems.com/niyogyugan-festival-2024-tabulation-phalcon/niyogyugan-festival-2024-tabulation-phalcon/';

$router->add(
    $server.'auth/login',
    [
        'controller' => 'auth',
        'action'     => 'login'
    ]
);

// Logout
$router->add(
    $server.'auth/logout',
    [
        'controller' => 'auth',
        'action'     => 'logout'
    ]
);

// Refresh JWT Access Token
$router->add(
    $server.'auth/refreshToken',
    [
        'controller' => 'auth',
        'action'     => 'refreshToken'
    ]
);

// (JWT) Refresh Token Expire
$router->add(
    $server.'auth/refreshTokenExpire',
    [
        'controller' => 'auth',
        'action'     => 'refreshTokenExpire'
    ]
);

// Backend SessionCheck
$router->add(
    $server.'auth/sessionCheck',
    [
        'controller' => 'auth',
        'action'     => 'sessionCheck'
    ]
);

// Check if user is logged-in
$router->add(
    $server.'auth/isLoggedIn',
    [
        'controller' => 'auth',
        'action'     => 'isLoggedIn'
    ]
);

// Update Profile Picture
$router->add(
    $server.'auth/updateProfilePic',
    [
        'controller' => 'auth',
        'action'     => 'updateProfilePic'
    ]
);

// Update Account Password
$router->add(
    $server.'auth/updatePassword',
    [
        'controller' => 'auth',
        'action'     => 'updatePassword'
    ]
);

// Load Leaderboards - Overall Total
$router->add(
    $server.'leaderboards/load',
    [
        'controller' => 'leaderboards',
        'action'     => 'load'
    ]
);

// Load Contest Results Per Municipality
$router->add(
    $server.'leaderboards/loadContestResultsPerMunicipality',
    [
        'controller' => 'leaderboards',
        'action'     => 'loadContestResultsPerMunicipality'
    ]
);

// Load List of Activites
$router->add(
    $server.'activities/loadActivities',
    [
        'controller' => 'activities',
        'action'     => 'loadActivities'
    ]
);

// Load Guidelines
$router->add(
    $server.'guidelines/loadGuidelines',
    [
        'controller' => 'guidelines',
        'action'     => 'loadGuidelines'
    ]
);



$router->handle($_SERVER['REQUEST_URI']);
