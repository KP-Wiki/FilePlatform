<?php
    namespace Functions;

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
                    throw new \Exception('Map ID out of acceptable range');

                $query = 'SELECT ' .
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
                $dbHandler -> PrepareAndBind ($spamCheckQuery, Array('mapid' => $mapID));
                $ratings = $dbHandler -> ExecuteAndFetch();

                $this -> utils -> http_response_code(200);
                return $ratings
            } catch (Exception $e) {
                $this -> utils -> http_response_code(400);
                return 'Map ID out of acceptable range';
            };
        }

        public function insertRating(&$dbHandler) {
            global $request;

            try {
                $mapID    = IntVal($request['call_parts'][3]);
                $score    = IntVal($request['query_vars']['score']);
                $ratingIP = ip2long($this -> utils -> getClientIp());

                if ($mapID === null || $mapID <= 0 ||
                    $score <= 0 || $score >= 6)
                    throw new \Exception('Map ID or Score out of acceptable range');

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
                    return 'You have already rated this map.';
                };

                $insertQuery = 'INSERT INTO ' .
                               '    `Ratings` (`map_fk`, `rating_amount`, `rating_ip`) ' .
                               'VALUES ' .
                               '    (:mapid, :ratingamount, :ratingip);';
                $dbHandler -> PrepareAndBind ($insertQuery, Array('mapid'        => $mapID,
                                                                  'ratingamount' => IntVal($request['query_vars']['score']),
                                                                  'ratingip'     => $ratingIP));
                $dbHandler -> Execute();

                $this -> utils -> http_response_code(201);
                return 'Rating processed succesfully';
            } catch (Exception $e) {
                $this -> utils -> http_response_code(400);
                return 'Unable to process rating' . PHP_EOL . 'Map ID or Score out of acceptable range';
            };
        }
    }
