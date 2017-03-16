<?php
    namespace Functions;

    class Rate
    {
        private $utils = null;

        public function __construct(&$utilsClass) {
            $this -> utils = $utilsClass;
        }

        public function getApiResponse(&$dbHandler) {
            global $request;

            try {
                $mapID    = IntVal($request['call_parts'][3]);
                $score    = IntVal($request['query_vars']['score']);
                $ratingIP = ip2long($this -> utils -> getClientIp());

                if ($mapID === null || $mapID <= 0 ||
                    $score <= 0 || $score >= 6)
                    throw new \Exception('Map ID out of acceptable scope');

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

                if ($ratingCount['rating_count'] >= 1)
                    return 'You have already rated this map.';

                $insertQuery = 'INSERT INTO ' .
                               '    `Ratings` (`map_fk`, `rating_amount`, `rating_ip`) ' .
                               'VALUES ' .
                               '    (:mapid, :ratingamount, :ratingip);';
                $dbHandler -> PrepareAndBind ($insertQuery, Array('mapid'        => $mapID,
                                                                  'ratingamount' => IntVal($request['query_vars']['score']),
                                                                  'ratingip'     => $ratingIP));
                $dbHandler -> Execute();

                return 'Rating processed succesfully';
            } catch (Exception $e) {
                return 'Unable to process rating';
            };
        }
    }
