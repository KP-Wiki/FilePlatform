<?php
    /**
     * The application dependency and container definition
     *
     * PHP version 7
     *
     * @package ManagementTools
     * @author  Thimo Braker <t.braker@sigmax.nl>
     * @version 1.0.1
     * @since   First available since Release 1.0.0
     */
    use Slim\Views\PhpRenderer;
    use Monolog\Logger;
    use Monolog\Processor\UidProcessor;
    use Monolog\Handler\StreamHandler;
    use ManagementTools\Core;
    use ManagementTools\Core\Utils;

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
        return new Utils\RenderUtils();
    };

    $container['formattingUtils'] = function ($c) {
        return new Utils\FormattingUtils();
    };

    $container['ipPoolDb'] = function ($c) {
        $dbClient = new Core\SQLConnector($c);

        if (!$dbClient->connect('ippool'))
            throw new Exception('Unable to connect to the database!');

        return $dbClient;
    };

    $container['meteringDb'] = function ($c) {
        $dbClient = new Core\InfluxConnector($c);

        if (!$dbClient->connect('metering'))
            throw new Exception('Unable to connect to the database!');

        return $dbClient;
    };
