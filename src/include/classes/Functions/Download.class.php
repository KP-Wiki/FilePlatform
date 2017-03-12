<?php
    namespace Functions;

    class Download
    {
        public function __construct() {
        }

        public function getContent(&$dbHandler) {
            global $request;

            $mapFile = null;

            $selectQuery = 'SELECT ' .
                           '    `Revisions`.`rev_file_name`, ' .
                           '    `Revisions`.`rev_file_path`, ' .
                           '    `Files`.`file_pk`, ' .
                           '    `Files`.`file_downloads` ' .
                           'FROM ' .
                           '    `Revisions` ' .
                           'LEFT JOIN ' .
                           '    `Files` ON `Revisions`.`file_fk` = `Files`.`file_pk` ' .
                           'WHERE ' .
                           '    `Revisions`.`rev_pk` = :revid AND ' .
                           '    `Revisions`.`rev_status_fk` = 1;';
            $dbHandler -> PrepareAndBind ($selectQuery, Array('revid' => IntVal($request['query_vars']['file'])));
            $mapFile = $dbHandler -> ExecuteAndFetch();

            if ($mapFile === null || Empty($mapFile)) {
                header('HTTP/1.1 404 Not Found');
                header('Location: /home');
                return null;
            };

            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $mapFile['rev_file_path'] . $mapFile['rev_file_name'];

            if (!file_exists($fullPath)) {
                header('HTTP/1.1 404 Not Found');
                header('Location: /home');
                return null;
            };

            $mapDownloads = $mapFile['file_downloads'] + 1;
            $updateQuery  = 'UPDATE ' .
                            '    `Files` ' .
                            'SET ' .
                            '    `file_downloads` = :downloads ' .
                            'WHERE ' .
                            '    `file_pk` = :fileid;';
            $dbHandler -> PrepareAndBind ($updateQuery, Array('downloads' => $mapDownloads,
                                                              'fileid'    => $mapFile['file_pk']));
            $dbHandler -> Execute();

            return $fullPath;
        }
    }
