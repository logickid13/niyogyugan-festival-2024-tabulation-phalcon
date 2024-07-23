<?php
use Phalcon\Mvc\Router; // phalcon class of routing

// $router = $di->getRouter();

$router = new Router();

$server = '/niyogyugan_scoring/';
// $server = 'https://niyogyugan.quezon.gov.ph/';

$router->add(
    $server.'api/auth/login',
    [
        'controller' => 'auth',
        'action'     => 'login'
    ]
);

// Logout
$router->add(
    $server.'api/auth/logout',
    [
        'controller' => 'auth',
        'action'     => 'logout'
    ]
);

// Refresh JWT Access Token
$router->add(
    $server.'api/auth/refreshToken',
    [
        'controller' => 'auth',
        'action'     => 'refreshToken'
    ]
);

// (JWT) Refresh Token Expire
$router->add(
    $server.'api/auth/refreshTokenExpire',
    [
        'controller' => 'auth',
        'action'     => 'refreshTokenExpire'
    ]
);

// Backend SessionCheck
$router->add(
    $server.'api/auth/sessionCheck',
    [
        'controller' => 'auth',
        'action'     => 'sessionCheck'
    ]
);

// Check if user is logged-in
$router->add(
    $server.'api/auth/isLoggedIn',
    [
        'controller' => 'auth',
        'action'     => 'isLoggedIn'
    ]
);

// Update Profile Picture
$router->add(
    $server.'api/auth/updateProfilePic',
    [
        'controller' => 'auth',
        'action'     => 'updateProfilePic'
    ]
);

// Update Account Password
$router->add(
    $server.'api/auth/updatePassword',
    [
        'controller' => 'auth',
        'action'     => 'updatePassword'
    ]
);

// Load Leaderboards - Overall Total
$router->add(
    $server.'api/leaderboards/load',
    [
        'controller' => 'leaderboards',
        'action'     => 'load'
    ]
);

// Load Contest Results Per Municipality
$router->add(
    $server.'api/leaderboards/loadContestResultsPerMunicipality',
    [
        'controller' => 'leaderboards',
        'action'     => 'loadContestResultsPerMunicipality'
    ]
);

// Load List of Activites
$router->add(
    $server.'api/activities/loadActivities',
    [
        'controller' => 'activities',
        'action'     => 'loadActivities'
    ]
);

// Load Guidelines
$router->add(
    $server.'api/guidelines/loadGuidelines',
    [
        'controller' => 'guidelines',
        'action'     => 'loadGuidelines'
    ]
);



$router->handle($_SERVER['REQUEST_URI']);
