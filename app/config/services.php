<?php
declare(strict_types=1);

use Phalcon\Escaper;
use Phalcon\Flash\Direct as Flash;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Session\Adapter\Stream as SessionAdapter;
use Phalcon\Session\Manager as SessionManager;
use Phalcon\Url as UrlResolver;
use Phalcon\Crypt as CryptographicHash;
use Phalcon\Http\Response\Cookies;

use Phalcon\Cache;
use Phalcon\Cache\Adapter\Libmemcached as StorageLibmemcached;
use Phalcon\Storage\SerializerFactory;

/** Cache Adapter using Libmemcached **/
$di->set(
    'cacheAdapter',
    function () {
        $serializerFactory = new SerializerFactory();
  
        $options = [
            'defaultSerializer' => 'Igbinary',
            'lifetime'          => 7200, // 7200 = 2 hours, 86400 = 1 day
            'servers' => [
                0 => [
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 1
                ]
            ]
        ];
 
        $adapter = new StorageLibmemcached($serializerFactory, $options);
  
        return $adapter;
    }
);

$di->setShared(
    'modelsCache',
    function () {
        $adapter = $this->getCacheAdapter();
        return new Cache($adapter);
    }
);

/** Crypt Hash **/
$di->setShared(
        'crypt',
        function () {
            $config = $this->getConfig();
            $crypt = new CryptographicHash();
            /**
             * Set the cipher algorithm.
             *
             * The `aes-256-gcm' is the preferable cipher, but it is not usable until the
             * openssl library is upgraded, which is available in PHP 7.1.
             *
             * The `aes-256-ctr' is arguably the best choice for cipher
             * algorithm in these days.
             */
            $crypt->setCipher('aes-256-ctr'); // the best cipher as of 2016
            $key = $config->crypt->key; // secret key
            $crypt->setKey($key);
            $crypt->useSigning(true);
            return $crypt;
        }
);

/** Cookies **/
$di->setShared(
    'cookies',
    function () {
        $cookies = new Cookies();

        // The `$key' MUST be at least 32 characters long and generated using a
        // cryptographically secure pseudo random generator.

        // $key = "#1dj8$=dp?.ak//j1V$~%*0XaK\xb1\x8d\xa9\x98\x054t7w!z%C*F-Jk\x98\x05\\\x5c";
        // $cookies->setSignKey($generated_key);
        $cookies->useEncryption(true);
        return $cookies;
    }
);

/** Models Manager */
// $di->setShared(
//     "modelsManager",
//     function() {
//         return new ModelsManager();
//     }
// );

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
});

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () {
    $config = $this->getConfig();

    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
});

/**
 * Setting up the view component
 */
$di->setShared('view', function () {
    $config = $this->getConfig();

    $view = new View();
    $view->setDI($this);
    $view->setViewsDir($config->application->viewsDir);

    $view->registerEngines([
        '.volt' => function ($view) {
            $config = $this->getConfig();

            $volt = new VoltEngine($view, $this);

            $volt->setOptions([
                'path' => $config->application->cacheDir,
                'separator' => '_'
            ]);

            return $volt;
        },
        '.phtml' => PhpEngine::class

    ]);

    return $view;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $params = [
        'host'     => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname'   => $config->database->dbname,
        'charset'  => $config->database->charset
    ];

    if ($config->database->adapter == 'Postgresql') {
        unset($params['charset']);
    }

    return new $class($params);
});


/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->setShared('modelsMetadata', function () {
    return new MetaDataAdapter();
});

/**
 * Register the session flash service with the Twitter Bootstrap classes
 */
$di->set('flash', function () {
    $escaper = new Escaper();
    $flash = new Flash($escaper);
    $flash->setImplicitFlush(false);
    $flash->setCssClasses([
        'error'   => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice'  => 'alert alert-info',
        'warning' => 'alert alert-warning'
    ]);

    return $flash;
});

/**
 * Start the session the first time some component request the session service
 */
$di->setShared('session', function () {
    $session = new SessionManager();
    $files = new SessionAdapter([
        'savePath' => sys_get_temp_dir(),
    ]);
    $session->setAdapter($files);
    $session->start();

    return $session;
});
