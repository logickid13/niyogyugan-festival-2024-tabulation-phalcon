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

// Scoring Configuration - Load Municipality
$router->add(
    $server.'scoring/loadMunicipality',
    [
        'controller' => 'scoring',
        'action'     => 'loadMunicipality'
    ]
);

// Scoring Configuration - Load Contest
$router->add(
    $server.'scoring/loadContest',
    [
        'controller' => 'scoring',
        'action'     => 'loadContest'
    ]
);

// Scoring Configuration - Get Current Score
$router->add(
    $server.'scoring/getCurrentScore',
    [
        'controller' => 'scoring',
        'action'     => 'getCurrentScore'
    ]
);

// Scoring Configuration - Add To Current Score
$router->add(
    $server.'scoring/addToCurrentScore',
    [
        'controller' => 'scoring',
        'action'     => 'addToCurrentScore'
    ]
);

// Scoring Configuration - Update Current Score
$router->add(
    $server.'scoring/updateCurrentScore',
    [
        'controller' => 'scoring',
        'action'     => 'updateCurrentScore'
    ]
);

// Consolation Configuration - Load Municipality
$router->add(
    $server.'consolation/loadMunicipality',
    [
        'controller' => 'consolation',
        'action'     => 'loadMunicipality'
    ]
);

// Consolation Configuration - Load Contest
$router->add(
    $server.'consolation/loadContest',
    [
        'controller' => 'consolation',
        'action'     => 'loadContest'
    ]
);

// Consolation Configuration - Update Scores of Multiple Municipalities per Contest
$router->add(
    $server.'consolation/updateConsolationScore',
    [
        'controller' => 'consolation',
        'action'     => 'updateConsolationScore'
    ]
);

// Activity Logs -Load Logs
$router->add(
    $server.'activitylogs/load',
    [
        'controller' => 'activitylogs',
        'action'     => 'load'
    ]
);

// Activity Logs - Users Autocomplete
$router->add(
    $server.'activitylogs/loadUsersAutoComplete',
    [
        'controller' => 'activitylogs',
        'action'     => 'loadUsersAutoComplete'
    ]
);

// Accounts - Load
$router->add(
    $server.'accounts/load',
    [
        'controller' => 'accounts',
        'action'     => 'load'
    ]
);

// Accounts - New Account(Without Profile Picture)
$router->add(
    $server.'accounts/newAccountnoProfilePic',
    [
        'controller' => 'accounts',
        'action'     => 'newAccountnoProfilePic'
    ]
);

// Accounts - New Account(With Profile Picture)
$router->add(
    $server.'accounts/newAccountWithProfilePic',
    [
        'controller' => 'accounts',
        'action'     => 'newAccountWithProfilePic'
    ]
);

$router->add(
    $server.'consolation/shit',
    [
        'controller' => 'consolation',
        'action'     => 'shit'
    ]
);

// Float - Ranks
$router->add(
    $server.'floatVotingManagment/ranking',
    [
        'controller' => 'floatVotingManagment',
        'action'     => 'ranking'
    ]
);

// Float - List of voters per float/municipalit
$router->add(
    $server.'floatVotingManagment/votersData',
    [
        'controller' => 'floatVotingManagment',
        'action'     => 'votersData'
    ]
);



$router->handle($_SERVER['REQUEST_URI']);
