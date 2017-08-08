<?php
    /**
     * The central controller for all download requests
     *
     * PHP version 7
     *
     * @package    MapPlatform
     * @subpackage Controllers\Api
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    namespace MapPlatform\Controllers\Api;
    
    use \InvalidArgumentException;
    use \Slim\Http\Request;
    use \Slim\Http\Response;
    use \Slim\Http\Stream;
    use \MapPlatform\Core;
    use \MapPlatform\AbstractClasses\ApiController;

    /**
     * Download controller
     *
     * @package    MapPlatform
     * @subpackage Controllers\Api
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    class DownloadController extends ApiController
    {
        /**
         * DownloadController invoker.
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
         * DownloadController map download funtion.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function downloadMap(Request $request, Response $response, $args) {
            $this->container->logger->info("ManagementTools '/api/v1/download/map/" . $args['revId'] . "' route");
            $mapItem  = null;
            $revId    = filter_var($args['revId'], FILTER_SANITIZE_NUMBER_INT);

            if ($revId === null || $revId <= 0) {
                $this->container->logger->error('downloadMap -> Map revision not found');

                return $response->withJson(['status' => 'Error', 'message' => 'File not found'], 404, JSON_PRETTY_PRINT);
            };

            $database   = $this->container->dataBase->PDO;
            $config     = $this->container->get('settings')['files'];
            $query      = $database->select(['Maps.map_pk', 'Maps.map_downloads', 'Revisions.rev_map_file_name', 'Revisions.rev_map_file_path'])
                                   ->from('Revisions')
                                   ->leftJoin('Maps', 'Maps.map_pk', '=', 'Revisions.map_fk')
                                   ->where('Revisions.rev_pk', '=', $revId)
                                   ->where('Revisions.rev_status_fk', '=', 1);
            $stmt       = $query->execute();
            $mapItemArr = $stmt->fetchall();

            if (count($mapItemArr) < 1) {
                $this->container->logger->error('downloadMap -> Map revision not found');

                return $response->withJson(['status' => 'Error', 'message' => 'File not found'], 404, JSON_PRETTY_PRINT);
            };

            $mapItem  = $mapItemArr[0];
            $fullPath = $config['uploadDir'] . $mapItem['rev_map_file_path'] . $mapItem['rev_map_file_name'];

            if (!file_exists($fullPath)) {
                $this->container->logger->error('downloadMap -> Map revision physical files not found');

                return $response->withJson(['status' => 'Error', 'message' => 'File not found'], 404, JSON_PRETTY_PRINT);
            };

            $mapDownloads = $mapItem['map_downloads'] + 1;
            $query        = $database->update()
                                     ->table('Maps')
                                     ->set(['map_downloads' => $mapDownloads])
                                     ->where('map_pk', '=', $mapItem['map_pk']);
            $database->beginTransaction();
            $affectedRows = $query->execute();

            if ($affectedRows === 1) {
                $database->commit();
            } else {
                $database->rollBack();
                $this->container->logger->debug('downloadMap -> Unable to update map download count');

                return $response->withJson(['status' => 'Error', 'message' => 'Unable to update map download count.'], 500, JSON_PRETTY_PRINT);
            };

            $fileHandle = fopen($fullPath, 'rb');
            $stream     = new Stream($fileHandle); // Create a stream instance for the response body

            return $response->withHeader('Content-Type', 'application/force-download')
                            ->withHeader('Content-Type', 'application/octet-stream')
                            ->withHeader('Content-Type', 'application/download')
                            ->withHeader('Content-Description', 'File Transfer')
                            ->withHeader('Content-Transfer-Encoding', 'binary')
                            ->withHeader('Content-Disposition', 'attachment; filename="' . $mapItem['rev_map_file_name'] . '"')
                            ->withHeader('Expires', '0')
                            ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                            ->withHeader('Pragma', 'public')
                            ->withBody($stream); // All stream contents will be sent to the response
        }
    }
