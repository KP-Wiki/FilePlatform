<?php
    namespace Functions\Views;

    class MapDetails
    {
        private $utils = null;

        public function __construct(&$utilsClass) {
            $this -> utils = $utilsClass;
        }

        public function getContent(&$dbHandler) {
            global $request;

            $mapItem     = null;
            $mapId       = IntVal($request['call_parts'][1]);
            $mapInfoFunc = new \Functions\MapInfo($this -> utils);
            $mapItem     = $mapInfoFunc -> getMapDetails($dbHandler, $mapId);

            $screenshotQuery = 'SELECT ' .
                               '    `screen_title`, ' .
                               '    `screen_alt`, ' .
                               '    `screen_file_name`, ' .
                               '    `screen_path`, ' .
                               '    `screen_order` ' .
                               'FROM ' .
                               '    `Screenshots` ' .
                               'WHERE ' .
                               '    `rev_fk` = :revid;';
            $dbHandler -> PrepareAndBind($screenshotQuery, Array('revid' => $mapItem['data']['rev_pk']));
            $screenshotItems = $dbHandler -> ExecuteAndFetchAll();
            $dbHandler -> Clean();

            if (is_array($screenshotItems) && count($screenshotItems) > 0) {
                $firstItem = True;

                foreach ($screenshotItems as $screenshotItem) {
                    if ($firstItem) {
                        $carouselIndicators = '                <li data-target="#screenshot_carousel" data-slide-to="' . $screenshotItem['screen_order'] .
                                              '" class="active"></li>' . PHP_EOL;
                        $carouselItems      = '                    <div class="item active">' . PHP_EOL .
                                              '                        <img src="' . $screenshotItem['screen_path'] . $screenshotItem['screen_file_name'] .
                                              '" alt="' . $screenshotItem['screen_alt'] . '">' . PHP_EOL .
                                              '                        <div class="carousel-caption">' . PHP_EOL .
                                              '                            ' . $screenshotItem['screen_title'] . PHP_EOL .
                                              '                        </div>' . PHP_EOL .
                                              '                    </div>' . PHP_EOL;
                        $firstItem          = False;
                    } else {
                        $carouselIndicators .= '                <li data-target="#screenshot_carousel" data-slide-to="' . $screenshotItem['screen_order'] .
                                               '"></li>' . PHP_EOL;
                        $carouselItems      .= '                    <div class="item">' . PHP_EOL .
                                               '                        <img src="' . $screenshotItem['screen_path'] . $screenshotItem['screen_file_name'] .
                                               '" alt="' . $screenshotItem['screen_alt'] . '">' . PHP_EOL .
                                               '                        <div class="carousel-caption">' . PHP_EOL .
                                               '                            ' . $screenshotItem['screen_title'] . PHP_EOL .
                                               '                        </div>' . PHP_EOL .
                                               '                    </div>' . PHP_EOL;
                    };
                };
            } else {
                $carouselIndicators = '                <li data-target="#screenshot_carousel" data-slide-to="0" class="active"></li>' . PHP_EOL .
                                      '                <li data-target="#screenshot_carousel" data-slide-to="1"></li>' . PHP_EOL;
                $carouselItems      = '                    <div class="item active">' . PHP_EOL .
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
                                      '                    </div>' . PHP_EOL;
            };

            $content = '<div class="row">' . PHP_EOL .
                       '    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-6 col-xs-offset-0 col-sm-offset-0 col-md-offset-2 col-lg-offset-3 toppad">' . PHP_EOL .
                       '        <div class="panel panel-default">' . PHP_EOL .
                       '            <div class="panel-heading">' . PHP_EOL .
                       '                <h4>' . $mapItem['data']['map_name'] . '</h4>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '            <div class="col-sm-6">' . PHP_EOL .
                       '                <div class="rating-block">' . PHP_EOL .
                       '                    <h4>Average user rating</h4>' . PHP_EOL .
                       '                    <h2 id="ratingAvg" class="bold padding-bottom-7">' .
                       ($mapItem['data']['avg_rating'] === 0 ? 'n/a' : $mapItem['data']['avg_rating'] . '<small> / 5</small>') . '</h2>' . PHP_EOL .
                       '                    <div class="starrr" id="ratingStarrr" kp-map-id="' . $mapId .
                       '" kp-map-rating="' . $mapItem['data']['avg_rating'] . '"></div><br />' . PHP_EOL .
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
                       '                <h4>Breakdown</h4>' . PHP_EOL .
                       '                <div class="row" style="margin-left:2px;">' . PHP_EOL .
                       '                    5 <span class="glyphicon glyphicon-star"></span>' . PHP_EOL .
                       '                    <span style="margin-left:10px;">' . $mapItem['data']['rating_five'] . ' Vote</span>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="row" style="margin-left:2px;">' . PHP_EOL .
                       '                    4 <span class="glyphicon glyphicon-star"></span>' . PHP_EOL .
                       '                    <span style="margin-left:10px;">' . $mapItem['data']['rating_four'] . ' Votes</span>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="row" style="margin-left:2px;">' . PHP_EOL .
                       '                    3 <span class="glyphicon glyphicon-star"></span>' . PHP_EOL .
                       '                    <span style="margin-left:10px;">' . $mapItem['data']['rating_three'] . ' Votes</span>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="row" style="margin-left:2px;">' . PHP_EOL .
                       '                    2 <span class="glyphicon glyphicon-star"></span>' . PHP_EOL .
                       '                    <span style="margin-left:10px;">' . $mapItem['data']['rating_two'] . ' Votes</span>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="row" style="margin-left:2px;">' . PHP_EOL .
                       '                    1 <span class="glyphicon glyphicon-star"></span>' . PHP_EOL .
                       '                    <span style="margin-left:10px;">' . $mapItem['data']['rating_one'] . ' Votes</span>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '            <table class="table table-user-information">' . PHP_EOL .
                       '                <tbody>' . PHP_EOL .
                       '                    <tr>' . PHP_EOL .
                       '                        <td class="col-sm-3"><b>Author</b></td>' . PHP_EOL .
                       '                        <td class="col-sm-9">' . PHP_EOL .
                       '                            <a href="/profile/' . $mapItem['data']['user_pk'] . '">' . $mapItem['data']['user_name'] . '</a>' . PHP_EOL .
                       '                        </td>' . PHP_EOL .
                       '                    </tr>' . PHP_EOL .
                       '                    <tr>' . PHP_EOL .
                       '                        <td class="col-sm-3"><b>Downloads</b></td>' . PHP_EOL .
                       '                        <td class="col-sm-9">' . $mapItem['data']['map_downloads'] . '</td>' . PHP_EOL .
                       '                    </tr>' . PHP_EOL .
                       '                    <tr>' . PHP_EOL .
                       '                        <td class="col-sm-3"><b>Version</b></td>' . PHP_EOL .
                       '                        <td class="col-sm-9">' . $mapItem['data']['rev_map_version'] . '</td>' . PHP_EOL .
                       '                    </tr>' . PHP_EOL .
                       '                    <tr>' . PHP_EOL .
                       '                        <td class="col-sm-3"><b>Last change date</b></td>' . PHP_EOL .
                       '                        <td class="col-sm-9">' . $mapItem['data']['rev_upload_date'] . '</td>' . PHP_EOL .
                       '                    </tr>' . PHP_EOL .
                       '                    <tr>' . PHP_EOL .
                       '                        <td class="col-sm-3"><b>Description</b></td>' . PHP_EOL .
                       '                        <td class="col-sm-9">' . nl2br($mapItem['data']['rev_map_description']) . '</td>' . PHP_EOL .
                       '                    </tr>' . PHP_EOL .
                       '                    <tr>' . PHP_EOL .
                       '                        <td class="col-lg-12" colspan="2">' . PHP_EOL .
                       '                            <button class="btn btn-success" id="btnDownloadMap" type="submit" title="Download this map" kp-map-id="' .
                       $mapItem['data']['rev_pk'] . '">' . PHP_EOL .
                       '                                <span class="glyphicon glyphicon-download-alt"></span>&nbsp;&nbsp;Download' . PHP_EOL .
                       '                            </button>' . PHP_EOL .
                       '                            <button class="btn btn-danger pull-right" id="btnFlagMap" type="submit" title="Flag this map" kp-map-id="' .
                       $mapItem['data']['rev_pk'] . '">' . PHP_EOL .
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
                       '        <center>' . PHP_EOL .
                       '            <div id="screenshot_carousel" class="carousel slide" data-ride="carousel">' . PHP_EOL .
                       '                <!-- Indicators -->' . PHP_EOL .
                       '                <ol class="carousel-indicators">' . PHP_EOL .
                       $carouselIndicators .
                       '                </ol>' . PHP_EOL .
                       '                <!-- Wrapper for slides -->' . PHP_EOL .
                       '                <center>' . PHP_EOL .
                       '                    <div class="carousel-inner" role="listbox">' . PHP_EOL .
                       $carouselItems .
                       '                    </div>' . PHP_EOL .
                       '                </center>' . PHP_EOL .
                       '                <!-- Controls -->' . PHP_EOL .
                       '                <a class="left carousel-control" href="#screenshot_carousel" role="button" data-slide="prev">' . PHP_EOL .
                       '                    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>' . PHP_EOL .
                       '                    <span class="sr-only">Previous</span>' . PHP_EOL .
                       '                </a>' . PHP_EOL .
                       '                <a class="right carousel-control" href="#screenshot_carousel" role="button" data-slide="next">' . PHP_EOL .
                       '                    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>' . PHP_EOL .
                       '                    <span class="sr-only">Next</span>' . PHP_EOL .
                       '                </a>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '        </center>' . PHP_EOL .
                       '    </div>' . PHP_EOL .
                       '</div>';

            return $content;
        }
    }
