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
                                  '    `file_fk` = :fileid';
                $dbHandler -> PrepareAndBind ($spamCheckQuery, Array('ratingip' => $ratingIP,
                                                                     'fileid'   => $request['query_vars']['file']));
                $ratingCount = $dbHandler -> ExecuteAndFetch();
                $dbHandler -> Clean();

                if ($ratingCount['rating_count'] >= 1)
                    return 'You have already rated this file.';

                $insertQuery = 'INSERT INTO ' .
                               '    `Ratings` (`file_fk`, `rating_amount`, `rating_ip`) ' .
                               'VALUES ' .
                               '    (:fileid, :ratingamount, :ratingip);';
                $dbHandler -> PrepareAndBind ($insertQuery, Array('fileid'       => IntVal($request['query_vars']['file']),
                                                                  'ratingamount' => IntVal($request['query_vars']['score']),
                                                                  'ratingip'     => $ratingIP));
                $dbHandler -> Execute();

                return 'Thank you for your rating.';
            } catch (Exception $e) {
                return 'Unable to process rating';
            };
        }
    }
