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
        public function __invoke(Request $request, Response $response, $args) {
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
        public function getQueue(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/flags/queue' route");
            $this->container->security->checkRememberMe();

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group <= 3))
                return $response->withJson(['result' => 'Nope'], 400, JSON_PRETTY_PRINT);

            $database = $this->container->dataBase->PDO;

            try {
                $query    = $database->select(['flag_pk',
                                               'rev_fk',
                                               'user_fk'
                                            ])
                                     ->from('Flags')
                                     ->whereNull('flag_assigned_user_fk')
                                     ->where('flag_status_fk', '=', 0, 'AND');
                $stmt     = $query->execute();
                $flagArr  = $stmt->fetchall();
                $responseArr = [
                    'status' => 'Ok',
                    'data' => []
                ];

                if (count($flagArr) <= 0)
                    return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);

                foreach ($flagArr as $flagItem) {
                    $responseArr['data'][] = [
                        'flag_pk' => IntVal($flagItem['flag_pk']),
                        'rev_fk'  => $flagItem['rev_fk'] == null ? 'N/A' : IntVal($flagItem['rev_fk']),
                        'user_fk' => $flagItem['user_fk'] == null ? 'N/A' : IntVal($flagItem['user_fk'])
                    ];
                };

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

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group <= 3))
                return $response->withJson(['result' => 'Nope'], 400, JSON_PRETTY_PRINT);

            $database = $this->container->dataBase->PDO;

            try {
                $query    = $database->select(['Flags.flag_pk',
                                               'Flags.rev_fk',
                                               'Flags.user_fk',
                                               'Flags.flag_status_fk',
                                               'FlagStatus.status AS flag_status'
                                            ])
                                     ->from('Flags')
                                     ->leftJoin('FlagStatus', 'FlagStatus.flag_status_pk', '=', 'Flags.flag_status_fk')
                                     ->where('Flags.flag_assigned_user_fk', '=', $_SESSION['user']->id)
                                     ->groupBy('Flags.flag_status_fk');
                $stmt     = $query->execute();
                $flagArr  = $stmt->fetchall();
                $responseArr = [
                    'status' => 'Ok',
                    'data' => []
                ];

                if (count($flagArr) <= 0)
                    return $response->withJson($responseArr, 200, JSON_PRETTY_PRINT);

                foreach ($flagArr as $flagItem) {
                    $responseArr['data'][] = [
                        'flag_pk'        => IntVal($flagItem['flag_pk']),
                        'rev_fk'         => $flagItem['rev_fk'] == null ? 'N/A' : IntVal($flagItem['rev_fk']),
                        'user_fk'        => $flagItem['user_fk'] == null ? 'N/A' : IntVal($flagItem['user_fk']),
                        'flag_status_fk' => IntVal($flagItem['flag_status_fk']),
                        'flag_status'    => $flagItem['flag_status']
                    ];
                };

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
                $revId    = filter_var($args['revId'], FILTER_SANITIZE_NUMBER_INT);

                if ($revId === null || $revId <= 0) {
                    return $response->withJson([
                        'result' => 'Error',
                        'message' => 'Invalid request, inputs missing'
                    ], 400, JSON_PRETTY_PRINT);
                };

                $query   = $database->select(['count(*) AS revCount'])
                                    ->from('Revisions')
                                    ->where('rev_pk', '=', $revId);
                $stmt    = $query->execute();
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
                $flagId = $query->execute(True);

                if ($flagId <= 0) {
                    $database->rollBack();
                    throw new Exception('Could not add the flag to the database');
                };

                $database->commit();

                return $response->withJson([
                    'result'  => 'Success',
                    'message' => 'Map has been flagged successfully!'
                ], 200, JSON_PRETTY_PRINT);
            } catch (Exception $ex) {
                $this->container->logger->error('flagMap -> Exception: ' . $ex->getMessage());

                return $response->withJson([
                    'result'  => 'Error',
                    'message' => $ex->getMessage()
                ], 500, JSON_PRETTY_PRINT);
            };
        }

        /**
         * FlagController Add user flag function.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
         public function flagUser(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/flags/user/" . $args['userId'] . "' route");
            $this->container->security->checkRememberMe();

            try {
                $database = $this->container->dataBase->PDO;
                $userId   = filter_var($args['userId'], FILTER_SANITIZE_NUMBER_INT);

                if ($userId === null || $userId <= 0) {
                    return $response->withJson([
                        'result' => 'Error',
                        'message' => 'Invalid request, inputs missing'
                    ], 400, JSON_PRETTY_PRINT);
                };

                $query   = $database->select(['count(*) AS userCount'])
                                    ->from('Users')
                                    ->where('user_pk', '=', $userId);
                $stmt    = $query->execute();
                $usrItem = $stmt->fetch();

                if (!array_key_exists('userCount', $usrItem) || $usrItem['userCount'] <= 0)
                    return $response->withJson([
                        'result' => 'Error',
                        'message' => 'User does not exists!'
                    ], 400, JSON_PRETTY_PRINT);

                $query = $database->insert(['user_fk', 'flag_status_fk'])
                                  ->into('Flags')
                                  ->values([$userId, 0]);
                $database->beginTransaction();
                $flagId = $query->execute(True);

                if ($flagId <= 0) {
                    $database->rollBack();
                    throw new Exception('Could not add the flag to the database');
                };

                $database->commit();

                return $response->withJson([
                    'result'  => 'Success',
                    'message' => 'Map has been flagged successfully!'
                ], 200, JSON_PRETTY_PRINT);
            } catch (Exception $ex) {
                $this->container->logger->error('flagMap -> Exception: ' . $ex->getMessage());

                return $response->withJson([
                    'result'  => 'Error',
                    'message' => $ex->getMessage()
                ], 500, JSON_PRETTY_PRINT);
            };
        }
#endregion

#region Update
        /**
         * FlagController map info update function.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function updateFlag(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/flags/" . $args['flagId'] . "/update' route");
            $this->container->security->checkRememberMe();

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group <= 3))
                return $response->withJson(['result' => 'Nope'], 400, JSON_PRETTY_PRINT);

            try {
                $database = $this->container->dataBase->PDO;
                $data     = $request->getParsedBody();
                $flagId   = filter_var($args['flagId'], FILTER_SANITIZE_NUMBER_INT);

                return $response->withJson([
                    'result' => 'Success',
                    'message' => 'Updated the map successfully.'
                ], 200, JSON_PRETTY_PRINT);
            } catch (Exception $ex) {
                return $response->withJson([
                    'result' => 'Error',
                    'message' => $ex->getMessage()
                ], 500, JSON_PRETTY_PRINT);
            };
        }
#endregion

#region Delete
        /**
         * FlagController Delete map function.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function deleteFlag(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/maps' route");
            $this->container->security->checkRememberMe();

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group <= 3))
                return $response->withJson(['result' => 'Nope'], 400, JSON_PRETTY_PRINT);

            return $response->withJson([], 200, JSON_PRETTY_PRINT);
        }
#endregion
    }
