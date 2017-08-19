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

    use \Slim\Http\Request;
    use \Slim\Http\Response;
    use \Slim\Http\Stream;
    use \MapPlatform\Core;
    use \MapPlatform\AbstractClasses\ApiController;
    use \Imagick;
    use \ZipArchive;
    use \Exception;
    use \InvalidArgumentException;

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
         * MapController map download funtion.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function downloadMap(Request $request, Response $response, $args) {
            $this->container->logger->info("ManagementTools '/api/v1/maps/download/" . $args['revId'] . "' route");
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
            $fullPath = $config['uploadDirFull'] . $mapItem['rev_map_file_path'] . $mapItem['rev_map_file_name'];

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

                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'Unable to update map download count.'
                ], 500, JSON_PRETTY_PRINT);
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
            $this->container->security->checkRememberMe();

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group <= 0)) {
                return $response->withJson(['result' => 'Nope'], 400, JSON_PRETTY_PRINT);
            } else {
                try {
                    $database     = $this->container->dataBase->PDO;
                    $config       = $this->container->get('settings')['files'];
                    $data         = $request->getParsedBody();
                    $mapName      = filter_var($data['mapName'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_BACKTICK ||
                                                                                         FILTER_FLAG_ENCODE_LOW ||
                                                                                         FILTER_FLAG_ENCODE_HIGH ||
                                                                                         FILTER_FLAG_ENCODE_AMP);
                    $mapVersion   = filter_var($data['mapVersion'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_BACKTICK ||
                                                                                            FILTER_FLAG_ENCODE_LOW ||
                                                                                            FILTER_FLAG_ENCODE_HIGH ||
                                                                                            FILTER_FLAG_ENCODE_AMP);
                    $mapDescShort = filter_var($data['mapDescShort'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_BACKTICK ||
                                                                                              FILTER_FLAG_ENCODE_LOW ||
                                                                                              FILTER_FLAG_ENCODE_HIGH ||
                                                                                              FILTER_FLAG_ENCODE_AMP);
                    $mapDescFull  = filter_var($data['mapDescFull'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_BACKTICK ||
                                                                                             FILTER_FLAG_ENCODE_LOW ||
                                                                                             FILTER_FLAG_ENCODE_HIGH ||
                                                                                             FILTER_FLAG_ENCODE_AMP);
                    $mapType      = filter_var($data['mapType'], FILTER_SANITIZE_NUMBER_INT);

                    if (Empty($_FILES['mapFile']['name']) ||
                        Empty($_FILES['datFile']['name']) ||
                        Empty($mapName) ||
                        Empty($mapVersion) ||
                        Empty($mapDescShort) ||
                        Empty($mapDescFull) ||
                        ($mapType < 0)) {
                        throw new Exception('Invalid request, inputs missing');
                    };

                    $mapDirInArchive = $mapName . '/';
                    $mapDirOnDisk    = $config['uploadDirFull'] . $mapName . '/' . $mapVersion . '/';
                    $mapDirForDB     = $mapName . '/' . $mapVersion . '/';

                    $query    = $database->select(['COUNT(*) AS map_count'])
                                         ->from('Maps')
                                         ->where('map_name', '=', $mapName);
                    $stmt     = $query->execute();
                    $mapCount = $stmt->fetch();

                    if ($mapCount['map_count'] > 0)
                        throw new Exception('Map already exists!');

                    // Create the directory that will hold our newly created ZIP archive
                    $this->container->fileUtils->mkdirRecursive($mapDirForDB, $config['uploadDirFull']);
                    $mapArchive = new ZipArchive();

                    // Try to create the new archive
                    if (!$mapArchive->open($mapDirOnDisk . $mapName . '.zip', ZipArchive::CREATE))
                        throw new Exception('Unable to create the archive');

                    // Create a new directory
                    $mapArchive-> addEmptyDir($mapDirInArchive);
                    // Add the required files
                    $mapArchive->addFile($_FILES['mapFile']['tmp_name'], $mapDirInArchive . $mapName . '.map');
                    $mapArchive->addFile($_FILES['datFile']['tmp_name'], $mapDirInArchive . $mapName . '.dat');

                    if (!Empty($_FILES['scriptFile']['name']))
                        $mapArchive->addFile($_FILES['scriptFile']['tmp_name'], $mapDirInArchive . $mapName . '.script');

                    // Because PHP uses an odd manner of stacking multiple files into an array we will re-array them here
                    if (!Empty($_FILES['libxFiles']['name'][0])) {
                        $libxFiles = $this->container->fileUtils->reArrayFiles($_FILES['libxFiles']);

                        // Add the files
                        foreach ($libxFiles as $libxFile) {
                            $fileBitsArr   = Explode('.', $libxFile['name']);
                            $fileBitsCount = count($fileBitsArr);
                            $fileExtention = '.' . $fileBitsArr[$fileBitsCount - 2] . '.libx'; // Get the language part as well
                            $mapArchive->addFile($libxFile['tmp_name'], $mapDirInArchive . $mapName . $fileExtention);
                        };
                    };

                    $mapArchive->close();
                    $query = $database->insert(['map_name', 'user_fk', 'map_Type_fk'])
                                      ->into('Maps')
                                      ->values([$mapName, $_SESSION['user']->id, $mapType]);
                    $database->beginTransaction();
                    $mapId = $query->execute(True);

                    if ($mapId <= 0) {
                        $database->rollBack();
                        throw new Exception('Could not add the map to the database');
                    };

                    $database->commit();
                    $query = $database->insert([
                                            'map_fk',
                                            'rev_map_file_name',
                                            'rev_map_file_path',
                                            'rev_map_version',
                                            'rev_map_description_short',
                                            'rev_map_description',
                                            'rev_status_fk'
                                        ])
                                        ->into('Revisions')
                                        ->values([
                                            $mapId,
                                            $mapName . '.zip',
                                            $mapDirForDB,
                                            $mapVersion,
                                            $mapDescShort,
                                            $mapDescFull,
                                            1 // 1 = Enabled and visible
                                        ]);
                    $database->beginTransaction();
                    $revId = $query->execute(True);

                    if ($revId <= 0) {
                        $database->rollBack();
                        throw new Exception('Could not add the map to the database');
                    };

                    $database->commit();

                    if (!$this->uploadImages($database, $mapName, $config['uploadDir'] . $mapDirForDB, $revId, $data))
                        throw new Exception('Could not add the screenshots to the map');

                    return $response->withJson([
                        'result'  => 'Success',
                        'message' => 'Map has been added successfully!<br />' . PHP_EOL .
                                     'Redirecting you now.'
                    ], 200, JSON_PRETTY_PRINT);
                } catch (Exception $ex) {
                    return $response->withJson([
                        'result'  => 'Error',
                        'message' => $ex->getMessage()
                    ], 500, JSON_PRETTY_PRINT);
                };
            };
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
            $this->container->security->checkRememberMe();

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group <= 0)) {
                return $response->withJson(['result' => 'Nope'], 400, JSON_PRETTY_PRINT);
            } else {
                try {
                    $database   = $this->container->dataBase->PDO;
                    $config     = $this->container->get('settings')['files'];
                    $data       = $request->getParsedBody();
                    $mapVersion = (filter_var($data['newMapRevVersion'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_BACKTICK ||
                                                                                                 FILTER_FLAG_ENCODE_LOW ||
                                                                                                 FILTER_FLAG_ENCODE_HIGH ||
                                                                                                 FILTER_FLAG_ENCODE_AMP));

                    if (Empty($_FILES['newMapRevMapFile']['name']) ||
                        Empty($_FILES['newMapRevDatFile']['name']) ||
                        Empty($mapVersion)) {
                        throw new Exception('Invalid request, inputs missing');
                    };

                    /*
                    $mapItem     = null;
                    $mapId       = IntVal($request['call_parts'][1]);
                    $mapInfoFunc = new \Functions\MapInfo($this -> utils);
                    $mapItem     = $mapInfoFunc -> getMapDetails($dbHandler, $mapId);

                    if (Empty($mapItem) || $mapItem['data']['rev_map_version'] === $mapVersion)
                        throw new Exception('Map versions are identical, please change it.');

                    $mapArchive      = new ZipArchive();
                    $mapName         = $mapItem['data']['map_name'];
                    $mapDescShort    = $mapItem['data']['rev_map_description_short'];
                    $mapDescFull     = $mapItem['data']['rev_map_description'];
                    $mapDirInArchive = $mapName . '/';
                    $mapDirOnDisk    = $config['files']['uploadDir'] . '/' . $mapName . '/' . $mapVersion . '/';

                    // Create the directory that will hold our newly created ZIP archive
                    $this -> utils -> mkdirRecursive(APP_DIR . $mapDirOnDisk);

                    // Try to create the new archive
                    if (!$mapArchive -> open(APP_DIR . $mapDirOnDisk . $mapName . '.zip', ZIPARCHIVE::CREATE))
                        throw new Exception('Unable to create the archive');

                    // Create a new directory
                    $mapArchive -> addEmptyDir($mapDirInArchive);
                    // Add the required files
                    $mapArchive -> addFile($_FILES['newMapRevMapFile']['tmp_name'], $mapDirInArchive . $mapName . '.map');
                    $mapArchive -> addFile($_FILES['newMapRevDatFile']['tmp_name'], $mapDirInArchive . $mapName . '.dat');

                    if (!Empty($_FILES['newMapRevScriptFile']['name']))
                        $mapArchive -> addFile($_FILES['newMapRevScriptFile']['tmp_name'], $mapDirInArchive . $mapName . '.script');

                    // Because PHP uses an odd manner of stacking multiple files into an array we will re-array them here
                    if (!Empty($_FILES['newMapRevLibxFiles']['name'][0])) {
                        $libxFiles = $this -> utils -> reArrayFiles($_FILES['newMapRevLibxFiles']);

                        // Add the files
                        foreach ($libxFiles as $libxFile) {
                            $fileBitsArr   = Explode('.', $libxFile['name']);
                            $fileBitsCount = count($fileBitsArr);
                            $fileExtention = '.' . $fileBitsArr[$fileBitsCount - 2] . '.libx'; // Get the language part as well
                            $mapArchive -> addFile($libxFile['tmp_name'], $mapDirInArchive . $mapName . $fileExtention);
                        };
                    };

                    $mapArchive -> close();

                    $insertRevQuery = 'INSERT INTO ' . PHP_EOL .
                                      '    `Revisions` (`map_fk`, `rev_map_file_name`, `rev_map_file_path`, `rev_map_version`, ' .
                                      '`rev_map_description_short`, `rev_map_description`, `rev_status_fk`) '. PHP_EOL .
                                      'VALUES ' . PHP_EOL .
                                      '    (:mapid, :filename, :filepath, :mapversion, :mapdescshort, :mapdescfull, :revstatusid);';
                    $dbHandler -> PrepareAndBind($insertRevQuery, Array('mapid'        => $mapId,
                                                                        'filename'     => $mapName . '.zip',
                                                                        'filepath'     => $mapDirOnDisk,
                                                                        'mapversion'   => $mapVersion,
                                                                        'mapdescshort' => $mapDescShort,
                                                                        'mapdescfull'  => $mapDescFull,
                                                                        'revstatusid'  => 1));
                    $dbHandler -> Execute();
                    $revId = $dbHandler -> GetLastInsertId();
                    $dbHandler -> Clean();

                    if ($revId == null)
                        throw new Exception('Could not add the map to the database');

                    $updateQuery = 'UPDATE ' . PHP_EOL .
                                   '    `Revisions` '. PHP_EOL .
                                   'SET ' . PHP_EOL .
                                   '    `rev_status_fk` = 3 '. PHP_EOL .
                                   'WHERE ' . PHP_EOL .
                                   '    `rev_pk` = :maprevid;';
                    $dbHandler -> PrepareAndBind($updateQuery, Array('maprevid' => $mapItem['data']['rev_pk']));
                    $dbHandler -> Execute();
                    $dbHandler -> Clean();

                    $content['status']  = 'Success';
                    $content['message'] = 'Map has been added successfully!<br />' . PHP_EOL .
                                          'Redirecting you now.';
                    $content['data']    = $mapId;
                    */

                    $resultArr = array();

                    return $response->withJson($resultArr, 200, JSON_PRETTY_PRINT);
                } catch (Exception $ex) {
                    return $response->withJson([
                        'result' => 'Error',
                        'message' => $ex->getMessage()
                    ], 500, JSON_PRETTY_PRINT);
                };
            };
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
            $this->container->security->checkRememberMe();

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group <= 0)) {
                return $response->withJson(['result' => 'Nope'], 400, JSON_PRETTY_PRINT);
            } else {
                $data = $request->getParsedBody();

                return $response->withJson([], 200, JSON_PRETTY_PRINT);
            };
        }

        private function uploadImages(&$database, $mapName, $mapDir, $revId, $formData, $oldRevId = null) {
            $config = $this->container->get('settings')['images'];

            try {
                $imageOrderNum   = 0;
                $screenshotFiles = array();

                if ($oldRevId !== null) {
                    $query              = $database->select(['*'])
                                                   ->from('Screenshots')
                                                   ->where('rev_fk', '=', $oldRevId)
                                                   ->orderBy('screen_order', 'ASC');
                    $stmt               = $query->execute();
                    $oldScreenshotFiles = $stmt->fetchall();
                } else {
                    $oldScreenshotFiles = array();
                };

                if (!Empty($_FILES['screenshotFileOne']['tmp_name'])) {
                    $detectedType = exif_imagetype($_FILES['screenshotFileOne']['tmp_name']);
                    $validFile    = in_array($detectedType, $config['allowedTypes']);

                    if ($validFile) {
                        $_FILES['screenshotFileOne']['imageTitle']    = (Empty(filter_var($formData['screenshotTitleOne'],
                                                                                          FILTER_SANITIZE_STRING,
                                                                                          FILTER_FLAG_STRIP_BACKTICK ||
                                                                                          FILTER_FLAG_STRIP_LOW ||
                                                                                          FILTER_FLAG_STRIP_HIGH ||
                                                                                          FILTER_FLAG_STRIP_AMP))
                                                                            ? $mapName . '-' . $imageOrderNum
                                                                            : filter_var($formData['screenshotTitleOne'],
                                                                                         FILTER_SANITIZE_STRING,
                                                                                         FILTER_FLAG_STRIP_BACKTICK ||
                                                                                         FILTER_FLAG_STRIP_LOW ||
                                                                                         FILTER_FLAG_STRIP_HIGH ||
                                                                                         FILTER_FLAG_STRIP_AMP));
                        $_FILES['screenshotFileOne']['imageOrderNum'] = $imageOrderNum;
                        $_FILES['screenshotFileOne']['imageType']     = $detectedType;
                        $screenshotFiles[]                            = $_FILES['screenshotFileOne'];
                        $imageOrderNum++;
                    };
                };

                if (!Empty($_FILES['screenshotFileTwo']['tmp_name'])) {
                    $detectedType = exif_imagetype($_FILES['screenshotFileTwo']['tmp_name']);
                    $validFile    = in_array($detectedType, $config['allowedTypes']);

                    if ($validFile) {
                        $_FILES['screenshotFileTwo']['imageTitle']    = (Empty(filter_var($formData['screenshotTitleTwo'],
                                                                                          FILTER_SANITIZE_STRING,
                                                                                          FILTER_FLAG_STRIP_BACKTICK ||
                                                                                          FILTER_FLAG_STRIP_LOW ||
                                                                                          FILTER_FLAG_STRIP_HIGH ||
                                                                                          FILTER_FLAG_STRIP_AMP))
                                                                            ? $mapName . '-' . $imageOrderNum
                                                                            : filter_var($formData['screenshotTitleTwo'],
                                                                                         FILTER_SANITIZE_STRING,
                                                                                         FILTER_FLAG_STRIP_BACKTICK ||
                                                                                         FILTER_FLAG_STRIP_LOW ||
                                                                                         FILTER_FLAG_STRIP_HIGH ||
                                                                                         FILTER_FLAG_STRIP_AMP));
                        $_FILES['screenshotFileTwo']['imageOrderNum'] = $imageOrderNum;
                        $_FILES['screenshotFileTwo']['imageType']     = $detectedType;
                        $screenshotFiles[]                            = $_FILES['screenshotFileTwo'];
                        $imageOrderNum++;
                    };
                };

                if (!Empty($_FILES['screenshotFileThree']['tmp_name'])) {
                    $detectedType = exif_imagetype($_FILES['screenshotFileThree']['tmp_name']);
                    $validFile    = in_array($detectedType, $config['allowedTypes']);

                    if ($validFile) {
                        $_FILES['screenshotFileThree']['imageTitle']    = (Empty(filter_var($formData['screenshotTitleThree'],
                                                                                            FILTER_SANITIZE_STRING,
                                                                                            FILTER_FLAG_STRIP_BACKTICK ||
                                                                                            FILTER_FLAG_STRIP_LOW ||
                                                                                            FILTER_FLAG_STRIP_HIGH ||
                                                                                            FILTER_FLAG_STRIP_AMP))
                                                                            ? $mapName . '-' . $imageOrderNum
                                                                            : filter_var($formData['screenshotTitleThree'],
                                                                                         FILTER_SANITIZE_STRING,
                                                                                         FILTER_FLAG_STRIP_BACKTICK ||
                                                                                         FILTER_FLAG_STRIP_LOW ||
                                                                                         FILTER_FLAG_STRIP_HIGH ||
                                                                                         FILTER_FLAG_STRIP_AMP));
                        $_FILES['screenshotFileThree']['imageOrderNum'] = $imageOrderNum;
                        $_FILES['screenshotFileThree']['imageType']     = $detectedType;
                        $screenshotFiles[]                              = $_FILES['screenshotFileThree'];
                        $imageOrderNum++;
                    };
                };

                if (!Empty($_FILES['screenshotFileFour']['tmp_name'])) {
                    $detectedType = exif_imagetype($_FILES['screenshotFileFour']['tmp_name']);
                    $validFile    = in_array($detectedType, $config['allowedTypes']);

                    if ($validFile) {
                        $_FILES['screenshotFileFour']['imageTitle']    = (Empty(filter_var($formData['screenshotTitleFour'],
                                                                                           FILTER_SANITIZE_STRING,
                                                                                           FILTER_FLAG_STRIP_BACKTICK ||
                                                                                           FILTER_FLAG_STRIP_LOW ||
                                                                                           FILTER_FLAG_STRIP_HIGH ||
                                                                                           FILTER_FLAG_STRIP_AMP))
                                                                            ? $mapName . '-' . $imageOrderNum
                                                                            : filter_var($formData['screenshotTitleFour'],
                                                                                         FILTER_SANITIZE_STRING,
                                                                                         FILTER_FLAG_STRIP_BACKTICK ||
                                                                                         FILTER_FLAG_STRIP_LOW ||
                                                                                         FILTER_FLAG_STRIP_HIGH ||
                                                                                         FILTER_FLAG_STRIP_AMP));
                        $_FILES['screenshotFileFour']['imageOrderNum'] = $imageOrderNum;
                        $_FILES['screenshotFileFour']['imageType']     = $detectedType;
                        $screenshotFiles[]                             = $_FILES['screenshotFileFour'];
                        $imageOrderNum++;
                    };
                };

                if (!Empty($_FILES['screenshotFileFive']['tmp_name'])) {
                    $detectedType = exif_imagetype($_FILES['screenshotFileFive']['tmp_name']);
                    $validFile    = in_array($detectedType, $config['allowedTypes']);

                    if ($validFile) {
                        $_FILES['screenshotFileFive']['imageTitle']    = (Empty(filter_var($formData['screenshotTitleFive'],
                                                                                           FILTER_SANITIZE_STRING,
                                                                                           FILTER_FLAG_STRIP_BACKTICK ||
                                                                                           FILTER_FLAG_STRIP_LOW ||
                                                                                           FILTER_FLAG_STRIP_HIGH ||
                                                                                           FILTER_FLAG_STRIP_AMP))
                                                                            ? $mapName . '-' . $imageOrderNum
                                                                            : filter_var($formData['screenshotTitleFive'],
                                                                                         FILTER_SANITIZE_STRING,
                                                                                         FILTER_FLAG_STRIP_BACKTICK ||
                                                                                         FILTER_FLAG_STRIP_LOW ||
                                                                                         FILTER_FLAG_STRIP_HIGH ||
                                                                                         FILTER_FLAG_STRIP_AMP));
                        $_FILES['screenshotFileFive']['imageOrderNum'] = $imageOrderNum;
                        $_FILES['screenshotFileFive']['imageType']     = $detectedType;
                        $screenshotFiles[]                             = $_FILES['screenshotFileFive'];
                    };
                };

                foreach ($screenshotFiles as $screenshotFile) {
                    $imageObject = new Imagick($screenshotFile['tmp_name']);
                    $this->container->fileUtils->resizeImage($imageObject, $config['maxWidth'], $config['maxHeight']);

                    if ($screenshotFile['imageType'] == IMAGETYPE_GIF) {
                        $imageExtention = '.gif';
                    } else {
                        $imageExtention = '.png';
                        $imageObject->setImageFormat('png');
                    };

                    $imageFileName = $mapName . '-' . $screenshotFile['imageOrderNum'] . $imageExtention;
                    $imageObject->writeImage($this->container->get('settings')['appRootDir'] . $mapDir . $imageFileName);
                    $imageObject->destroy();

                    $query = $database->insert(['rev_fk', 'screen_title', 'screen_alt', 'screen_file_name', 'screen_path', 'screen_order'])
                                      ->into('Screenshots')
                                      ->values([
                                        $revId,
                                        $screenshotFile['imageTitle'],
                                        $imageFileName,
                                        $imageFileName,
                                        $mapDir,
                                        $screenshotFile['imageOrderNum']
                                    ]);
                    $database->beginTransaction();
                    $insertID = $query->execute(True);

                    if ($insertID > 0) {
                        $database->commit();
                    } else {
                        $database->rollBack();
                        $this->container->logger->debug('uploadImages -> Unable to insert screenshot into the database');
                    };
                };

                foreach ($oldScreenshotFiles as $oldScreenshotFile) { // Only append these to the revision but keep original location
                    $query = $database->insert(['rev_fk', 'screen_title', 'screen_alt', 'screen_file_name', 'screen_path', 'screen_order'])
                                      ->into('Screenshots')
                                      ->values([
                                            $revId,
                                            $oldScreenshotFile['screen_title'],
                                            $oldScreenshotFile['screen_alt'],
                                            $oldScreenshotFile['screen_file_name'],
                                            $oldScreenshotFile['screen_path'],
                                            $imageOrderNum
                                        ]);
                    $database->beginTransaction();
                    $insertID = $query->execute(True);

                    if ($insertID > 0) {
                        $database->commit();
                    } else {
                        $database->rollBack();
                        $this->container->logger->debug('uploadImages -> Unable to insert screenshot into the database');
                        continue;
                    };

                    $imageOrderNum++;
                };

                return True;
            } catch (Exception $e) {
                return False;
            };
        }
    }
