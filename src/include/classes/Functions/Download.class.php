<?php
    namespace Functions;

    class Download
    {
        public function __construct() {
        }

        public function getApiResponse(&$dbHandler) {
            global $request;

            if (!isset($request['call_parts'][3])) {
                header('HTTP/1.1 404 Not Found');
                return null;
            };

            $mapItem = null;
            $mapId   = IntVal($request['call_parts'][3]);

            if ($mapId === null || $mapId <= 0) {
                header('HTTP/1.1 404 Not Found');
                return null;
            };

            $selectQuery = 'SELECT ' .
                           '    `Maps`.`map_pk`, ' .
                           '    `Maps`.`map_downloads`, ' .
                           '    `Revisions`.`rev_map_file_name`, ' .
                           '    `Revisions`.`rev_map_file_path` ' .
                           'FROM ' .
                           '    `Revisions` ' .
                           'LEFT JOIN ' .
                           '    `Maps` ON `Revisions`.`map_fk` = `Maps`.`map_pk` ' .
                           'WHERE ' .
                           '    `Revisions`.`rev_pk` = :revid AND ' .
                           '    `Revisions`.`rev_status_fk` = 1;';
            $dbHandler -> PrepareAndBind($selectQuery, Array('revid' => $mapId));
            $mapItem = $dbHandler -> ExecuteAndFetch();
            $dbHandler -> Clean();

            if ($mapItem === null || Empty($mapItem)) {
                header('HTTP/1.1 404 Not Found');
                return null;
            };

            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $mapItem['rev_map_file_path'] . $mapItem['rev_map_file_name'];

            if (!file_exists($fullPath)) {
                header('HTTP/1.1 404 Not Found');
                return null;
            };

            $mapDownloads = $mapItem['map_downloads'] + 1;
            $updateQuery  = 'UPDATE ' .
                            '    `Maps` ' .
                            'SET ' .
                            '    `map_downloads` = :downloads ' .
                            'WHERE ' .
                            '    `map_pk` = :mapid;';
            $dbHandler -> PrepareAndBind($updateQuery, Array('downloads' => $mapDownloads,
                                                             'mapid'     => $mapItem['map_pk']));
            $dbHandler -> Execute();

            return $fullPath;
        }
    }
