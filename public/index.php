<?php
    /**
     * The application initiator.
     *
     * PHP version 7
     *
     * @package MapPlatform
     * @author  Thimo Braker <thibmorozier@gmail.com>
     * @version 1.0.0
     * @since   First available since Release 1.0.0
     */
    if (PHP_SAPI == 'cli-server') {
        // To help the built-in PHP dev server, check if the request was actually for
        // something which should probably be served as a static file
        $url  = parse_url($_SERVER['REQUEST_URI']);
        $file = __DIR__ . $url['path'];

        if (is_file($file))
            return false;
    };

    require __DIR__ . '/../vendor/autoload.php';

    session_start();

    use \Slim\App;

    // Instantiate the app
    $settings = require __DIR__ . '/../src/settings.php';
    $app      = new App($settings);

    // Set up dependency factory
    require __DIR__ . '/../src/dependencies.php';
    // Register middleware
    require __DIR__ . '/../src/middleware.php';
    // Register routes
    require __DIR__ . '/../src/routes.php';

    $fileUtils  = $app->getContainer()->fileUtils;
	$fuSettings = $app->getContainer()->get('settings')['minifier'];
    $fileUtils->minifyCSS($fuSettings['css']);
    $fileUtils->minifyJS($fuSettings['js']);

    // Run app
    $app->run();
