<?php
    namespace Functions;

    class Rate
    {
        private $utils = null;

        public function __construct(&$utilsClass) {
            $this -> utils = $utilsClass;
        }

        public function getContent(&$dbHandler) {
            global $request;

            try {
                $ratingIP       = ip2long($this -> utils -> getClientIp());
                $spamCheckQuery = 'SELECT ' .
                                  '    COUNT(*) AS rating_count ' .
                                  'FROM ' .
                                  '    `Ratings` ' .
                                  'WHERE ' .
                                  '    `rating_ip` = :ratingip AND' .
                                  '    `map_fk` = :mapid';
                $dbHandler -> PrepareAndBind ($spamCheckQuery, Array('ratingip' => $ratingIP,
                                                                     'mapid'    => $request['query_vars']['map']));
                $ratingCount = $dbHandler -> ExecuteAndFetch();
                $dbHandler -> Clean();

                if ($ratingCount['rating_count'] >= 1)
                    return 'You have already rated this map.';

                $insertQuery = 'INSERT INTO ' .
                               '    `Ratings` (`map_fk`, `rating_amount`, `rating_ip`) ' .
                               'VALUES ' .
                               '    (:mapid, :ratingamount, :ratingip);';
                $dbHandler -> PrepareAndBind ($insertQuery, Array('mapid'        => IntVal($request['query_vars']['map']),
                                                                  'ratingamount' => IntVal($request['query_vars']['score']),
                                                                  'ratingip'     => $ratingIP));
                $dbHandler -> Execute();

                return 'Thank you for your rating.';
            } catch (Exception $e) {
                return 'Unable to process rating';
            };
        }
    }
