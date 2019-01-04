<?php
    /**
     * The central controller for all flagging features
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
    use \DateTime;
    use \Imagick;
    use \ZipArchive;
    use \Exception;
    use \InvalidArgumentException;

    /**
     * Flag controller
     *
     * @package    MapPlatform
     * @subpackage Controllers\Api
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    class FlagController extends ApiController
    {
        /**
         * FlagController invoker.
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
         * FlagController Get queued flags.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function getQueue(Request $request, Response $response, $args)
        {
            $this->container->logger->info("MapPlatform '/api/v1/flags/queue' route");
            $this->container->security->checkRememberMe();

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group < 9))
                return $response->withJson(['result' => 'Nope'], 400, JSON_PRETTY_PRINT);

            $database = $this->container->dataBase->PDO;

            try {
                $query = $database->select([
                        'Flags.flag_pk',
                        'Flags.rev_fk',
                        'Revisions.rev_map_file_name',
                        'Revisions.rev_map_version'
                    ])
                    ->from('Flags')
                    ->leftJoin('Revisions', 'Revisions.rev_pk', '=', 'Flags.rev_fk')
                    ->whereNull('Flags.flag_assigned_user_fk')
                    ->where('Flags.flag_status_fk', '=', 0, 'AND');
                $stmt = $query->execute();
                $flagArr = $stmt->fetchall();
                $responseArr = [
                    'status' => 'Ok',
                    'data' => []
                ];

                if (count($flagArr) <= 0)
                    return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);

                foreach ($flagArr as $flagItem) {
                    $responseArr['data'][] = [
                        'flag_pk' => intval($flagItem['flag_pk']),
                        'rev_fk' => intval($flagItem['rev_fk']),
                        'rev_map_file_name' => $flagItem['rev_map_file_name'],
                        'rev_map_version' => $flagItem['rev_map_version']
                    ];
                }

                return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);
            } catch (Exception $ex) {
                $this->container->logger->error('getQueue -> ex = ' . $ex);
                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'Unable to retrieve flags, please try again later.',
                    'data' => []
                ], 500, JSON_PRETTY_PRINT);
            }
        }

        /**
         * FlagController Get flags assigned to current user.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function getMine(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/flags/mine' route");
            $this->container->security->checkRememberMe();

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group < 9))
                return $response->withJson(['result' => 'Nope'], 400, JSON_PRETTY_PRINT);

            $database = $this->container->dataBase->PDO;

            try {
                $query = $database->select([
                        'Flags.flag_pk',
                        'Flags.rev_fk',
                        'Revisions.rev_map_file_name',
                        'Revisions.rev_map_version'
                    ])
                    ->from('Flags')
                    ->leftJoin('Revisions', 'Revisions.rev_pk', '=', 'Flags.rev_fk')
                    ->where('Flags.flag_assigned_user_fk', '=', $_SESSION['user']->id, 'AND')
                    ->where('Flags.flag_status_fk', '=', 1, 'AND')
                    ->groupBy('Flags.rev_fk');
                $stmt = $query->execute();
                $flagArr = $stmt->fetchall();
                $responseArr = [
                    'status' => 'Ok',
                    'data' => []
                ];

                if (count($flagArr) <= 0)
                    return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);

                foreach ($flagArr as $flagItem) {
                    $responseArr['data'][] = [
                        'flag_pk' => intval($flagItem['flag_pk']),
                        'rev_fk' => intval($flagItem['rev_fk']),
                        'rev_map_file_name' => $flagItem['rev_map_file_name'],
                        'rev_map_version' => $flagItem['rev_map_version']
                    ];
                }

                return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);
            } catch (Exception $ex) {
                $this->container->logger->error('getMine -> ex = ' . $ex);
                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'Unable to retrieve flags, please try again later.',
                    'data' => []
                ], 500, JSON_PRETTY_PRINT);
            }
        }
#endregion

#region Create
        /**
         * FlagController Add map flag function.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function flagMap(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/flags/map/" . $args['revId'] . "' route");
            $this->container->security->checkRememberMe();

            try {
                $database = $this->container->dataBase->PDO;
                $revId = filter_var($args['revId'], FILTER_SANITIZE_NUMBER_INT);

                if ($revId === null || $revId <= 0) {
                    return $response->withJson([
                        'result' => 'Error',
                        'message' => 'Invalid request, inputs missing'
                    ], 400, JSON_PRETTY_PRINT);
                }

                $query = $database->select(['count(*) AS revCount'])
                    ->from('Revisions')
                    ->where('rev_pk', '=', $revId);
                $stmt = $query->execute();
                $mapItem = $stmt->fetch();

                if (!array_key_exists('revCount', $mapItem) || $mapItem['revCount'] <= 0)
                    return $response->withJson([
                        'result' => 'Error',
                        'message' => 'Map does not exists!'
                    ], 400, JSON_PRETTY_PRINT);

                $query = $database->insert(['rev_fk', 'flag_status_fk'])
                    ->into('Flags')
                    ->values([$revId, 0]);
                $database->beginTransaction();
                $flagId = $query->execute(true);

                if ($flagId <= 0) {
                    $database->rollBack();
                    throw new Exception('Could not add the flag to the database');
                }

                $database->commit();
                return $response->withJson([
                    'result' => 'Success',
                    'message' => 'Map has been flagged successfully!'
                ], 200, JSON_PRETTY_PRINT);
            } catch (Exception $ex) {
                $this->container->logger->error('flagMap -> Exception: ' . $ex->getMessage());
                return $response->withJson([
                    'result' => 'Error',
                    'message' => $ex->getMessage()
                ], 500, JSON_PRETTY_PRINT);
            }
        }
#endregion

#region Update
        /**
         * FlagController pickup method.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function pickupFlag(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/flags/" . $args['flagId'] . "/pickup' route");
            $this->container->security->checkRememberMe();

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group < 9))
                return $response->withJson(['result' => 'Nope'], 400, JSON_PRETTY_PRINT);

            try {
                $database = $this->container->dataBase->PDO;
                $flagId = filter_var($args['flagId'], FILTER_SANITIZE_NUMBER_INT);
                $query = $database->update()
                    ->table('Flags')
                    ->set(['flag_status_fk' => 1 /* Assigned */])
                    ->set(['flag_assigned_user_fk' => $_SESSION['user']->id])
                    ->where('flag_pk', '=', $flagId);
                $database->beginTransaction();
                $affectedRows = $query->execute();

                if ($affectedRows <= 0) {
                    $database->rollBack();
                    throw new Exception('Could not update the flag status');
                }

                $database->commit();
                return $response->withJson([
                    'result' => 'Success',
                    'message' => 'Updated the flag successfully.'
                ], 200, JSON_PRETTY_PRINT);
            } catch (Exception $ex) {
                return $response->withJson([
                    'result' => 'Error',
                    'message' => $ex->getMessage()
                ], 500, JSON_PRETTY_PRINT);
            }
        }

        /**
         * FlagController close method.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function closeFlag(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/flags/" . $args['flagId'] . "/close' route");
            $this->container->security->checkRememberMe();

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group < 9))
                return $response->withJson(['result' => 'Nope'], 400, JSON_PRETTY_PRINT);

            try {
                $database = $this->container->dataBase->PDO;
                $flagId = filter_var($args['flagId'], FILTER_SANITIZE_NUMBER_INT);
                $query = $database->update()
                    ->table('Flags')
                    ->set(['flag_status_fk' => 2 /* Closed */])
                    ->where('flag_pk', '=', $flagId);
                $database->beginTransaction();
                $affectedRows = $query->execute();

                if ($affectedRows <= 0) {
                    $database->rollBack();
                    throw new Exception('Could not update the flag status');
                }

                $database->commit();
                return $response->withJson([
                    'result' => 'Success',
                    'message' => 'Updated the flag successfully.'
                ], 200, JSON_PRETTY_PRINT);
            } catch (Exception $ex) {
                return $response->withJson([
                    'result' => 'Error',
                    'message' => $ex->getMessage()
                ], 500, JSON_PRETTY_PRINT);
            }
        }

        /**
         * FlagController resolve method.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function resolveFlag(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/flags/" . $args['flagId'] . "/resolve' route");
            $this->container->security->checkRememberMe();

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group < 9))
                return $response->withJson(['result' => 'Nope'], 400, JSON_PRETTY_PRINT);

            try {
                $database = $this->container->dataBase->PDO;
                $flagId = filter_var($args['flagId'], FILTER_SANITIZE_NUMBER_INT);
                $query = $database->select(['rev_fk'])
                    ->from('Flags')
                    ->where('flag_pk', '=', $flagId);
                $flagItem = $query->execute()->fetch();
                $this->container->logger->debug('flagItem -> ' . print_r($flagItem, true));
                $query = $database->update()
                    ->table('Revisions')
                    ->set(['rev_status_fk' => 4 /* Removed */])
                    ->where('rev_pk', '=', $flagItem['rev_fk']);
                $database->beginTransaction();
                $affectedRows = $query->execute();

                if ($affectedRows <= 0) {
                    $database->rollBack();
                    throw new Exception('Could not update the revision status');
                }

                $database->commit();
                $query = $database->update()
                    ->table('Flags')
                    ->set(['flag_status_fk' => 2 /* Closed */])
                    ->where('flag_pk', '=', $flagId);
                $database->beginTransaction();
                $affectedRows = $query->execute();

                if ($affectedRows <= 0) {
                    $database->rollBack();
                    throw new Exception('Could not update the flag status');
                }

                $database->commit();
                return $response->withJson([
                    'result' => 'Success',
                    'message' => 'Updated the flag successfully.'
                ], 200, JSON_PRETTY_PRINT);
            } catch (Exception $ex) {
                return $response->withJson([
                    'result' => 'Error',
                    'message' => $ex->getMessage()
                ], 500, JSON_PRETTY_PRINT);
            }
        }
#endregion
    }
