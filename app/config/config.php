<?php

/*
 * Modified: prepend directory path of current file, because of this file own different ENV under between Apache and command line.
 * NOTE: please remove this comment.
 */
defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');

return new \Phalcon\Config([
    'database' => [
        'adapter'     => 'Mysql',
        'host'        => 'localhost',
        'username'    => 'root', // qsadmin_quezonsystemsivan
        'password'    => 'root',// (iR$unR6-Tt,
        // 'password'    => 'ivanpicto',// (iR$unR6-Tt,
        'dbname'      => 'qsadmin_niyogyugan_scoring',
        'charset'     => 'utf8',
    ],
    'application' => [
        'appDir'         => APP_PATH . '/',
        'controllersDir' => APP_PATH . '/controllers/',
        'modelsDir'      => APP_PATH . '/models/',
        'migrationsDir'  => APP_PATH . '/migrations/',
        'viewsDir'       => APP_PATH . '/views/',
        'pluginsDir'     => APP_PATH . '/plugins/',
        'libraryDir'     => APP_PATH . '/library/',
        'cacheDir'       => BASE_PATH . '/cache/',
        'helperDir'      => APP_PATH . '/helper/',
        'baseUri'        => '/',
    ],
    'crypt' => [
        'key' => "eiOn9xLpjpvr4mizOMGpOhOag0TGpkaU" // custom crypt key used on phalcon crypt
    ]
]);
