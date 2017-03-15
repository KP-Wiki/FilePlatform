<?php
    namespace Functions;

    class Home
    {
        public function __construct() {
        }

        public function getContent(&$dbHandler) {
            $mapListItems = null;

            $content = '                    <div class="panel panel-default">' . PHP_EOL .
                       '                        <div class="panel-heading">Knights Province Map database</div>' . PHP_EOL .
                       '                        <table id="homeTable" class="table table-striped table-bordered table-hover" ' .
                       'data-toggle="table" ' .
                       'data-search="true" ' .
                       'data-sort-name="avg_rating" ' .
                       'data-sort-order="desc" ' .
                       'data-show-toggle="true" ' .
                       'data-show-columns="true" ' .
                       'data-minimum-count-columns="2" ' .
                       'data-show-pagination-switch="true" ' .
                       'data-pagination="true" ' .
                       'data-page-list="[10, 25, 50, 100, ALL]" ' .
                       'data-show-footer="false" ' .
                       'data-side-pagination="client">' . PHP_EOL .
                       '                            <thead>' . PHP_EOL .
                       '                                <tr>' . PHP_EOL .
                       '                                    <th class="col-xs-2" data-field="file_type_name" data-sortable="true">Type</th>' . PHP_EOL .
                       '                                    <th class="col-xs-2" data-field="file_name" data-formatter="detailUrlFormatter" data-sortable="true">Name</th>' . PHP_EOL .
                       '                                    <th class="col-xs-2" data-field="user_name" data-sortable="true">Author</th>' . PHP_EOL .
                       '                                    <th class="col-xs-4" data-field="rev_file_description_short" data-sortable="false">Description</th>' . PHP_EOL .
                       '                                    <th class="col-xs-1" data-field="avg_rating" data-sortable="true">Rating</th>' . PHP_EOL .
                       '                                    <th class="col-xs-1" data-field="file_downloads" data-sortable="true">Downloads</th>' . PHP_EOL .
                       '                                </tr>' . PHP_EOL .
                       '                            </thead>' . PHP_EOL .
                       '                        </table>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                    <script>' . PHP_EOL .
                       '                        $(function(){' . PHP_EOL .
                       '                            fillTable(\'Home\', \'#homeTable\');' . PHP_EOL .
                       '                        });' . PHP_EOL .
                       '                    </script>' . PHP_EOL;

            return $content;
        }

        public function getApiResponse(&$dbHandler) {
            header('Cache-Control: no-cache, must-revalidate');
            header('Content-type: application/json');

            try {
                $mapListItems = null;
                $content      = Array();

                $query = 'SELECT ' .
                         '    `Files`.`file_pk`, ' .
                         '    `Files`.`file_name`, ' .
                         '    `Files`.`file_downloads`, ' .
                         '    `Revisions`.`rev_file_description_short`, ' .
                         '    `Users`.`user_name`, ' .
                         '    `FileTypes`.`file_type_name` ' .
                         'FROM ' .
                         '    `Files` ' .
                         'LEFT JOIN ' .
                         '    `Revisions` ON `Files`.`file_pk` = `Revisions`.`file_fk` ' .
                         'LEFT JOIN ' .
                         '    `Users` ON `Files`.`user_fk` = `Users`.`user_pk` ' .
                         'LEFT JOIN ' .
                         '    `FileTypes` ON `Files`.`file_type_fk` = `FileTypes`.`file_type_pk` ' .
                         'WHERE ' .
                         '    `Revisions`.`rev_status_fk` = 1 AND ' .
                         '    `Files`.`file_visible` = 1 ' .
                         'ORDER BY ' .
                         '    `Files`.`file_name` DESC;';
                $dbHandler -> PrepareAndBind ($query);
                $mapListItems = $dbHandler -> ExecuteAndFetchAll();
                $dbHandler -> Clean();

                if ($mapListItems != null) {
                    foreach ($mapListItems as $mapItem) {
                        $ratingQuery = 'SELECT ' .
                                       '    ROUND(AVG(CAST(`rating_amount` AS DECIMAL(12,2))), 1) AS avg_rating ' .
                                       'FROM ' .
                                       '    `Ratings` ' .
                                       'WHERE ' .
                                       '    `file_fk` = :fileid;';
                        $dbHandler -> PrepareAndBind ($ratingQuery, Array('fileid' => $mapItem['file_pk']));
                        $avgRating = $dbHandler -> ExecuteAndFetch();

                        $contentItem = Array('file_pk'                    => IntVal($mapItem['file_pk']),
                                             'file_name'                  => $mapItem['file_name'],
                                             'file_downloads'             => IntVal($mapItem['file_downloads']),
                                             'rev_file_description_short' => $mapItem['rev_file_description_short'],
                                             'user_name'                  => $mapItem['user_name'],
                                             'file_type_name'                  => $mapItem['file_type_name'],
                                             'avg_rating'                 => ($avgRating['avg_rating'] === null ? 'n/a' : FloatVal($avgRating['avg_rating'])));

                        $content[] = $contentItem;
                    };
                };
            } catch (Exception $e) {
                $content = 'Error retrieving data from the database.';
            };

            return $content;
        }
    }
