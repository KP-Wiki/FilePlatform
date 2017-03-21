<?php
    namespace Functions;
    use \Exception;

    class Rate
    {
        private $utils = null;

        public function __construct(&$utilsClass) {
            $this -> utils = $utilsClass;
        }

        public function getRating(&$dbHandler) {
            global $request;

            try {
                $mapID = IntVal($request['call_parts'][3]);

                if ($mapID === null || $mapID <= 0)
                    throw new Exception('Map ID out of acceptable range');

                $query1 = 'SET @mapid = :mapid;';
                $dbHandler -> PrepareAndBind ($query1, Array('mapid' => $mapID));
                $dbHandler -> Execute();

                $query2 = 'SELECT ' .
                          '    ROUND(AVG(CAST(`rating_amount` AS DECIMAL(12,2))), 1) AS avg_rating, ' .
                          '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 1 AND map_fk = @mapid), 0) AS rating_one, ' .
                          '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 2 AND map_fk = @mapid), 0) AS rating_two, ' .
                          '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 3 AND map_fk = @mapid), 0) AS rating_three, ' .
                          '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 4 AND map_fk = @mapid), 0) AS rating_four, ' .
                          '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 5 AND map_fk = @mapid), 0) AS rating_five ' .
                          'FROM ' .
                          '    `Ratings` ' .
                          'WHERE ' .
                          '    `map_fk` = @mapid;';
                $dbHandler -> PrepareAndBind ($query2);
                $ratings = $dbHandler -> ExecuteAndFetch();

                $this -> utils -> http_response_code(200);
                return Array('status' => 'Ok',
                             'data'   => $ratings);
            } catch (Exception $e) {
                $this -> utils -> http_response_code(400);
                return Array('status'  => 'Error',
                             'message' => 'Map ID out of acceptable range');
            };
        }

        public function insertRating(&$dbHandler) {
            global $request;

            try {
                if (!isset($_POST['score']))
                    throw new Exception('Map ID or Score out of acceptable range');

                $mapID    = IntVal($request['call_parts'][3]);
                $score    = IntVal($_POST['score']);
                $ratingIP = ip2long($this -> utils -> getClientIp());

                if ($mapID === null || $mapID <= 0 ||
                    $score <= 0 || $score >= 6)
                    throw new Exception('Map ID or Score out of acceptable range');

                $spamCheckQuery = 'SELECT ' .
                                  '    COUNT(*) AS rating_count ' .
                                  'FROM ' .
                                  '    `Ratings` ' .
                                  'WHERE ' .
                                  '    `rating_ip` = :ratingip AND' .
                                  '    `map_fk` = :mapid';
                $dbHandler -> PrepareAndBind ($spamCheckQuery, Array('ratingip' => $ratingIP,
                                                                     'mapid'    => $mapID));
                $ratingCount = $dbHandler -> ExecuteAndFetch();
                $dbHandler -> Clean();

                if ($ratingCount['rating_count'] >= 1) {
                    $this -> utils -> http_response_code(409);
                    return Array('status'  => 'Error',
                                 'message' => 'You have already rated this map.');
                };

                $insertQuery = 'INSERT INTO ' .
                               '    `Ratings` (`map_fk`, `rating_amount`, `rating_ip`) ' .
                               'VALUES ' .
                               '    (:mapid, :ratingamount, :ratingip);';
                $dbHandler -> PrepareAndBind ($insertQuery, Array('mapid'        => $mapID,
                                                                  'ratingamount' => $score,
                                                                  'ratingip'     => $ratingIP));
                $dbHandler -> Execute();

                $this -> utils -> http_response_code(201);
                return Array('status' => 'Ok',
                             'data'   => 'Rating processed succesfully');
            } catch (Exception $e) {
                $this -> utils -> http_response_code(400);
                return Array('status'  => 'Error',
                             'message' => 'Unable to process rating' . PHP_EOL . 'Map ID or Score out of acceptable range');
            };
        }
    }
