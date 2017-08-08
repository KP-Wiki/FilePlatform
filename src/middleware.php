<?php
    /**
     * The application middleware
     *
     * PHP version 7
     *
     * @package MapPlatform
     * @author  Thimo Braker <thibmorozier@gmail.com>
     * @version 1.0.0
     * @since   First available since Release 1.0.0
     */
    // Application middleware
    use \Psr7Middlewares\Middleware\TrailingSlash;

    $app->add((new TrailingSlash(False))->redirect(301));
