<?php
    namespace Functions;

    class FileDetails
    {
        public function __construct() {
        }

        public function getContent(&$dbHandler) {
            global $request;

            $mapItem = null;

            $query1 = 'SET @fileid = :fileid;';
            $dbHandler -> PrepareAndBind ($query1, Array('fileid' => IntVal($request['query_vars']['file'])));
            $dbHandler -> Execute();

            $query2 = 'SELECT ' .
                      '    `Files`.`file_pk`, ' .
                      '    `Files`.`file_name`, ' .
                      '    `Files`.`file_downloads`, ' .
                      '    `Revisions`.`rev_pk`, ' .
                      '    `Revisions`.`rev_file_name`, ' .
                      '    `Revisions`.`rev_file_path`, ' .
                      '    `Revisions`.`rev_file_version`, ' .
                      '    `Revisions`.`rev_file_description`, ' .
                      '    `Revisions`.`rev_upload_date`, ' .
                      '    `Users`.`user_name`, ' .
                      '    ROUND(AVG(CAST(`Ratings`.`rating_amount` AS DECIMAL(12,2))), 1) AS avg_rating, ' .
                      '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 1 AND file_fk = @fileid), 0) AS rating_one, ' .
                      '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 2 AND file_fk = @fileid), 0) AS rating_two, ' .
                      '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 3 AND file_fk = @fileid), 0) AS rating_three, ' .
                      '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 4 AND file_fk = @fileid), 0) AS rating_four, ' .
                      '    IFNULL((SELECT COUNT(*) FROM `Ratings` WHERE `rating_amount` = 5 AND file_fk = @fileid), 0) AS rating_five ' .
                      'FROM ' .
                      '    `Users` ' .
                      'LEFT JOIN ' .
                      '    `Files` ON `Users`.`user_pk` = `Files`.`user_fk` ' .
                      'LEFT JOIN ' .
                      '    `Revisions` ON `Files`.`file_pk` = `Revisions`.`file_fk` ' .
                      'LEFT JOIN ' .
                      '    `Ratings` ON `Files`.`file_pk` = `Ratings`.`file_fk` ' .
                      'WHERE ' .
                      '    `Revisions`.`rev_status_fk` = 1 AND ' .
                      '    `Revisions`.`file_fk` = @fileid;';
            $dbHandler -> PrepareAndBind ($query2);
            $mapItem = $dbHandler -> ExecuteAndFetch();

            $content = '<div class="col-xs-12 col-sm-12 col-md-10 col-lg-6 col-xs-offset-0 col-sm-offset-0 col-md-offset-1 col-lg-offset-3 toppad">' . PHP_EOL .
                       '    <div class="panel panel-default">' . PHP_EOL .
                       '        <div class="panel-heading">' . PHP_EOL .
                       '            <h4>' . $mapItem['file_name'] . '</h4>' . PHP_EOL .
                       '        </div>' . PHP_EOL .
                       '        <div class="col-sm-6">' . PHP_EOL .
                       '            <div class="rating-block">' . PHP_EOL .
                       '                <h4>Average user rating</h4>' . PHP_EOL .
                       '                <h2 class="bold padding-bottom-7">' . ($mapItem['avg_rating'] === null ? 'n/a' : $mapItem['avg_rating'] . '<small> / 5</small>') . '</h2>' . PHP_EOL .
                       '                <button type="submit" class="btn ' . ($mapItem['avg_rating'] >= 1 ? 'btn-warning' : 'btn-default btn-grey') . ' btn-sm" aria-label="Left Align"' .
                       ' onclick="window.open(\'/ratefile?file=' . $mapItem['file_pk'] .
                       '&score=1\', \'popUpWindow\', \'height=400, width=600, left=10, top=10, , scrollbars=yes, menubar=no\'); return false;">' . PHP_EOL .
                       '                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span>' . PHP_EOL .
                       '                </button>' . PHP_EOL .
                       '                <button type="submit" class="btn ' . ($mapItem['avg_rating'] >= 2 ? 'btn-warning' : 'btn-default btn-grey') . ' btn-sm" aria-label="Left Align"' .
                       ' onclick="window.open(\'/ratefile?file=' . $mapItem['file_pk'] .
                       '&score=2\', \'popUpWindow\', \'height=400, width=600, left=10, top=10, , scrollbars=yes, menubar=no\'); return false;">' . PHP_EOL .
                       '                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span>' . PHP_EOL .
                       '                </button>' . PHP_EOL .
                       '                <button type="submit" class="btn ' . ($mapItem['avg_rating'] >= 3 ? 'btn-warning' : 'btn-default btn-grey') . ' btn-sm" aria-label="Left Align"' .
                       ' onclick="window.open(\'/ratefile?file=' . $mapItem['file_pk'] .
                       '&score=3\', \'popUpWindow\', \'height=400, width=600, left=10, top=10, , scrollbars=yes, menubar=no\'); return false;">' . PHP_EOL .
                       '                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span>' . PHP_EOL .
                       '                </button>' . PHP_EOL .
                       '                <button type="submit" class="btn ' . ($mapItem['avg_rating'] >= 4 ? 'btn-warning' : 'btn-default btn-grey') . ' btn-sm" aria-label="Left Align"' .
                       ' onclick="window.open(\'/ratefile?file=' . $mapItem['file_pk'] .
                       '&score=4\', \'popUpWindow\', \'height=400, width=600, left=10, top=10, , scrollbars=yes, menubar=no\'); return false;">' . PHP_EOL .
                       '                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span>' . PHP_EOL .
                       '                </button>' . PHP_EOL .
                       '                <button type="submit" class="btn ' . ($mapItem['avg_rating'] >= 5 ? 'btn-warning' : 'btn-default btn-grey') . ' btn-sm" aria-label="Left Align"' .
                       ' onclick="window.open(\'/ratefile?file=' . $mapItem['file_pk'] .
                       '&score=5\', \'popUpWindow\', \'height=400, width=600, left=10, top=10, , scrollbars=yes, menubar=no\'); return false;">' . PHP_EOL .
                       '                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span>' . PHP_EOL .
                       '                </button>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '        </div>' . PHP_EOL .
                       '        <div class="col-sm-6">' . PHP_EOL .
                       '            <h4>Rating breakdown</h4>' . PHP_EOL .
                       '            <div class="pull-left">' . PHP_EOL .
                       '                <div class="pull-left" style="width:35px; line-height:1;">' . PHP_EOL .
                       '                    <div style="height:9px; margin:5px 0;">5 <span class="glyphicon glyphicon-star"></span></div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="pull-left" style="width:180px;">' . PHP_EOL .
                       '                    <div class="progress" style="height:9px; margin:8px 0;">' . PHP_EOL .
                       '                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="5" aria-valuemin="0" aria-valuemax="5" style="width: 1000%">' . PHP_EOL .
                       '                            <span class="sr-only">80% Complete (danger)</span>' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="pull-right" style="margin-left:10px;">' . $mapItem['rating_five'] . '</div>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '            <div class="pull-left">' . PHP_EOL .
                       '                <div class="pull-left" style="width:35px; line-height:1;">' . PHP_EOL .
                       '                    <div style="height:9px; margin:5px 0;">4 <span class="glyphicon glyphicon-star"></span></div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="pull-left" style="width:180px;">' . PHP_EOL .
                       '                    <div class="progress" style="height:9px; margin:8px 0;">' . PHP_EOL .
                       '                        <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="4" aria-valuemin="0" aria-valuemax="5" style="width: 80%">' . PHP_EOL .
                       '                            <span class="sr-only">80% Complete (danger)</span>' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="pull-right" style="margin-left:10px;">' . $mapItem['rating_four'] . '</div>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '            <div class="pull-left">' . PHP_EOL .
                       '                <div class="pull-left" style="width:35px; line-height:1;">' . PHP_EOL .
                       '                    <div style="height:9px; margin:5px 0;">3 <span class="glyphicon glyphicon-star"></span></div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="pull-left" style="width:180px;">' . PHP_EOL .
                       '                    <div class="progress" style="height:9px; margin:8px 0;">' . PHP_EOL .
                       '                        <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="3" aria-valuemin="0" aria-valuemax="5" style="width: 60%">' . PHP_EOL .
                       '                            <span class="sr-only">80% Complete (danger)</span>' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="pull-right" style="margin-left:10px;">' . $mapItem['rating_three'] . '</div>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '            <div class="pull-left">' . PHP_EOL .
                       '                <div class="pull-left" style="width:35px; line-height:1;">' . PHP_EOL .
                       '                    <div style="height:9px; margin:5px 0;">2 <span class="glyphicon glyphicon-star"></span></div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="pull-left" style="width:180px;">' . PHP_EOL .
                       '                    <div class="progress" style="height:9px; margin:8px 0;">' . PHP_EOL .
                       '                        <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="2" aria-valuemin="0" aria-valuemax="5" style="width: 40%">' . PHP_EOL .
                       '                            <span class="sr-only">80% Complete (danger)</span>' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="pull-right" style="margin-left:10px;">' . $mapItem['rating_two'] . '</div>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '            <div class="pull-left">' . PHP_EOL .
                       '                <div class="pull-left" style="width:35px; line-height:1;">' . PHP_EOL .
                       '                    <div style="height:9px; margin:5px 0;">1 <span class="glyphicon glyphicon-star"></span></div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="pull-left" style="width:180px;">' . PHP_EOL .
                       '                    <div class="progress" style="height:9px; margin:8px 0;">' . PHP_EOL .
                       '                        <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="5" style="width: 20%">' . PHP_EOL .
                       '                            <span class="sr-only">80% Complete (danger)</span>' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="pull-right" style="margin-left:10px;">' . $mapItem['rating_one'] . '</div>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '        </div>' . PHP_EOL .
                       '        <table class="table table-user-information">' . PHP_EOL .
                       '            <tbody>' . PHP_EOL .
                       '                <tr>' . PHP_EOL .
                       '                    <td class="col-sm-3"><b>Author</b></td>' . PHP_EOL .
                       '                    <td class="col-sm-9">' . $mapItem['user_name'] . '</td>' . PHP_EOL .
                       '                </tr>' . PHP_EOL .
                       '                <tr>' . PHP_EOL .
                       '                    <td class="col-sm-3"><b>Downloads</b></td>' . PHP_EOL .
                       '                    <td class="col-sm-9">' . $mapItem['file_downloads'] . '</td>' . PHP_EOL .
                       '                </tr>' . PHP_EOL .
                       '                <tr>' . PHP_EOL .
                       '                    <td class="col-sm-3"><b>Version</b></td>' . PHP_EOL .
                       '                    <td class="col-sm-9">' . $mapItem['rev_file_version'] . '</td>' . PHP_EOL .
                       '                </tr>' . PHP_EOL .
                       '                <tr>' . PHP_EOL .
                       '                    <td class="col-sm-3"><b>Last change date</b></td>' . PHP_EOL .
                       '                    <td class="col-sm-9">' . $mapItem['rev_upload_date'] . '</td>' . PHP_EOL .
                       '                </tr>' . PHP_EOL .
                       '                <tr>' . PHP_EOL .
                       '                    <td class="col-sm-3"><b>Description</b></td>' . PHP_EOL .
                       '                    <td class="col-sm-9">' . nl2br($mapItem['rev_file_description']) . '</td>' . PHP_EOL .
                       '                </tr>' . PHP_EOL .
                       '                <tr>' . PHP_EOL .
                       '                    <td colspan="2">' . PHP_EOL .
                       '                        <button type="submit" title="Download this map" onclick="window.open(\'/download?file=' . $mapItem['rev_pk'] . '\', \'popUpWindow\', \'height=400, width=600, left=10, top=10, , scrollbars=yes, menubar=no\'); return false;">' . PHP_EOL .
                       '                            <span class="glyphicon glyphicon-download-alt"></span>' . PHP_EOL .
                       '                        </button>' . PHP_EOL .
                       '                        <button title="Flag this map">' . PHP_EOL .
                       '                            <span class="glyphicon glyphicon-flag"></span>' . PHP_EOL .
                       '                        </button>' . PHP_EOL .
                       '                    </td>' . PHP_EOL .
                       '                </tr>' . PHP_EOL .
                       '            </tbody>' . PHP_EOL .
                       '        </table>' . PHP_EOL .
                       '    </div>' . PHP_EOL .
                       '</div>' . PHP_EOL .
                       '<div class="row">' . PHP_EOL .
                       '<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xs-offset-0 col-sm-offset-0 col-md-offset-3 col-lg-offset-3" style="margin-bottom: 25px;">' . PHP_EOL .
                       '    <div id="screenshot_carousel" class="carousel slide" data-ride="carousel">' . PHP_EOL .
                       '        <!-- Indicators -->' . PHP_EOL .
                       '        <ol class="carousel-indicators">' . PHP_EOL .
                       '            <li data-target="#screenshot_carousel" data-slide-to="0" class="active"></li>' . PHP_EOL .
                       '            <li data-target="#screenshot_carousel" data-slide-to="1"></li>' . PHP_EOL .
                       '        </ol>' . PHP_EOL .
                       '        <!-- Wrapper for slides -->' . PHP_EOL .
                       '        <center>' . PHP_EOL .
                       '            <div class="carousel-inner" role="listbox">' . PHP_EOL .
                       '                <div class="item active">' . PHP_EOL .
                       '                    <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9InllcyI/PjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB3aWR0aD0iOTAwIiBoZWlnaHQ9IjUwMCIgdmlld0JveD0iMCAwIDkwMCA1MDAiIHByZXNlcnZlQXNwZWN0UmF0aW89Im5vbmUiPjwhLS0KU291cmNlIFVSTDogaG9sZGVyLmpzLzkwMHg1MDAvYXV0by8jNzc3OiM1NTUvdGV4dDpGaXJzdCBzbGlkZQpDcmVhdGVkIHdpdGggSG9sZGVyLmpzIDIuNi4wLgpMZWFybiBtb3JlIGF0IGh0dHA6Ly9ob2xkZXJqcy5jb20KKGMpIDIwMTItMjAxNSBJdmFuIE1hbG9waW5za3kgLSBodHRwOi8vaW1za3kuY28KLS0+PGRlZnM+PHN0eWxlIHR5cGU9InRleHQvY3NzIj48IVtDREFUQVsjaG9sZGVyXzE1YWM0YTEzOWVhIHRleHQgeyBmaWxsOiM1NTU7Zm9udC13ZWlnaHQ6Ym9sZDtmb250LWZhbWlseTpBcmlhbCwgSGVsdmV0aWNhLCBPcGVuIFNhbnMsIHNhbnMtc2VyaWYsIG1vbm9zcGFjZTtmb250LXNpemU6NDVwdCB9IF1dPjwvc3R5bGU+PC9kZWZzPjxnIGlkPSJob2xkZXJfMTVhYzRhMTM5ZWEiPjxyZWN0IHdpZHRoPSI5MDAiIGhlaWdodD0iNTAwIiBmaWxsPSIjNzc3Ii8+PGc+PHRleHQgeD0iMzA4LjI5Njg3NSIgeT0iMjcwLjEiPkZpcnN0IHNsaWRlPC90ZXh0PjwvZz48L2c+PC9zdmc+" alt="First slide [900x500]">' . PHP_EOL .
                       '                    <div class="carousel-caption">' . PHP_EOL .
                       '                        First image' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="item">' . PHP_EOL .
                       '                    <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9InllcyI/PjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB3aWR0aD0iOTAwIiBoZWlnaHQ9IjUwMCIgdmlld0JveD0iMCAwIDkwMCA1MDAiIHByZXNlcnZlQXNwZWN0UmF0aW89Im5vbmUiPjwhLS0KU291cmNlIFVSTDogaG9sZGVyLmpzLzkwMHg1MDAvYXV0by8jNjY2OiM0NDQvdGV4dDpTZWNvbmQgc2xpZGUKQ3JlYXRlZCB3aXRoIEhvbGRlci5qcyAyLjYuMC4KTGVhcm4gbW9yZSBhdCBodHRwOi8vaG9sZGVyanMuY29tCihjKSAyMDEyLTIwMTUgSXZhbiBNYWxvcGluc2t5IC0gaHR0cDovL2ltc2t5LmNvCi0tPjxkZWZzPjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+PCFbQ0RBVEFbI2hvbGRlcl8xNWFjNGEwZGE5ZiB0ZXh0IHsgZmlsbDojNDQ0O2ZvbnQtd2VpZ2h0OmJvbGQ7Zm9udC1mYW1pbHk6QXJpYWwsIEhlbHZldGljYSwgT3BlbiBTYW5zLCBzYW5zLXNlcmlmLCBtb25vc3BhY2U7Zm9udC1zaXplOjQ1cHQgfSBdXT48L3N0eWxlPjwvZGVmcz48ZyBpZD0iaG9sZGVyXzE1YWM0YTBkYTlmIj48cmVjdCB3aWR0aD0iOTAwIiBoZWlnaHQ9IjUwMCIgZmlsbD0iIzY2NiIvPjxnPjx0ZXh0IHg9IjI2NC45NTMxMjUiIHk9IjI3MC4xIj5TZWNvbmQgc2xpZGU8L3RleHQ+PC9nPjwvZz48L3N2Zz4=" alt="Second slide [900x500]">' . PHP_EOL .
                       '                    <div class="carousel-caption">' . PHP_EOL .
                       '                        Second image' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '        </center>' . PHP_EOL .
                       '        <!-- Controls -->' . PHP_EOL .
                       '        <a class="left carousel-control" href="#screenshot_carousel" role="button" data-slide="prev">' . PHP_EOL .
                       '            <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>' . PHP_EOL .
                       '            <span class="sr-only">Previous</span>' . PHP_EOL .
                       '        </a>' . PHP_EOL .
                       '        <a class="right carousel-control" href="#screenshot_carousel" role="button" data-slide="next">' . PHP_EOL .
                       '            <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>' . PHP_EOL .
                       '            <span class="sr-only">Next</span>' . PHP_EOL .
                       '        </a>' . PHP_EOL .
                       '    </div>' . PHP_EOL .
                       '</div>';

            return $content;
        }
    }
