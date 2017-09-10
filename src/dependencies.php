<?php
    /**
     * The application dependency and container definition
     *
     * PHP version 7
     *
     * @package MapPlatform
     * @author  Thimo Braker <thibmorozier@gmail.com>
     * @version 1.0.0
     * @since   First available since Release 1.0.0
     */
    use \Slim\Views\PhpRenderer;
    use \Monolog\Logger;
    use \Monolog\Processor\UidProcessor;
    use \Monolog\Handler\StreamHandler;
    use \MapPlatform\Core;
    use \MapPlatform\Core\Utils;

    // DIC configuration
    $container = $app->getContainer();

    // view renderer
    $container['renderer'] = function ($c) {
        $settings = $c->get('settings')['renderer'];

        return new PhpRenderer($settings['template_path']);
    };

    // monolog
    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger   = new Logger($settings['name']);

        $logger->pushProcessor(new UidProcessor());
        $logger->pushHandler(new StreamHandler($settings['path'], $settings['level']));

        return $logger;
    };

    $container['fileUtils'] = function ($c) {
        return new Utils\FileUtils($c);
    };

    $container['renderUtils'] = function ($c) {
        return new Utils\RenderUtils($c);
    };

    $container['formattingUtils'] = function ($c) {
        return new Utils\FormattingUtils($c);
    };

    $container['miscUtils'] = function ($c) {
        return new Utils\MiscUtils($c);
    };

    $container['dataBase'] = function ($c) {
        $dbClient = new Core\SQLConnector($c);

        if (!$dbClient->connect())
            throw new Exception('Unable to connect to the database!');

        return $dbClient;
    };

    $container['security'] = function ($c) {
        return new Core\Security($c);
    };
