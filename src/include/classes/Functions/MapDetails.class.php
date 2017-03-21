<?php
    namespace Functions;
    use \Exception;
    use \DateTime;

    class MapDetails
    {
        private $utils = null;

        public function __construct(&$utilsClass) {
            $this -> utils = $utilsClass;
        }

        public function getContent(&$dbHandler) {
            global $request;

            $mapItem = null;

            $query1 = 'SET @mapid = :mapid;';
            $dbHandler -> PrepareAndBind($query1, Array('mapid' => IntVal($request['query_vars']['map'])));
            $dbHandler -> Execute();

            $query2 = 'SELECT ' .
                      '    `Maps`.`map_pk`, ' .
                      '    `Maps`.`map_name`, ' .
                      '    `Maps`.`map_downloads`, ' .
                      '    `Revisions`.`rev_pk`, ' .
                      '    `Revisions`.`rev_map_version`, ' .
                      '    `Revisions`.`rev_map_description`, ' .
                      '    `Revisions`.`rev_upload_date`, ' .
                      '    `Users`.`user_name`, ' .
                      '    ROUND(AVG(CAST(`Ratings`.`rating_amount` AS DECIMAL(12,2))), 1) AS avg_rating, ' .
                      '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 1 AND map_fk = @mapid), 0) AS rating_one, ' .
                      '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 2 AND map_fk = @mapid), 0) AS rating_two, ' .
                      '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 3 AND map_fk = @mapid), 0) AS rating_three, ' .
                      '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 4 AND map_fk = @mapid), 0) AS rating_four, ' .
                      '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 5 AND map_fk = @mapid), 0) AS rating_five ' .
                      'FROM ' .
                      '    `Users` ' .
                      'LEFT JOIN ' .
                      '    `Maps` ON `Users`.`user_pk` = `Maps`.`user_fk` ' .
                      'LEFT JOIN ' .
                      '    `Revisions` ON `Maps`.`map_pk` = `Revisions`.`map_fk` ' .
                      'LEFT JOIN ' .
                      '    `Ratings` ON `Maps`.`map_pk` = `Ratings`.`map_fk` ' .
                      'WHERE ' .
                      '    `Revisions`.`rev_status_fk` = 1 AND ' .
                      '    `Revisions`.`map_fk` = @mapid;';
            $dbHandler -> PrepareAndBind($query2);
            $mapItem = $dbHandler -> ExecuteAndFetch();

            $lastChangeDate = new DateTime($mapItem['rev_upload_date']);

            $content = '<div class="row">' . PHP_EOL .
                       '    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-6 col-xs-offset-0 col-sm-offset-0 col-md-offset-2 col-lg-offset-3 toppad">' . PHP_EOL .
                       '        <div class="panel panel-default">' . PHP_EOL .
                       '            <div class="panel-heading">' . PHP_EOL .
                       '                <h4>' . $mapItem['map_name'] . '</h4>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '            <div class="col-sm-6">' . PHP_EOL .
                       '                <div class="rating-block">' . PHP_EOL .
                       '                    <h4>Average user rating</h4>' . PHP_EOL .
                       '                    <h2 id="ratingAvg" class="bold padding-bottom-7">' .
                       ($mapItem['avg_rating'] === null ? 'n/a' : $mapItem['avg_rating'] . '<small> / 5</small>') . '</h2>' . PHP_EOL .
                       '                    <div class="starrr" id="ratingStarrr" kp-map-id="' . $mapItem['map_pk'] .
                       '" kp-map-rating="' . ($mapItem['avg_rating'] === null ? '0' : $mapItem['avg_rating']) . '"></div><br />' . PHP_EOL .
                       '                    <div id="ratingResultSuccess" class="alert alert-success alert-dismissible spacersmall" role="alert" ' .
                       'style="display: none;">' . PHP_EOL .
                       '                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">' .
                       '                            <span aria-hidden="true">&times;</span>' .
                       '                        </button>' . PHP_EOL .
                       '                        <span class="message"></span>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                    <div id="ratingResultError" class="alert alert-danger alert-dismissible spacersmall" role="alert" style="display: none;">' . PHP_EOL .
                       '                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">' .
                       '                            <span aria-hidden="true">&times;</span>' .
                       '                        </button>' . PHP_EOL .
                       '                        <span class="message"></span>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '            <div class="col-sm-6">' . PHP_EOL .
                       '                <h4>Rating breakdown</h4>' . PHP_EOL .
                       '                <div class="pull-left">' . PHP_EOL .
                       '                    <div class="pull-left" style="width:35px; line-height:1;">' . PHP_EOL .
                       '                        <div style="height:9px; margin:5px 0;">5 <span class="glyphicon glyphicon-star"></span></div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                    <div class="pull-left" style="width:180px;">' . PHP_EOL .
                       '                        <div class="progress" style="height:9px; margin:8px 0;">' . PHP_EOL .
                       '                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="5" aria-valuemin="0"' .
                       ' aria-valuemax="5" style="width: 1000%">' . PHP_EOL .
                       '                                <span class="sr-only">80% Complete (danger)</span>' . PHP_EOL .
                       '                            </div>' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                    <div id="ratingFive" class="pull-right" style="margin-left:10px;">' . $mapItem['rating_five'] . '</div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="pull-left">' . PHP_EOL .
                       '                    <div class="pull-left" style="width:35px; line-height:1;">' . PHP_EOL .
                       '                        <div style="height:9px; margin:5px 0;">4 <span class="glyphicon glyphicon-star"></span></div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                    <div class="pull-left" style="width:180px;">' . PHP_EOL .
                       '                        <div class="progress" style="height:9px; margin:8px 0;">' . PHP_EOL .
                       '                            <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="4" aria-valuemin="0"' .
                       ' aria-valuemax="5" style="width: 80%">' . PHP_EOL .
                       '                                <span class="sr-only">80% Complete (danger)</span>' . PHP_EOL .
                       '                            </div>' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                    <div id="ratingFour" class="pull-right" style="margin-left:10px;">' . $mapItem['rating_four'] . '</div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="pull-left">' . PHP_EOL .
                       '                    <div class="pull-left" style="width:35px; line-height:1;">' . PHP_EOL .
                       '                        <div style="height:9px; margin:5px 0;">3 <span class="glyphicon glyphicon-star"></span></div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                    <div class="pull-left" style="width:180px;">' . PHP_EOL .
                       '                        <div class="progress" style="height:9px; margin:8px 0;">' . PHP_EOL .
                       '                            <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="3" aria-valuemin="0"' .
                       ' aria-valuemax="5" style="width: 60%">' . PHP_EOL .
                       '                                <span class="sr-only">80% Complete (danger)</span>' . PHP_EOL .
                       '                            </div>' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                    <div id="ratingThree" class="pull-right" style="margin-left:10px;">' . $mapItem['rating_three'] . '</div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="pull-left">' . PHP_EOL .
                       '                    <div class="pull-left" style="width:35px; line-height:1;">' . PHP_EOL .
                       '                        <div style="height:9px; margin:5px 0;">2 <span class="glyphicon glyphicon-star"></span></div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                    <div class="pull-left" style="width:180px;">' . PHP_EOL .
                       '                        <div class="progress" style="height:9px; margin:8px 0;">' . PHP_EOL .
                       '                            <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="2" aria-valuemin="0"' .
                       ' aria-valuemax="5" style="width: 40%">' . PHP_EOL .
                       '                                <span class="sr-only">80% Complete (danger)</span>' . PHP_EOL .
                       '                            </div>' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                    <div id="ratingTwo" class="pull-right" style="margin-left:10px;">' . $mapItem['rating_two'] . '</div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="pull-left">' . PHP_EOL .
                       '                    <div class="pull-left" style="width:35px; line-height:1;">' . PHP_EOL .
                       '                        <div style="height:9px; margin:5px 0;">1 <span class="glyphicon glyphicon-star"></span></div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                    <div class="pull-left" style="width:180px;">' . PHP_EOL .
                       '                        <div class="progress" style="height:9px; margin:8px 0;">' . PHP_EOL .
                       '                            <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="1" aria-valuemin="0"' .
                       ' aria-valuemax="5" style="width: 20%">' . PHP_EOL .
                       '                                <span class="sr-only">80% Complete (danger)</span>' . PHP_EOL .
                       '                            </div>' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                    <div id="ratingOne" class="pull-right" style="margin-left:10px;">' . $mapItem['rating_one'] . '</div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '            <table class="table table-user-information">' . PHP_EOL .
                       '                <tbody>' . PHP_EOL .
                       '                    <tr>' . PHP_EOL .
                       '                        <td class="col-sm-3"><b>Author</b></td>' . PHP_EOL .
                       '                        <td class="col-sm-9">' . $mapItem['user_name'] . '</td>' . PHP_EOL .
                       '                    </tr>' . PHP_EOL .
                       '                    <tr>' . PHP_EOL .
                       '                        <td class="col-sm-3"><b>Downloads</b></td>' . PHP_EOL .
                       '                        <td class="col-sm-9">' . $mapItem['map_downloads'] . '</td>' . PHP_EOL .
                       '                    </tr>' . PHP_EOL .
                       '                    <tr>' . PHP_EOL .
                       '                        <td class="col-sm-3"><b>Version</b></td>' . PHP_EOL .
                       '                        <td class="col-sm-9">' . $mapItem['rev_map_version'] . '</td>' . PHP_EOL .
                       '                    </tr>' . PHP_EOL .
                       '                    <tr>' . PHP_EOL .
                       '                        <td class="col-sm-3"><b>Last change date</b></td>' . PHP_EOL .
                       '                        <td class="col-sm-9">' . $lastChangeDate -> format('Y-m-d H:i') . '</td>' . PHP_EOL .
                       '                    </tr>' . PHP_EOL .
                       '                    <tr>' . PHP_EOL .
                       '                        <td class="col-sm-3"><b>Description</b></td>' . PHP_EOL .
                       '                        <td class="col-sm-9">' . nl2br($mapItem['rev_map_description']) . '</td>' . PHP_EOL .
                       '                    </tr>' . PHP_EOL .
                       '                    <tr>' . PHP_EOL .
                       '                        <td class="col-lg-12" colspan="2">' . PHP_EOL .
                       '                            <button class="btn btn-success" id="btnDownloadMap" type="submit" title="Download this map" kp-map-id="' .
                       $mapItem['rev_pk'] . '">' . PHP_EOL .
                       '                                <span class="glyphicon glyphicon-download-alt"></span>&nbsp;&nbsp;Download' . PHP_EOL .
                       '                            </button>' . PHP_EOL .
                       '                            <button class="btn btn-danger pull-right" id="btnFlagMap" type="submit" title="Flag this map" kp-map-id="' .
                       $mapItem['rev_pk'] . '">' . PHP_EOL .
                       '                                <span class="glyphicon glyphicon-flag"></span>' . PHP_EOL .
                       '                            </button>' . PHP_EOL .
                       '                        </td>' . PHP_EOL .
                       '                    </tr>' . PHP_EOL .
                       '                </tbody>' . PHP_EOL .
                       '            </table>' . PHP_EOL .
                       '        </div>' . PHP_EOL .
                       '    </div>' . PHP_EOL .
                       '</div>' . PHP_EOL .
                       '<div class="row spacer">' . PHP_EOL .
                       '    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 col-xs-offset-0 col-sm-offset-0 col-md-offset-2 col-lg-offset-2" ' .
                       'style="margin-bottom: 25px;">' . PHP_EOL .
                       '        <div id="screenshot_carousel" class="carousel slide" data-ride="carousel">' . PHP_EOL .
                       '            <!-- Indicators -->' . PHP_EOL .
                       '            <ol class="carousel-indicators">' . PHP_EOL .
                       '                <li data-target="#screenshot_carousel" data-slide-to="0" class="active"></li>' . PHP_EOL .
                       '                <li data-target="#screenshot_carousel" data-slide-to="1"></li>' . PHP_EOL .
                       '            </ol>' . PHP_EOL .
                       '            <!-- Wrapper for slides -->' . PHP_EOL .
                       '            <center>' . PHP_EOL .
                       '                <div class="carousel-inner" role="listbox">' . PHP_EOL .
                       '                    <div class="item active">' . PHP_EOL .
                       '                        <img src="/uploads/images/kp_2016-08-30_21-29-44.png" alt="Knights Province Image 1">' . PHP_EOL .
                       '                        <div class="carousel-caption">' . PHP_EOL .
                       '                            A first look at combat' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                    <div class="item">' . PHP_EOL .
                       '                        <img src="/uploads/images/kp_2016-09-03_18-34-31.png" alt="Knights Province Image 2">' . PHP_EOL .
                       '                        <div class="carousel-caption">' . PHP_EOL .
                       '                            A basic village' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '            </center>' . PHP_EOL .
                       '            <!-- Controls -->' . PHP_EOL .
                       '            <a class="left carousel-control" href="#screenshot_carousel" role="button" data-slide="prev">' . PHP_EOL .
                       '                <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>' . PHP_EOL .
                       '                <span class="sr-only">Previous</span>' . PHP_EOL .
                       '            </a>' . PHP_EOL .
                       '            <a class="right carousel-control" href="#screenshot_carousel" role="button" data-slide="next">' . PHP_EOL .
                       '                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>' . PHP_EOL .
                       '                <span class="sr-only">Next</span>' . PHP_EOL .
                       '            </a>' . PHP_EOL .
                       '        </div>' . PHP_EOL .
                       '    </div>' . PHP_EOL .
                       '</div>';

            return $content;
        }

        public function getMapDetails(&$dbHandler) {
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

                            $content[] = $contentItem;
                        };

                        $this -> utils -> http_response_code(200);
                    };
                } elseif (isset($request['call_parts'][3])) {
                    $mapItem = null;
                    $mapId   = IntVal($request['call_parts'][3]);

                    if ($mapId === null || $mapId <= 0)
                        throw new Exception('Ilegal map ID : ' . $mapId);

                    $query1 = 'SET @mapid = :mapid;';
                    $dbHandler -> PrepareAndBind($query1, Array('mapid' => $mapId));
                    $dbHandler -> Execute();

                    $query2 = 'SELECT ' .
                              '    `Maps`.`map_name`, ' .
                              '    `Maps`.`map_downloads`, ' .
                              '    `Revisions`.`rev_map_description_short`, ' .
                              '    `Revisions`.`rev_map_description`, ' .
                              '    `Revisions`.`rev_upload_date`, ' .
                              '    `Users`.`user_name`, ' .
                              '    `MapTypes`.`map_type_name`, ' .
                              '    (SELECT ' .
                              '         ROUND(AVG(CAST(`rating_amount` AS DECIMAL(12,2))), 1) ' .
                              '     FROM ' .
                              '         `Ratings` ' .
                              '     WHERE ' .
                              '         `map_fk` = @mapid ' .
                              '    ) AS avg_rating ' .
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
                              '    `Maps`.`map_visible` = 1 AND ' .
                              '    `Maps`.`map_pk` = @mapid;';
                    $dbHandler -> PrepareAndBind($query2);
                    $mapItem = $dbHandler -> ExecuteAndFetch();

                    if ($mapItem != null) {
                        $this -> utils -> http_response_code(200);
                        $lastChangeDate = new DateTime($mapItem['rev_upload_date']);
                        $content['map_pk']                    = $mapId;
                        $content['map_name']                  = $mapItem['map_name'];
                        $content['map_downloads']             = IntVal($mapItem['map_downloads']);
                        $content['rev_map_description_short'] = $mapItem['rev_map_description_short'];
                        $content['rev_map_description']       = $mapItem['rev_map_description'];
                        $content['rev_upload_date']           = $lastChangeDate -> format('Y-m-d H:i');
                        $content['user_name']                 = $mapItem['user_name'];
                        $content['map_type_name']             = $mapItem['map_type_name'];
                        $content['avg_rating']                = ($mapItem['avg_rating'] === null ? 'n/a' : FloatVal($mapItem['avg_rating']));
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
                             '    `Revisions`.`rev_status_fk` = 1 AND ' .
                             '    `Maps`.`map_visible` = 1 ' .
                             'ORDER BY ' .
                             '    `Maps`.`map_name` DESC;';
                    $dbHandler -> PrepareAndBind($query);
                    $mapListItems = $dbHandler -> ExecuteAndFetchAll();
                    $dbHandler -> Clean();

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

                            $content[] = $contentItem;
                        };

                        $this -> utils -> http_response_code(200);
                    };
                };
            } catch (Exception $e) {
                $this -> utils -> http_response_code(404);
                $content['status']  = 'Error';
                $content['message'] = 'Error retrieving data from the database.';
            };

            return $content;
        }
    }
