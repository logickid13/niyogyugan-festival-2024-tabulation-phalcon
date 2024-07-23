<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
// $loader->registerDirs(
//     [
//         $config->application->controllersDir,
//         $config->application->modelsDir
//     ]
// )->register();

$loader->setExtensions(
    [
        'php',
        'inc',
        'phb',
    ]
);

$loader->registerDirs(
    [
        $config->application->controllersDir,
        $config->application->modelsDir,
        $config->application->helperDir
    ]
);

// $loader->registerFiles(
//     [
//         $config->application->datatablesDir.'DataTable.php',
//         $config->application->datatablesDir.'ParamsParser.php',
//         $config->application->datatablesDir.'/Adapters/AdapterInterface.php',
//         $config->application->datatablesDir.'/Adapters/ArrayAdapter.php'
//     ]
// );

// $loader->registerFiles(
//     [
//         $config->application->barcodeDir.'BarcodeGenerator.php',
//         $config->application->barcodeDir.'BarcodeGeneratorPNG.php',
//         $config->application->phpMailerDir.'PHPMailer.php',
//         $config->application->phpMailerDir.'POP3.php',
//         $config->application->phpMailerDir.'SMTP.php',
//         $config->application->phpMailerDir.'OAuth.php',
//         $config->application->phpMailerDir.'Exception.php',
//     ]
// );


// $loader->registerClasses(
//     [
//        'SSP' => $config->application->datatablesSSPDir.'ssp.php'
//     ]
// );

$loader->register();
