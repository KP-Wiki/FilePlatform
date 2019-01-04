<?php
    /**
     * The central controller for all rating requests
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
     * Rating controller
     *
     * @package    MapPlatform
     * @subpackage Controllers\Api
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    class RatingController extends ApiController
    {
        /**
         * RatingController invoker.
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

        /**
         * RatingController default page.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function home(Request $request, Response $response, $args)
        {
            $this->container->logger->info("ManagementTools '/api/v1/testscript" . (empty($args['catchall']) ? "" : "/" . $args['catchall']) . "' route");
            return $response->withJson([], 200, JSON_PRETTY_PRINT);
        }

        public function getRating(Request $request, Response $response, $args)
        {
            $this->container->logger->info("MapPlatform '/api/v1/rating/" . $args['mapId'] . "' GET route");
            $database = $this->container->dataBase->PDO;
            $mapId = filter_var($args['mapId'], FILTER_SANITIZE_NUMBER_INT);
            $resultArr = [];
            $resultStatus = 200;
            $query = 'SET @mapid = :mapid;';
            $stmt = $database->prepare($query);
            $stmt->bindParam(':mapid', $mapId);
            $stmt->execute();
            $query = $database->select([
                    'ROUND(AVG(CAST(Ratings.rating_amount AS DECIMAL(12,2))), 1) AS avg_rating',
                    'IFNULL((SELECT COUNT(*) FROM Ratings WHERE rating_amount = 1 AND map_fk = @mapid), 0) AS rating_one',
                    'IFNULL((SELECT COUNT(*) FROM Ratings WHERE rating_amount = 2 AND map_fk = @mapid), 0) AS rating_two',
                    'IFNULL((SELECT COUNT(*) FROM Ratings WHERE rating_amount = 3 AND map_fk = @mapid), 0) AS rating_three',
                    'IFNULL((SELECT COUNT(*) FROM Ratings WHERE rating_amount = 4 AND map_fk = @mapid), 0) AS rating_four',
                    'IFNULL((SELECT COUNT(*) FROM Ratings WHERE rating_amount = 5 AND map_fk = @mapid), 0) AS rating_five'
                ])
                ->from('Ratings')
                ->where('map_fk', '=', $mapId);
            $stmt = $query->execute();
            $ratings = $stmt->fetch();

            if (($ratings != null) && ($ratings['avg_rating'] != null)) {
                $resultArr = [
                    'status' => 'Ok',
                    'data' => $ratings
                ];
            } else {
                $resultArr = [
                    'status' => 'Error',
                    'message' => 'Map ID out of acceptable range'
                ];
                $resultStatus = 400;
            }

            return $response->withJson($resultArr, $resultStatus, JSON_PRETTY_PRINT);
        }

        public function addRating(Request $request, Response $response, $args)
        {
            $this->container->logger->info("MapPlatform '/api/v1/rating/" . $args['mapId'] . "' POST route");
            $database = $this->container->dataBase->PDO;
            $mapId = filter_var($args['mapId'], FILTER_SANITIZE_NUMBER_INT);
            $data = $request->getParsedBody();

            try {
                if (!isset($data['score']))
                    throw new Exception('Map ID or Score out of acceptable range');

                $score = filter_var($data['score'], FILTER_SANITIZE_NUMBER_INT);
                $ratingIP = ip2long($this->container->miscUtils->getClientIp());

                if ($mapId === null || $mapId <= 0 || $score <= 0 || $score >= 6)
                    throw new Exception('Map ID or Score out of acceptable range');

                $query = $database->select(['COUNT(*) AS rating_count'])
                    ->from('Ratings')
                    ->where('rating_ip', '=', $ratingIP)
                    ->where('map_fk', '=', $mapId, 'AND');
                $stmt = $query->execute();
                $ratingCount = $stmt->fetch();

                if ($ratingCount['rating_count'] >= 1)
                    return $response->withJson([
                        'status' => 'Error',
                        'message' => 'You have already rated this map.'
                    ], 409, JSON_PRETTY_PRINT);

                $query = $database->insert(['map_fk', 'rating_amount', 'rating_ip'])
                    ->into('Ratings')
                    ->values([$mapId, $score, $ratingIP]);
                $database->beginTransaction();
                $insertId = $query->execute(True);

                if ($insertId > 0) {
                    $database->commit();
                    return $response->withJson([
                        'status' => 'Ok',
                        'data' => 'Rating processed succesfully'
                    ], 201, JSON_PRETTY_PRINT);
                } else {
                    $database->rollBack();
                    throw new Exception('Map ID or Score out of acceptable range');
                }
            } catch (Exception $e) {
                return $response->withJson([
                    'status' => 'Error',
                    'message' => 'Unable to process rating' . PHP_EOL . 'Map ID or Score out of acceptable range'
                ], 400, JSON_PRETTY_PRINT);
            }
        }
    }
