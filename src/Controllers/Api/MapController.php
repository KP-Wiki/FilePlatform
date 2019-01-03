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

    use \DateTime;
    use \Exception;
    use \Imagick;
    use \InvalidArgumentException;
    use \MapPlatform\AbstractClasses\ApiController;
    use \MapPlatform\Core\Constants;
    use \Slim\Http\Request;
    use \Slim\Http\Response;
    use \Slim\Http\Stream;
    use \ZipArchive;

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
        public function __invoke(Request $request, Response $response, $args)
        {
            return $response;
        }

#region Retrieve
        /**
         * MapController Get all maps.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function getAllMaps(Request $request, Response $response, $args)
        {
            $this->container->logger->info("MapPlatform '/api/v1/maps' route");
            $this->container->security->checkRememberMe();
            $database = $this->container->dataBase->PDO;

            try {
                $query = $database->select(['Maps.map_pk',
                                            'Maps.map_name',
                                            'Maps.map_downloads',
                                            'Maps.user_fk',
                                            'Revisions.rev_map_description_short',
                                            'Revisions.rev_map_description',
                                            'Revisions.rev_upload_date',
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
                $stmt = $query->execute();
                $mapItemArr = $stmt->fetchall();
                $responseArr = [
                    'status' => 'Ok',
                    'data' => []
                ];

                if (count($mapItemArr) <= 0)
                    return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);

                foreach ($mapItemArr as $mapItem) {
                    $query = $database->select(['ROUND(AVG(CAST(rating_amount AS DECIMAL(12,2))), 1) AS avg_rating'])
                                      ->from('Ratings')
                                      ->where('map_fk', '=', $mapItem['map_pk']);
                    $stmt = $query->execute();
                    $avgRating = $stmt->fetch();
                    $lastChangeDate = date_create($mapItem['rev_upload_date']);
                    $responseArr['data'][] = [
                        'map_pk' => intval($mapItem['map_pk']),
                        'map_name' => $mapItem['map_name'],
                        'map_downloads' => intval($mapItem['map_downloads']),
                        'rev_map_description_short' => $mapItem['rev_map_description_short'],
                        'rev_map_description' => $mapItem['rev_map_description'],
                        'rev_upload_date' => $lastChangeDate->format('Y-m-d H:i'),
                        'user_pk' => $mapItem['user_fk'],
                        'user_name' => $mapItem['user_name'],
                        'map_type_name' => $mapItem['map_type_name'],
                        'avg_rating' => ($avgRating['avg_rating'] === null ? 'n/a' : floatval($avgRating['avg_rating']))
                    ];
                }

                return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);
            } catch (Exception $ex) {
                $this->container->logger->error('getAllMaps -> ex = ' . $ex);
                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'Unable to retrieve maps, please try again later.',
                    'data' => []
                ], 500, JSON_PRETTY_PRINT);
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
        public function getMap(Request $request, Response $response, $args)
        {
            $this->container->logger->info("MapPlatform '/api/v1/maps/" . $args['mapId'] . "' route");
            $this->container->security->checkRememberMe();
            $mapId = filter_var($args['mapId'], FILTER_SANITIZE_NUMBER_INT);
            $database = $this->container->dataBase->PDO;

            if ($mapId === null || $mapId <= 0) {
                $this->container->logger->error('getMap -> Map not found');
                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'Map with ID ' . $mapId . ' can not be found',
                    'data' => []
                ], 404, JSON_PRETTY_PRINT);
            }

            try {
                $mapItem = $this->getMapFromDB($database, $mapId);

                if ($mapItem != null && $mapItem['map_name'] != null) {
                    $lastChangeDate = new DateTime($mapItem['rev_upload_date']);
                    $responseArr = [
                        'status' => 'Success',
                        'data' => [
                            'map_pk' => $mapId,
                            'map_name' => $mapItem['map_name'],
                            'map_downloads' => intval($mapItem['map_downloads']),
                            'rev_pk' => $mapItem['rev_pk'],
                            'rev_map_description_short' => $mapItem['rev_map_description_short'],
                            'rev_map_description' => $mapItem['rev_map_description'],
                            'rev_upload_date' => $lastChangeDate->format('Y-m-d H:i'),
                            'rev_map_version' => $mapItem['rev_map_version'],
                            'user_pk' => $mapItem['user_fk'],
                            'user_name' => $mapItem['user_name'],
                            'map_type_name' => $mapItem['map_type_name'],
                            'avg_rating' => ($mapItem['avg_rating'] === null ? 'n/a' : floatval($mapItem['avg_rating'])),
                            'rating_one' => intval($mapItem['rating_one']),
                            'rating_two' => intval($mapItem['rating_two']),
                            'rating_three' => intval($mapItem['rating_three']),
                            'rating_four' => intval($mapItem['rating_four']),
                            'rating_five' => intval($mapItem['rating_five'])
                        ]
                    ];
                    return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);
                } else {
                    $this->container->logger->error('getMap -> Map with ID ' . $mapId . ' does not exist.');
                    return $response->withJson([
                        'status' => 'Error',
                        'message' => 'Map with ID ' . $mapId . ' does not exist.',
                        'data' => []
                    ], 404, JSON_PRETTY_PRINT);
                }
            } catch (Exception $ex) {
                $this->container->logger->error('getMap -> ex = ' . $ex);
                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'Unable to retrieve maps for the specified user, please try again later.'
                ], 500, JSON_PRETTY_PRINT);
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
            $this->container->security->checkRememberMe();
            $userId = filter_var($args['userId'], FILTER_SANITIZE_NUMBER_INT);
            $database = $this->container->dataBase->PDO;

            if ($userId === null || $userId <= 0) {
                $this->container->logger->error('getMapsByUser -> Invalid user ID');
                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'Invalid user ID',
                    'data' => []
                ], 400, JSON_PRETTY_PRINT);
            }

            try {
                $query = $database->select(['Maps.map_pk',
                                            'Maps.map_name',
                                            'Maps.map_downloads',
                                            'Maps.user_fk',
                                            'Revisions.rev_map_description_short',
                                            'Revisions.rev_map_description',
                                            'Revisions.rev_upload_date',
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
                $stmt = $query->execute();
                $mapItemArr = $stmt->fetchall();
                $responseArr = [
                    'status' => 'Success',
                    'data' => []
                ];

                if (count($mapItemArr) <= 0)
                    return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);

                foreach ($mapItemArr as $mapItem) {
                    $query = $database->select(['ROUND(AVG(CAST(rating_amount AS DECIMAL(12,2))), 1) AS avg_rating'])
                                      ->from('Ratings')
                                      ->where('map_fk', '=', $mapItem['map_pk']);
                    $stmt = $query->execute();
                    $avgRating = $stmt->fetch();
                    $lastChangeDate = date_create($mapItem['rev_upload_date']);
                    $responseArr['data'][] = [
                        'map_pk' => intval($mapItem['map_pk']),
                        'map_name' => $mapItem['map_name'],
                        'map_downloads' => intval($mapItem['map_downloads']),
                        'rev_map_description_short' => $mapItem['rev_map_description_short'],
                        'rev_map_description' => $mapItem['rev_map_description'],
                        'rev_upload_date' => $lastChangeDate->format('Y-m-d H:i'),
                        'user_pk' => $mapItem['user_fk'],
                        'user_name' => $mapItem['user_name'],
                        'map_type_name' => $mapItem['map_type_name'],
                        'avg_rating' => ($avgRating['avg_rating'] === null ? 'n/a' : floatval($avgRating['avg_rating']))
                    ];
                }

                return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);
            } catch (Exception $ex) {
                $this->container->logger->error('getMapsByUser -> ex = ' . $ex);

                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'Unable to retrieve maps for the specified user, please try again later.',
                    'data' => []
                ], 500, JSON_PRETTY_PRINT);
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
            $this->container->security->checkRememberMe();
            $mapItem = null;
            $revId = filter_var($args['revId'], FILTER_SANITIZE_NUMBER_INT);

            if ($revId === null || $revId <= 0) {
                $this->container->logger->error('downloadMap -> Invalid map revision');
                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'Invalid map revision'
                ], 404, JSON_PRETTY_PRINT);
            }

            $database = $this->container->dataBase->PDO;
            $config = $this->container->get('settings')['files'];
            $query = $database->select(['Maps.map_pk',
                                             'Maps.map_downloads',
                                             'Revisions.rev_map_file_name',
                                             'Revisions.rev_map_file_path'])
                                   ->from('Revisions')
                                   ->leftJoin('Maps', 'Maps.map_pk', '=', 'Revisions.map_fk')
                                   ->where('Revisions.rev_pk', '=', $revId)
                                   ->where('Revisions.rev_status_fk', '=', 1, 'AND');
            $stmt = $query->execute();
            $mapItemArr = $stmt->fetchall();

            if (count($mapItemArr) < 1) {
                $this->container->logger->error('downloadMap -> Map revision not found');
                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'File not found'
                ], 404, JSON_PRETTY_PRINT);
            }

            $mapItem = $mapItemArr[0];
            $fullPath = $config['uploadDirFull'] . $mapItem['rev_map_file_path'] . $mapItem['rev_map_file_name'];

            if (!file_exists($fullPath)) {
                $this->container->logger->error('downloadMap -> Map revision physical files not found');
                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'File not found'
                ], 404, JSON_PRETTY_PRINT);
            }

            $mapDownloads = $mapItem['map_downloads'] + 1;
            $query = $database->update()
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
            }

            $fileHandle = fopen($fullPath, 'rb');
            $stream = new Stream($fileHandle); // Create a stream instance for the response body
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
#endregion

#region Create
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
                    $database = $this->container->dataBase->PDO;
                    $config = $this->container->get('settings')['files'];
                    $data = $request->getParsedBody();
                    $files = $request->getUploadedFiles();
                    $this->container->logger->debug("data: " . print_r($data, True));
                    $mapName = filter_var($data['mapName'], FILTER_SANITIZE_STRING, Constants::STRING_FILTER_FLAGS);
                    $mapVersion = filter_var($data['mapVersion'], FILTER_SANITIZE_STRING, Constants::STRING_FILTER_FLAGS);
                    $mapDescShort = filter_var($data['mapDescShort'], FILTER_SANITIZE_STRING, Constants::STRING_FILTER_FLAGS);
                    $mapDescFull = filter_var($data['mapDescFull'], FILTER_SANITIZE_STRING, Constants::STRING_FILTER_FLAGS);
                    $mapType = filter_var($data['mapType'], FILTER_SANITIZE_NUMBER_INT);

                    if (
                        Empty($_FILES['mapFile']['name']) ||
                        Empty($_FILES['datFile']['name']) ||
                        Empty($mapName) ||
                        Empty($mapVersion) ||
                        Empty($mapDescShort) ||
                        Empty($mapDescFull) ||
                        ($mapType < 0)
                    ) {
                        return $response->withJson([
                            'result' => 'Error',
                            'message' => 'Invalid request, inputs missing'
                        ], 400, JSON_PRETTY_PRINT);
                    }

                    $mapDirInArchive = $mapName . '/';
                    $mapDirForDB = $mapDirInArchive . $mapVersion . '/';
                    $mapDirOnDisk = $config['uploadDirFull'] . $mapDirForDB;
                    $query = $database->select(['COUNT(*) AS map_count'])
                                      ->from('Maps')
                                      ->where('map_name', '=', $mapName);
                    $stmt = $query->execute();
                    $mapCount = $stmt->fetch();

                    if ($mapCount['map_count'] > 0)
                        return $response->withJson([
                            'result' => 'Error',
                            'message' => 'Map already exists!'
                        ], 400, JSON_PRETTY_PRINT);

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
                            $fileBitsArr = explode('.', $libxFile['name']);
                            $fileBitsCount = count($fileBitsArr);
                            $fileExtention = '.' . $fileBitsArr[$fileBitsCount - 2] . '.libx'; // Get the language part as well
                            $mapArchive->addFile($libxFile['tmp_name'], $mapDirInArchive . $mapName . $fileExtention);
                        }
                    }

                    $mapArchive->close();
                    $query = $database->insert(['map_name', 'user_fk', 'map_Type_fk'])
                                      ->into('Maps')
                                      ->values([$mapName, $_SESSION['user']->id, $mapType]);
                    $database->beginTransaction();
                    $mapId = $query->execute(true);

                    if ($mapId <= 0) {
                        $database->rollBack();
                        throw new Exception('Could not add the map to the database');
                    }

                    $database->commit();
                    $query = $database->insert(['map_fk',
                                                'rev_map_file_name',
                                                'rev_map_file_path',
                                                'rev_map_version',
                                                'rev_map_description_short',
                                                'rev_map_description',
                                                'rev_status_fk'
                                      ])
                                      ->into('Revisions')
                                      ->values([$mapId,
                                                $mapName . '.zip',
                                                $mapDirForDB,
                                                $mapVersion,
                                                $mapDescShort,
                                                $mapDescFull,
                                                1 // 1 = Enabled and visible
                                      ]);
                    $database->beginTransaction();
                    $revId = $query->execute(true);

                    if ($revId <= 0) {
                        $database->rollBack();
                        throw new Exception('Could not add the map to the database');
                    }

                    $database->commit();

                    if (!$this->uploadImages($database, $mapName, $config['uploadDir'] . $mapDirForDB, $revId, $data))
                        throw new Exception('Could not add the screenshots to the map');

                    return $response->withJson([
                        'result' => 'Success',
                        'message' => 'Map has been added successfully!<br />Redirecting you now.'
                    ], 200, JSON_PRETTY_PRINT);
                } catch (Exception $ex) {
                    $this->container->logger->error('addMap -> Exception: ' . $ex->getMessage());
                    return $response->withJson([
                        'result' => 'Error',
                        'message' => $ex->getMessage()
                    ], 500, JSON_PRETTY_PRINT);
                }
            }
        }
#endregion

#region Update
        /**
         * MapController map info update function.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function updateMapInfo(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/maps/" . $args['mapId'] . "/updateinfo' route");
            $this->container->security->checkRememberMe();

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group <= 0)) {
                return $response->withJson(['result' => 'Nope'], 400, JSON_PRETTY_PRINT);
            } else {
                try {
                    $database = $this->container->dataBase->PDO;
                    $data = $request->getParsedBody();
                    $mapId = filter_var($args['mapId'], FILTER_SANITIZE_NUMBER_INT);
                    $this->container->logger->debug("data: " . print_r($data, True));

                    if (Empty($data['editMapDescShort']) ||
                        Empty($data['editMapDescFull']) ||
                        Empty($mapId)) {
                        return $response->withJson([
                            'result' => 'Error',
                            'message' => 'Invalid request, inputs missing'
                        ], 400, JSON_PRETTY_PRINT);
                    }

                    $editMapDescShort = filter_var($data['editMapDescShort'], FILTER_SANITIZE_STRING, Constants::STRING_FILTER_FLAGS);
                    $editMapDescFull = filter_var($data['editMapDescFull'], FILTER_SANITIZE_STRING, Constants::STRING_FILTER_FLAGS);
                    $this->container->logger->debug("mapId: " . print_r($mapId, True));
                    $mapItem = $this->getMinimalMapFromDB($database, $mapId);

                    if (($mapItem != null) && ($mapItem['map_name'] != null)) {
                        if ($mapItem['user_fk'] == $_SESSION['user']->id) {
                            $query = $database->insert(['map_fk',
                                                        'rev_map_file_name',
                                                        'rev_map_file_path',
                                                        'rev_map_version',
                                                        'rev_map_description_short',
                                                        'rev_map_description',
                                                        'rev_status_fk'
                                              ])
                                              ->into('Revisions')
                                              ->values([$mapId,
                                                        $mapItem['rev_map_file_name'],
                                                        $mapItem['rev_map_file_path'],
                                                        $mapItem['rev_map_version'],
                                                        $editMapDescShort,
                                                        $editMapDescFull,
                                                        1 // 1 = Enabled and visible
                                              ]);
                            $database->beginTransaction();
                            $revId = $query->execute(True);

                            if ($revId <= 0) {
                                $database->rollBack();
                                throw new Exception('Could not update the map');
                            }

                            $database->commit();
                            $query = $database->update()
                                              ->table('Revisions')
                                              ->set(['rev_status_fk' => 3 /* Disabled */])
                                              ->where('rev_pk', '=', $mapItem['rev_pk']);
                            $database->beginTransaction();
                            $affectedRows = $query->execute();

                            if ($affectedRows <= 0) {
                                $database->rollBack();
                                throw new Exception('Could not update the previous revision');
                            }

                            $database->commit();
                            return $response->withJson([
                                'result' => 'Success',
                                'message' => 'Updated the map successfully.'
                            ], 200, JSON_PRETTY_PRINT);
                        } else {
                            $this->container->logger->error('updateMapFiles -> User ' . $_SESSION['user']->id . ' tried to edit someone else\'s map.');
                            return $response->withJson([
                                'result' => 'Error',
                                'message' => 'You can only edit your own maps.'
                            ], 400, JSON_PRETTY_PRINT);
                        }
                    } else {
                        $this->container->logger->error('updateMapInfo -> Map with ID ' . $mapId . ' does not exist.');
                        return $response->withJson([
                            'status' => 'Error',
                            'message' => 'Map with ID ' . $mapId . ' does not exist.'
                        ], 404, JSON_PRETTY_PRINT);
                    }
                } catch (Exception $ex) {
                    return $response->withJson([
                        'result' => 'Error',
                        'message' => $ex->getMessage()
                    ], 500, JSON_PRETTY_PRINT);
                }
            }
        }

        /**
         * MapController map file update function.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function updateMapFiles(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/maps/" . $args['mapId'] . "/updatefiles' route");
            $this->container->security->checkRememberMe();

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group <= 0)) {
                return $response->withJson(['result' => 'Nope'], 400, JSON_PRETTY_PRINT);
            } else {
                try {
                    $database = $this->container->dataBase->PDO;
                    $config = $this->container->get('settings')['files'];
                    $data = $request->getParsedBody();
                    $mapId = filter_var($args['mapId'], FILTER_SANITIZE_NUMBER_INT);
                    $this->container->logger->debug("data: " . print_r($data, True));

                    if (
                        Empty($data['editMapRevVersion']) ||
                        Empty($_FILES['editMapMapFile']['name']) ||
                        Empty($_FILES['editMapDatFile']['name']) ||
                        Empty($mapId)
                    ) {
                        return $response->withJson([
                            'result' => 'Error',
                            'message' => 'Invalid request, inputs missing'
                        ], 400, JSON_PRETTY_PRINT);
                    }

                    $mapItem = $this->getMinimalMapFromDB($database, $mapId);
                    $mapVersion = filter_var($data['editMapRevVersion'], FILTER_SANITIZE_STRING, Constants::STRING_FILTER_FLAGS);

                    if (($mapItem != null) && ($mapItem['map_name'] != null)) {
                        if ($mapItem['user_fk'] == $_SESSION['user']->id) {
                            $mapName = $mapItem['map_name'];
                            $mapDirInArchive = $mapName . '/';
                            $mapDirForDB = $mapDirInArchive . $mapVersion . '/';
                            $mapDirOnDisk = $config['uploadDirFull'] . $mapDirForDB;

                            // Create the directory that will hold our newly created ZIP archive
                            $this->container->fileUtils->mkdirRecursive($mapDirForDB, $config['uploadDirFull']);
                            $mapArchive = new ZipArchive();

                            // Try to create the new archive
                            if (!$mapArchive->open($mapDirOnDisk . $mapName . '.zip', ZipArchive::CREATE))
                                throw new Exception('Unable to create the archive');

                            // Create a new directory
                            $mapArchive-> addEmptyDir($mapDirInArchive);
                            // Add the required files
                            $mapArchive->addFile($_FILES['editMapMapFile']['tmp_name'], $mapDirInArchive . $mapName . '.map');
                            $mapArchive->addFile($_FILES['editMapDatFile']['tmp_name'], $mapDirInArchive . $mapName . '.dat');

                            if (!Empty($_FILES['editMapDcriptFile']['name']))
                                $mapArchive->addFile($_FILES['editMapScriptFile']['tmp_name'], $mapDirInArchive . $mapName . '.script');

                            // Because PHP uses an odd manner of stacking multiple files into an array we will re-array them here
                            if (!Empty($_FILES['editMapLibxFiles']['name'][0])) {
                                $libxFiles = $this->container->fileUtils->reArrayFiles($_FILES['editMapLibxFiles']);

                                // Add the files
                                foreach ($libxFiles as $libxFile) {
                                    $fileBitsArr = explode('.', $libxFile['name']);
                                    $fileBitsCount = count($fileBitsArr);
                                    $fileExtention = '.' . $fileBitsArr[$fileBitsCount - 2] . '.libx'; // Get the language part as well
                                    $mapArchive->addFile($libxFile['tmp_name'], $mapDirInArchive . $mapName . $fileExtention);
                                }
                            }

                            $mapArchive->close();
                            $query = $database->insert(['map_fk',
                                                        'rev_map_file_name',
                                                        'rev_map_file_path',
                                                        'rev_map_version',
                                                        'rev_map_description_short',
                                                        'rev_map_description',
                                                        'rev_status_fk'
                                              ])
                                              ->into('Revisions')
                                              ->values([$mapId,
                                                        $mapItem['rev_map_file_name'],
                                                        $mapDirForDB,
                                                        $mapVersion,
                                                        $mapItem['rev_map_description_short'],
                                                        $mapItem['rev_map_description'],
                                                        1 // 1 = Enabled and visible
                                              ]);
                            $database->beginTransaction();
                            $revId = $query->execute(true);

                            if ($revId <= 0) {
                                $database->rollBack();
                                throw new Exception('Could not update the map');
                            }

                            $database->commit();
                            $query = $database->update()
                                              ->table('Revisions')
                                              ->set(['rev_status_fk' => 3 /* Disabled */])
                                              ->where('rev_pk', '=', $mapItem['rev_pk']);
                            $database->beginTransaction();
                            $affectedRows = $query->execute();

                            if ($affectedRows <= 0) {
                                $database->rollBack();
                                throw new Exception('Could not update the previous revision');
                            }

                            $database->commit();
                            return $response->withJson([
                                'result' => 'Success',
                                'message' => 'Updated the map successfully.'
                            ], 200, JSON_PRETTY_PRINT);
                        } else {
                            $this->container->logger->error('updateMapFiles -> User ' . $_SESSION['user']->id . ' tried to edit someone else\'s map.');
                            return $response->withJson([
                                'result' => 'Error',
                                'message' => 'You can only edit your own maps.'
                            ], 400, JSON_PRETTY_PRINT);
                        }
                    } else {
                        $this->container->logger->error('updateMapFiles -> Map with ID ' . $mapId . ' does not exist.');
                        return $response->withJson([
                            'status' => 'Error',
                            'message' => 'Map with ID ' . $mapId . ' does not exist.'
                        ], 404, JSON_PRETTY_PRINT);
                    }
                } catch (Exception $ex) {
                    return $response->withJson([
                        'result' => 'Error',
                        'message' => $ex->getMessage()
                    ], 500, JSON_PRETTY_PRINT);
                }
            }
        }
