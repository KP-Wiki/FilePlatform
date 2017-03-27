<?php
    namespace Functions;
    use \Exception;
    use \DateTime;

    class MapInfo
    {
        private $utils = null;

        public function __construct(&$utilsClass) {
            $this -> utils = $utilsClass;
        }

        public function getMapDetails(&$dbHandler, $aMapId = null) {
            global $request;

            $content = Array();

            try {
                if (isset($request['query_vars']['user'])) {
                    $userId       = IntVal($request['query_vars']['user']);
                    $mapListItems = null;

                    if ($userId === null || $userId <= 0)
                        throw new Exception('Ilegal user ID : ' . $userId);

                    $query = 'SELECT ' .
                             '    `Maps`.`map_pk`, ' .
                             '    `Maps`.`map_name`, ' .
                             '    `Maps`.`map_downloads`, ' .
                             '    `Revisions`.`rev_map_description_short`, ' .
                             '    `Revisions`.`rev_map_description`, ' .
                             '    `Revisions`.`rev_upload_date`, ' .
                             '    `Users`.`user_name`, ' .
                             '    `MapTypes`.`map_type_name` ' .
                             'FROM ' .
                             '    `Maps` ' .
                             'LEFT JOIN ' .
                             '    `Revisions` ON `Maps`.`map_pk` = `Revisions`.`map_fk` ' .
                             'LEFT JOIN ' .
                             '    `Users` ON `Maps`.`user_fk` = `Users`.`user_pk` ' .
                             'LEFT JOIN ' .
                             '    `MapTypes` ON `Maps`.`map_type_fk` = `MapTypes`.`map_type_pk` ' .
                             'WHERE ' .
                             '    `Revisions`.`rev_status_fk` IN (0, 1) AND ' .
                             '    `Maps`.`user_fk` = :userid ' .
                             'ORDER BY ' .
                             '    `Maps`.`map_name` DESC;';
                    $dbHandler -> PrepareAndBind($query, Array('userid' => $userId));
                    $mapListItems = $dbHandler -> ExecuteAndFetchAll();
                    $dbHandler -> Clean();

                    $content['status'] = 'Ok';

                    if ($mapListItems != null) {
                        foreach ($mapListItems as $mapItem) {
                            $ratingQuery = 'SELECT ' .
                                           '    ROUND(AVG(CAST(`rating_amount` AS DECIMAL(12,2))), 1) AS avg_rating ' .
                                           'FROM ' .
                                           '    `Ratings` ' .
                                           'WHERE ' .
                                           '    `map_fk` = :mapid;';
                            $dbHandler -> PrepareAndBind($ratingQuery, Array('mapid' => $mapItem['map_pk']));
                            $avgRating = $dbHandler -> ExecuteAndFetch();
                            $lastChangeDate = new DateTime($mapItem['rev_upload_date']);

                            $contentItem = Array('map_pk'                    => IntVal($mapItem['map_pk']),
                                                 'map_name'                  => $mapItem['map_name'],
                                                 'map_downloads'             => IntVal($mapItem['map_downloads']),
                                                 'rev_map_description_short' => $mapItem['rev_map_description_short'],
                                                 'rev_map_description'       => $mapItem['rev_map_description'],
                                                 'rev_upload_date'           => $lastChangeDate -> format('Y-m-d H:i'),
                                                 'user_name'                 => $mapItem['user_name'],
                                                 'map_type_name'             => $mapItem['map_type_name'],
                                                 'avg_rating'                => ($avgRating['avg_rating'] === null ? 'n/a' : FloatVal($avgRating['avg_rating'])));

                            $content['data'][] = $contentItem;
                        };
                    } else {
                        $content['data'] = Array();
                    };

                    $this -> utils -> http_response_code(200);
                } elseif (isset($request['call_parts'][3]) || $aMapId !== null) {
                    $mapItem = null;
                    $mapId   = IntVal($aMapId !== null ? $aMapId : $request['call_parts'][3]);

                    if ($mapId === null || $mapId <= 0)
                        throw new Exception('Ilegal map ID : ' . $mapId);

                    $query1 = 'SET @mapid = :mapid;';
                    $dbHandler -> PrepareAndBind($query1, Array('mapid' => $mapId));
                    $dbHandler -> Execute();

                    $query2 = 'SELECT ' .
                              '    `Maps`.`map_name`, ' .
                              '    `Maps`.`map_downloads`, ' .
                              '    `Revisions`.`rev_pk`, ' .
                              '    `Revisions`.`rev_map_description_short`, ' .
                              '    `Revisions`.`rev_map_description`, ' .
                              '    `Revisions`.`rev_upload_date`, ' .
                              '    `Revisions`.`rev_map_version`, ' .
                              '    `Users`.`user_name`, ' .
                              '    `MapTypes`.`map_type_name`, ' .
                              '    ROUND(AVG(CAST(`Ratings`.`rating_amount` AS DECIMAL(12,2))), 1) AS avg_rating, ' .
                              '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 1 AND map_fk = @mapid), 0) AS rating_one, ' .
                              '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 2 AND map_fk = @mapid), 0) AS rating_two, ' .
                              '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 3 AND map_fk = @mapid), 0) AS rating_three, ' .
                              '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 4 AND map_fk = @mapid), 0) AS rating_four, ' .
                              '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 5 AND map_fk = @mapid), 0) AS rating_five ' .
                              'FROM ' .
                              '    `Maps` ' .
                              'LEFT JOIN ' .
                              '    `Revisions` ON `Maps`.`map_pk` = `Revisions`.`map_fk` ' .
                              'LEFT JOIN ' .
                              '    `Users` ON `Maps`.`user_fk` = `Users`.`user_pk` ' .
                              'LEFT JOIN ' .
                              '    `MapTypes` ON `Maps`.`map_type_fk` = `MapTypes`.`map_type_pk` ' .
                              'LEFT JOIN ' .
                              '    `Ratings` ON `Maps`.`map_pk` = `Ratings`.`map_fk` ' .
                              'WHERE ' .
                              '    `Revisions`.`rev_status_fk` = 1 AND ' .
                              '    `Maps`.`map_visible` = 1 AND ' .
                              '    `Maps`.`map_pk` = @mapid;';
                    $dbHandler -> PrepareAndBind($query2);
                    $mapItem = $dbHandler -> ExecuteAndFetch();

                    if ($mapItem != null && $mapItem['map_name'] != null) {
                        $this -> utils -> http_response_code(200);
                        $lastChangeDate = new DateTime($mapItem['rev_upload_date']);
                        $content['status']                            = 'Ok';
                        $content['data']['map_pk']                    = $mapId;
                        $content['data']['map_name']                  = $mapItem['map_name'];
                        $content['data']['map_downloads']             = IntVal($mapItem['map_downloads']);
                        $content['data']['rev_pk']                    = $mapItem['rev_pk'];
                        $content['data']['rev_map_description_short'] = $mapItem['rev_map_description_short'];
                        $content['data']['rev_map_description']       = $mapItem['rev_map_description'];
                        $content['data']['rev_upload_date']           = $lastChangeDate -> format('Y-m-d H:i');
                        $content['data']['rev_map_version']           = $mapItem['rev_map_version'];
                        $content['data']['user_name']                 = $mapItem['user_name'];
                        $content['data']['map_type_name']             = $mapItem['map_type_name'];
                        $content['data']['avg_rating']                = ($mapItem['avg_rating'] === null ? 'n/a' : FloatVal($mapItem['avg_rating']));
                        $content['data']['rating_one']                = IntVal($mapItem['rating_one']);
                        $content['data']['rating_two']                = IntVal($mapItem['rating_two']);
                        $content['data']['rating_three']              = IntVal($mapItem['rating_three']);
                        $content['data']['rating_four']               = IntVal($mapItem['rating_four']);
                        $content['data']['rating_five']               = IntVal($mapItem['rating_five']);
                    } else {
                        $this -> utils -> http_response_code(404);
                        $content['status']  = 'Error';
                        $content['message'] = 'Map with ID ' . $mapId . ' does not exist.';
                    };
                } else {
                    $mapListItems = null;

                    $query = 'SELECT ' .
                             '    `Maps`.`map_pk`, ' .
                             '    `Maps`.`map_name`, ' .
                             '    `Maps`.`map_downloads`, ' .
                             '    `Revisions`.`rev_map_description_short`, ' .
                             '    `Users`.`user_name`, ' .
                             '    `MapTypes`.`map_type_name` ' .
                             'FROM ' .
                             '    `Maps` ' .
                             'LEFT JOIN ' .
                             '    `Revisions` ON `Maps`.`map_pk` = `Revisions`.`map_fk` ' .
                             'LEFT JOIN ' .
                             '    `Users` ON `Maps`.`user_fk` = `Users`.`user_pk` ' .
                             'LEFT JOIN ' .
                             '    `MapTypes` ON `Maps`.`map_type_fk` = `MapTypes`.`map_type_pk` ' .
                             'WHERE ' .
                             '    `Revisions`.`rev_status_fk` = 1 AND ' .
                             '    `Maps`.`map_visible` = 1 ' .
                             'ORDER BY ' .
                             '    `Maps`.`map_name` DESC;';
                    $dbHandler -> PrepareAndBind($query);
                    $mapListItems = $dbHandler -> ExecuteAndFetchAll();
                    $dbHandler -> Clean();

                    $content['status'] = 'Ok';

                    if ($mapListItems != null) {
                        foreach ($mapListItems as $mapItem) {
                            $ratingQuery = 'SELECT ' .
                                           '    ROUND(AVG(CAST(`rating_amount` AS DECIMAL(12,2))), 1) AS avg_rating ' .
                                           'FROM ' .
                                           '    `Ratings` ' .
                                           'WHERE ' .
                                           '    `map_fk` = :mapid;';
                            $dbHandler -> PrepareAndBind($ratingQuery, Array('mapid' => $mapItem['map_pk']));
                            $avgRating = $dbHandler -> ExecuteAndFetch();

                            $contentItem = Array('map_pk'                    => IntVal($mapItem['map_pk']),
                                                 'map_name'                  => $mapItem['map_name'],
                                                 'map_downloads'             => IntVal($mapItem['map_downloads']),
                                                 'rev_map_description_short' => $mapItem['rev_map_description_short'],
                                                 'user_name'                 => $mapItem['user_name'],
                                                 'map_type_name'             => $mapItem['map_type_name'],
                                                 'avg_rating'                => ($avgRating['avg_rating'] === null ? 'n/a' : FloatVal($avgRating['avg_rating'])));

                            $content['data'][] = $contentItem;
                        };
                    } else {
                        $content['data'] = Array();
                    };

                    $this -> utils -> http_response_code(200);
                };
            } catch (Exception $e) {
                $this -> utils -> http_response_code(404);
                $content['status']  = 'Error';
                $content['message'] = 'Error retrieving data from the database.';
            };

            return $content;
        }
    }
