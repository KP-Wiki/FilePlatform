<?php
    /**
     * The central controller for all map features
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
    use \MapPlatform\Core;
    use \MapPlatform\AbstractClasses\ApiController;

    /**
     * Map controller
     *
     * @package    MapPlatform
     * @subpackage Controllers\Api
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    class MapController extends ApiController
    {
        /**
         * MapController invoker.
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
         * MapController Get all maps.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function getAllMaps(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/maps' route");
            $database = $this->container->dataBase->PDO;

            try {
                $query       = $database->select(['Maps.map_pk',
                                                  'Maps.map_name',
                                                  'Maps.map_downloads',
                                                  'Revisions.rev_map_description_short',
                                                  'Revisions.rev_map_description',
                                                  'Revisions.rev_upload_date',
                                                  'Users.user_pk',
                                                  'Users.user_name',
                                                  'MapTypes.map_type_name'
                                                ])
                                        ->from('Maps')
                                        ->leftJoin('Revisions', 'Revisions.map_fk', '=', 'Maps.map_pk')
                                        ->leftJoin('Users', 'Users.user_pk', '=', 'Maps.user_fk')
                                        ->leftJoin('MapTypes', 'MapTypes.map_type_pk', '=', 'Maps.map_type_fk')
                                        ->where('Revisions.rev_status_fk', '=', 1)
                                        ->where('Maps.map_visible', '=', 1, 'AND')
                                        ->orderBy('Maps.map_name', 'ASC');
                $stmt        = $query->execute();
                $mapItemArr  = $stmt->fetchall();
                $responseArr = [
                    'status' => 'Ok',
                    'data' => []
                ];

                if (count($mapItemArr) < 1) {
                    $this->container->logger->debug('getAllMaps -> No maps found');

                    return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);
                };

                foreach ($mapItemArr as $mapItem) {
                    $query          = $database->select(['ROUND(AVG(CAST(rating_amount AS DECIMAL(12,2))), 1) AS avg_rating'])
                                               ->from('Ratings')
                                               ->where('map_fk', '=', $mapItem['map_pk']);
                    $stmt           = $query->execute();
                    $avgRating      = $stmt->fetch();
                    $lastChangeDate = date_create($mapItem['rev_upload_date']);
                    $contentItem    = [
                        'map_pk'                    => IntVal($mapItem['map_pk']),
                        'map_name'                  => $mapItem['map_name'],
                        'map_downloads'             => IntVal($mapItem['map_downloads']),
                        'rev_map_description_short' => $mapItem['rev_map_description_short'],
                        'rev_map_description'       => $mapItem['rev_map_description'],
                        'rev_upload_date'           => $lastChangeDate->format('Y-m-d H:i'),
                        'user_pk'                   => $mapItem['user_pk'],
                        'user_name'                 => $mapItem['user_name'],
                        'map_type_name'             => $mapItem['map_type_name'],
                        'avg_rating'                => ($avgRating['avg_rating'] === null ? 'n/a' : FloatVal($avgRating['avg_rating']))
                    ];

                    $responseArr['data'][] = $contentItem;
                };

                return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);
            } catch (Exception $ex) {
                $this->container->logger->error('getAllMaps -> ex = ' . $ex);
                $responseArr = [
                    'status' => 'Error',
                    'message' => 'Unable to retrieve maps, please try again later.'
                ];

                return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);
            }
        }

        /**
         * MapController Get a specific map.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function getMap(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/maps/" . $args['mapId'] . "' route");
            $mapId        = filter_var($args['mapId'], FILTER_SANITIZE_NUMBER_INT);
            $database     = $this->container->dataBase->PDO;
            $mapListItems = null;

            if ($mapId === null || $mapId <= 0) {
                $this->container->logger->error('getMap -> Map not found');
                $responseArr = [
                    'status' => 'Error',
                    'message' => 'Map with ID ' . $mapId . ' can not be found'
                ];

                return $response->withJson($responseArr, 404, JSON_PRETTY_PRINT);
            };

            try {
                $query = 'SET @mapid = :mapid;';
                $stmt  = $database->prepare($query)
                                  ->bindParam(':mapid', $mapId);
                $stmt->execute();

                $query   = $database->select(['Maps.map_name',
                                              'Maps.map_downloads',
                                              'Revisions.rev_pk',
                                              'Revisions.rev_map_description_short',
                                              'Revisions.rev_map_description',
                                              'Revisions.rev_upload_date',
                                              'Revisions.rev_map_version',
                                              'Users.user_pk',
                                              'Users.user_name',
                                              'MapTypes.map_type_name',
                                              'ROUND(AVG(CAST(Ratings.rating_amount AS DECIMAL(12,2))), 1) AS avg_rating',
                                              'IFNULL((SELECT COUNT(*) FROM Ratings WHERE rating_amount = 1 AND map_fk = @mapid), 0) AS rating_one',
                                              'IFNULL((SELECT COUNT(*) FROM Ratings WHERE rating_amount = 2 AND map_fk = @mapid), 0) AS rating_two',
                                              'IFNULL((SELECT COUNT(*) FROM Ratings WHERE rating_amount = 3 AND map_fk = @mapid), 0) AS rating_three',
                                              'IFNULL((SELECT COUNT(*) FROM Ratings WHERE rating_amount = 4 AND map_fk = @mapid), 0) AS rating_four',
                                              'IFNULL((SELECT COUNT(*) FROM Ratings WHERE rating_amount = 5 AND map_fk = @mapid), 0) AS rating_five'
                                            ])
                                    ->from('Maps')
                                    ->leftJoin('Revisions', 'Revisions.map_fk', '=', 'Maps.map_pk')
                                    ->leftJoin('Users', 'Users.user_pk', '=', 'Maps.user_fk')
                                    ->leftJoin('MapTypes', 'MapTypes.map_type_pk', '=', 'Maps.map_type_fk')
                                    ->leftJoin('Ratings', 'Ratings.map_fk', '=', 'Maps.map_pk')
                                    ->where('Revisions.rev_status_fk', '=', 1)
                                    ->where('Maps.map_visible', '=', 1, 'AND')
                                    ->where('Maps.map_pk', '=', $mapId, 'AND');
                $stmt    = $query->execute();
                $mapItem = $stmt->fetch();
                
                if ($mapItem != null && $mapItem['map_name'] != null) {
                    $lastChangeDate = new DateTime($mapItem['rev_upload_date']);
                    $responseArr    = [
                        'status' => 'Ok',
                        'data'   => [
                            'map_pk'                    => $mapId,
                            'map_name'                  => $mapItem['map_name'],
                            'map_downloads'             => IntVal($mapItem['map_downloads']),
                            'rev_pk'                    => $mapItem['rev_pk'],
                            'rev_map_description_short' => $mapItem['rev_map_description_short'],
                            'rev_map_description'       => $mapItem['rev_map_description'],
                            'rev_upload_date'           => $lastChangeDate -> format('Y-m-d H:i'),
                            'rev_map_version'           => $mapItem['rev_map_version'],
                            'user_pk'                   => $mapItem['user_pk'],
                            'user_name'                 => $mapItem['user_name'],
                            'map_type_name'             => $mapItem['map_type_name'],
                            'avg_rating'                => ($mapItem['avg_rating'] === null ? 'n/a' : FloatVal($mapItem['avg_rating'])),
                            'rating_one'                => IntVal($mapItem['rating_one']),
                            'rating_two'                => IntVal($mapItem['rating_two']),
                            'rating_three'              => IntVal($mapItem['rating_three']),
                            'rating_four'               => IntVal($mapItem['rating_four']),
                            'rating_five'               => IntVal($mapItem['rating_five'])
                        ]
                    ];

                    return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);
                } else {
                    $this->container->logger->error('getMap -> Map with ID ' . $mapId . ' does not exist.');
                    $responseArr = [
                        'status' => 'Error',
                        'message' => 'Map with ID ' . $mapId . ' does not exist.'
                    ];

                    return $response->withJson($responseArr, 404, JSON_PRETTY_PRINT);
                };
            } catch (Exception $ex) {
                $this->container->logger->error('getMap -> ex = ' . $ex);
                $responseArr = [
                    'status' => 'Error',
                    'message' => 'Unable to retrieve maps for the specified user, please try again later.'
                ];

                return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);
            }
        }

        /**
         * MapController Get a map by user ID.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function getMapsByUser(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/maps/user/" . $args['userId'] . "' route");
            $userId       = filter_var($args['userId'], FILTER_SANITIZE_NUMBER_INT);
            $database     = $this->container->dataBase->PDO;

            if ($userId === null || $userId <= 0) {
                $this->container->logger->error('getMapsByUser -> User not found');
                $responseArr = [
                    'status' => 'Ok',
                    'data' => []
                ];

                // Send empty array, helps against user enumeration
                return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);
            };

            try {
                $query       = $database->select(['Maps.map_pk',
                                                  'Maps.map_name',
                                                  'Maps.map_downloads',
                                                  'Revisions.rev_map_description_short',
                                                  'Revisions.rev_map_description',
                                                  'Revisions.rev_upload_date',
                                                  'Users.user_pk',
                                                  'Users.user_name',
                                                  'MapTypes.map_type_name'
                                                ])
                                        ->from('Maps')
                                        ->leftJoin('Revisions', 'Revisions.map_fk', '=', 'Maps.map_pk')
                                        ->leftJoin('Users', 'Users.user_pk', '=', 'Maps.user_fk')
                                        ->leftJoin('MapTypes', 'MapTypes.map_type_pk', '=', 'Maps.map_type_fk')
                                        ->whereIn('Revisions.rev_status_fk', [0, 1])
                                        ->where('Maps.user_fk', '=', $userId, 'AND')
                                        ->orderBy('Maps.map_name', 'ASC');
                $stmt        = $query->execute();
                $mapItemArr  = $stmt->fetchall();
                $responseArr = [
                    'status' => 'Ok',
                    'data' => []
                ];

                if (count($mapItemArr) < 1) {
                    $this->container->logger->debug('getMapsByUser -> No maps found for user');

                    return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);
                };

                foreach ($mapItemArr as $mapItem) {
                    $query          = $database->select(['ROUND(AVG(CAST(rating_amount AS DECIMAL(12,2))), 1) AS avg_rating'])
                                               ->from('Ratings')
                                               ->where('map_fk', '=', $mapItem['map_pk']);
                    $stmt           = $query->execute();
                    $avgRating      = $stmt->fetch();
                    $lastChangeDate = date_create($mapItem['rev_upload_date']);
                    $contentItem    = [
                        'map_pk'                    => IntVal($mapItem['map_pk']),
                        'map_name'                  => $mapItem['map_name'],
                        'map_downloads'             => IntVal($mapItem['map_downloads']),
                        'rev_map_description_short' => $mapItem['rev_map_description_short'],
                        'rev_map_description'       => $mapItem['rev_map_description'],
                        'rev_upload_date'           => $lastChangeDate->format('Y-m-d H:i'),
                        'user_pk'                   => $mapItem['user_pk'],
                        'user_name'                 => $mapItem['user_name'],
                        'map_type_name'             => $mapItem['map_type_name'],
                        'avg_rating'                => ($avgRating['avg_rating'] === null ? 'n/a' : FloatVal($avgRating['avg_rating']))
                    ];

                    $responseArr['data'][] = $contentItem;
                };

                return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);
            } catch (Exception $ex) {
                $this->container->logger->error('getMapsByUser -> ex = ' . $ex);
                $responseArr = [
                    'status' => 'Error',
                    'message' => 'Unable to retrieve maps for the specified user, please try again later.'
                ];

                return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);
            }
        }

        /**
         * MapController Add map function.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function addMap(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/maps' route");
            $data = $request->getParsedBody();
            $resultArr = array();

            return $response->withJson($resultArr, 200, JSON_PRETTY_PRINT);
        }

        /**
         * MapController Update map function.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function updateMap(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/maps' route");
            $data = $request->getParsedBody();
            $resultArr = array();

            return $response->withJson($resultArr, 200, JSON_PRETTY_PRINT);
        }

        /**
         * MapController Delete map function.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function deleteMap(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/maps' route");
            $data = $request->getParsedBody();
            $resultArr = array();

            return $response->withJson($resultArr, 200, JSON_PRETTY_PRINT);
        }
    }