#endregion

#region Delete
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
            }
        }
#endregion

#region Misc functions
        /**
         * Map image upload function.
         *
         * @param \Slim\PDO\Database $database
         * @param string $mapName
         * @param string $mapDir
         * @param int $revId
         * @param array $formData
         * @param int|null $oldRevId
         *
         * @return boolean
         */
        private function uploadImages(&$database, $mapName, $mapDir, $revId, $formData, $oldRevId = null) {
            $config = $this->container->get('settings')['images'];

            try {
                $imageOrderNum   = 0;
                $screenshotFiles = [];

                if ($oldRevId !== null) {
                    $query = $database->select(['*'])
                                      ->from('Screenshots')
                                      ->where('rev_fk', '=', $oldRevId)
                                      ->orderBy('screen_order', 'ASC');
                    $stmt = $query->execute();
                    $oldScreenshotFiles = $stmt->fetchall();
                } else {
                    $oldScreenshotFiles = [];
                }

                if (!Empty($_FILES['screenshotFileOne']['tmp_name'])) {
                    $detectedType = exif_imagetype($_FILES['screenshotFileOne']['tmp_name']);
                    $validFile = in_array($detectedType, $config['allowedTypes']);

                    if ($validFile) {
                        $_FILES['screenshotFileOne']['imageTitle'] = (Empty(filter_var($formData['screenshotTitleOne'],
                                                                                       FILTER_SANITIZE_STRING,
                                                                                       Constants::STRING_FILTER_FLAGS))
                                                                        ? $mapName . '-' . $imageOrderNum
                                                                        : filter_var($formData['screenshotTitleOne'],
                                                                                     FILTER_SANITIZE_STRING,
                                                                                     Constants::STRING_FILTER_FLAGS));
                        $_FILES['screenshotFileOne']['imageOrderNum'] = $imageOrderNum;
                        $_FILES['screenshotFileOne']['imageType'] = $detectedType;
                        $screenshotFiles[] = $_FILES['screenshotFileOne'];
                        $imageOrderNum++;
                    }
                }

                if (!Empty($_FILES['screenshotFileTwo']['tmp_name'])) {
                    $detectedType = exif_imagetype($_FILES['screenshotFileTwo']['tmp_name']);
                    $validFile = in_array($detectedType, $config['allowedTypes']);

                    if ($validFile) {
                        $_FILES['screenshotFileTwo']['imageTitle'] = (Empty(filter_var($formData['screenshotTitleTwo'],
                                                                                       FILTER_SANITIZE_STRING,
                                                                                       Constants::STRING_FILTER_FLAGS))
                                                                        ? $mapName . '-' . $imageOrderNum
                                                                        : filter_var($formData['screenshotTitleTwo'],
                                                                                     FILTER_SANITIZE_STRING,
                                                                                     Constants::STRING_FILTER_FLAGS));
                        $_FILES['screenshotFileTwo']['imageOrderNum'] = $imageOrderNum;
                        $_FILES['screenshotFileTwo']['imageType'] = $detectedType;
                        $screenshotFiles[] = $_FILES['screenshotFileTwo'];
                        $imageOrderNum++;
                    }
                }

                if (!Empty($_FILES['screenshotFileThree']['tmp_name'])) {
                    $detectedType = exif_imagetype($_FILES['screenshotFileThree']['tmp_name']);
                    $validFile = in_array($detectedType, $config['allowedTypes']);

                    if ($validFile) {
                        $_FILES['screenshotFileThree']['imageTitle'] = (Empty(filter_var($formData['screenshotTitleThree'],
                                                                                         FILTER_SANITIZE_STRING,
                                                                                         Constants::STRING_FILTER_FLAGS))
                                                                        ? $mapName . '-' . $imageOrderNum
                                                                        : filter_var($formData['screenshotTitleThree'],
                                                                                     FILTER_SANITIZE_STRING,
                                                                                     Constants::STRING_FILTER_FLAGS));
                        $_FILES['screenshotFileThree']['imageOrderNum'] = $imageOrderNum;
                        $_FILES['screenshotFileThree']['imageType'] = $detectedType;
                        $screenshotFiles[] = $_FILES['screenshotFileThree'];
                        $imageOrderNum++;
                    }
                }

                if (!Empty($_FILES['screenshotFileFour']['tmp_name'])) {
                    $detectedType = exif_imagetype($_FILES['screenshotFileFour']['tmp_name']);
                    $validFile = in_array($detectedType, $config['allowedTypes']);

                    if ($validFile) {
                        $_FILES['screenshotFileFour']['imageTitle'] = (Empty(filter_var($formData['screenshotTitleFour'],
                                                                                        FILTER_SANITIZE_STRING,
                                                                                        Constants::STRING_FILTER_FLAGS))
                                                                        ? $mapName . '-' . $imageOrderNum
                                                                        : filter_var($formData['screenshotTitleFour'],
                                                                                     FILTER_SANITIZE_STRING,
                                                                                     Constants::STRING_FILTER_FLAGS));
                        $_FILES['screenshotFileFour']['imageOrderNum'] = $imageOrderNum;
                        $_FILES['screenshotFileFour']['imageType'] = $detectedType;
                        $screenshotFiles[] = $_FILES['screenshotFileFour'];
                        $imageOrderNum++;
                    }
                }

                if (!Empty($_FILES['screenshotFileFive']['tmp_name'])) {
                    $detectedType = exif_imagetype($_FILES['screenshotFileFive']['tmp_name']);
                    $validFile = in_array($detectedType, $config['allowedTypes']);

                    if ($validFile) {
                        $_FILES['screenshotFileFive']['imageTitle'] = (Empty(filter_var($formData['screenshotTitleFive'],
                                                                                        FILTER_SANITIZE_STRING,
                                                                                        Constants::STRING_FILTER_FLAGS))
                                                                        ? $mapName . '-' . $imageOrderNum
                                                                        : filter_var($formData['screenshotTitleFive'],
                                                                                     FILTER_SANITIZE_STRING,
                                                                                     Constants::STRING_FILTER_FLAGS));
                        $_FILES['screenshotFileFive']['imageOrderNum'] = $imageOrderNum;
                        $_FILES['screenshotFileFive']['imageType'] = $detectedType;
                        $screenshotFiles[] = $_FILES['screenshotFileFive'];
                    }
                }

                foreach ($screenshotFiles as $screenshotFile) {
                    $imageObject = new Imagick($screenshotFile['tmp_name']);
                    $this->container->fileUtils->resizeImage($imageObject, $config['maxWidth'], $config['maxHeight']);

                    if ($screenshotFile['imageType'] == IMAGETYPE_GIF) {
                        $imageExtention = '.gif';
                    } else {
                        $imageExtention = '.png';
                        $imageObject->setImageFormat('png');
                    }

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
                    $insertID = $query->execute(true);

                    if ($insertID > 0) {
                        $database->commit();
                    } else {
                        $database->rollBack();
                        $this->container->logger->debug('uploadImages -> Unable to insert screenshot into the database');
                    }
                }

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
                    $insertID = $query->execute(true);

                    if ($insertID > 0) {
                        $database->commit();
                    } else {
                        $database->rollBack();
                        $this->container->logger->debug('uploadImages -> Unable to insert screenshot into the database');
                        continue;
                    }

                    $imageOrderNum++;
                }

                return true;
            } catch (Exception $e) {
                return false;
            }
        }

        /**
         * Map info retrieval function.
         *
         * @param \Slim\PDO\Database $aDatabase
         * @param int $aMapId
         *
         * @return array|null
         */
        private function getMapFromDB(&$aDatabase, $aMapId) {
            try {
                $query = 'SET @mapid = :mapid;';
                $stmt = $aDatabase->prepare($query);
                $stmt->bindParam(':mapid', $aMapId);
                $stmt->execute();
                $query = $aDatabase->select(['Maps.map_name',
                                             'Maps.map_downloads',
                                             'Maps.user_fk',
                                             'Revisions.rev_pk',
                                             'Revisions.rev_map_description_short',
                                             'Revisions.rev_map_description',
                                             'Revisions.rev_upload_date',
                                             'Revisions.rev_map_version',
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
                                   ->where('Maps.map_pk', '=', $aMapId, 'AND');
                $stmt = $query->execute();
                $mapItem = $stmt->fetch();
                return $mapItem;
            } catch (Exception $ex) {
                return null;
            }
        }

        private function getMinimalMapFromDB(&$aDatabase, $aMapId) {
            try {
                $query = $aDatabase->select(['Maps.map_name',
                                             'Maps.user_fk',
                                             'Revisions.rev_pk',
                                             'Revisions.rev_map_file_name',
                                             'Revisions.rev_map_file_path',
                                             'Revisions.rev_map_version',
                                             'Revisions.rev_map_description_short',
                                             'Revisions.rev_map_description'
                                        ])
                                   ->from('Maps')
                                   ->leftJoin('Revisions', 'Revisions.map_fk', '=', 'Maps.map_pk')
                                   ->leftJoin('MapTypes', 'MapTypes.map_type_pk', '=', 'Maps.map_type_fk')
                                   ->where('Revisions.rev_status_fk', '=', 1)
                                   /* ->where('Maps.map_visible', '=', 1, 'AND') // Disabled for possible use later on */
                                   ->where('Maps.map_pk', '=', $aMapId, 'AND');
                $stmt = $query->execute();
                $mapItem = $stmt->fetch();
                return $mapItem;
            } catch (Exception $ex) {
                return null;
            }
        }
#endregion
    }
