<?php
    /**
     * The central controller for all indirect image access
     *
     * PHP version 7
     *
     * @package    MapPlatform
     * @subpackage Controllers
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    namespace MapPlatform\Controllers;
    
    use \InvalidArgumentException;
    use \Slim\Http\Request;
    use \Slim\Http\Response;
    use \Slim\Http\Stream;
    use \MapPlatform\Core;
    use \MapPlatform\AbstractClasses\PageController;

    /**
     * Image controller
     *
     * @package    MapPlatform
     * @subpackage Controllers
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    class ImageController extends PageController
    {
        /**
         * ImageController invoker.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function __invoke(Request $request, Response $response, $args) {
            return $response;
        }
        
        /**
         * Show the default page.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function home(Request $request, Response $response, $args) {
            return $response;
        }
        
        /**
         * ImageController map image retrieval funtion.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function getMapImage(Request $request, Response $response, $args) {
            $this->container->logger->info("ManagementTools '/images/" . $args['revId'] . "/" . $args['screenId'] . "' route");
            $revId    = filter_var($args['revId'], FILTER_SANITIZE_NUMBER_INT);
            $screenId = filter_var($args['screenId'], FILTER_SANITIZE_NUMBER_INT);

            if (($revId === null) || ($revId <= 0) ||
                ($screenId === null) || ($screenId <= 0)) {
                $this->container->logger->error('getMapImage -> Map image not found');

                return $response->withStatus(404, 'Image not found.');
            };

            $database   = $this->container->dataBase->PDO;
            $query      = $database->select(['screen_file_name',
                                                'screen_path'
                                            ])
                                    ->from('Screenshots')
                                    ->where('rev_fk', '=', $revId)
                                    ->where('screen_pk', '=', $screenId, 'AND');
            $stmt       = $query->execute();
            $screenshot = $stmt->fetch();

            if ($screenshot == null || empty($screenshot))
                return $response->withStatus(404, 'Image not found.');

            $config     = $this->container->get('settings');
            $fullPath   = $config['appRootDir'] . $screenshot['screen_path'] . $screenshot['screen_file_name'];
            $fullPath   = str_replace('//', '/', $fullPath); // Be sure to stay compatible with legacy
            $fileHandle = fopen($fullPath, 'rb');
            $stream     = new Stream($fileHandle); // Create a stream instance for the response body

            return $response->withHeader('Content-Type', 'image/png')
                            ->withHeader('Expires', '0')
                            ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                            ->withHeader('Pragma', 'public')
                            ->withBody($stream); // All stream contents will be sent to the response
        }
                
        /**
         * ImageController default image retrieval funtion.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function getDefaultImage(Request $request, Response $response, $args) {
            $this->container->logger->info("ManagementTools '/images/default/" . $args['imageName'] . "' route");
            $imageName = filter_var($args['imageName'], FILTER_SANITIZE_STRING);

            if (($imageName === null) || ($imageName == '')) {
                    $this->container->logger->error('getDefaultImage -> Default image not found');
    
                    return $response->withStatus(404, 'Image not found.');
            };

            $config     = $this->container->get('settings');
            $fullPath   = $config['appRootDir'] . 'uploads/images/' . $imageName;
            $fileHandle = fopen($fullPath, 'rb');
            $stream     = new Stream($fileHandle); // Create a stream instance for the response body

            return $response->withHeader('Content-Type', 'image/png')
                            ->withHeader('Expires', '0')
                            ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                            ->withHeader('Pragma', 'public')
                            ->withBody($stream); // All stream contents will be sent to the response
        }
    }
