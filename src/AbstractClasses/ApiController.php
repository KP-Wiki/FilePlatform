<?php
    /**
     * The abstract API controller
     *
     * PHP version 7
     *
     * @package    MapPlatform
     * @subpackage AbstractClasses
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    namespace MapPlatform\AbstractClasses;

    use Slim\Http\Request;
    use Slim\Http\Response;
    use Slim\Container;

    /**
     * Abstract ApiController
     *
     * @package    MapPlatform
     * @subpackage AbstractClasses
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    abstract class ApiController
    {
        /** @var \Slim\Container $container The framework container */
        protected $container;

        /**
         * Class constructor.
         *
         * @param \Slim\Container $container
         */
        public function __construct(Container $container) {
            $this->container = $container;
        }

        /**
         * Class invoker.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public abstract function __invoke(Request $request, Response $response, $args);
    }
