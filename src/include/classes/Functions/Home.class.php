<?php
    namespace Functions;

    class Home
    {
        public function __construct() {
        }

        public function getContent(&$dbHandler) {
            $mapListItems = null;

            $query = 'SELECT ' .
                     '    `Files`.`file_name`, ' .
                     '    `Files`.`file_downloads`, ' .
                     '    `Revisions`.`rev_pk`, ' .
                     '    `Revisions`.`rev_file_name`, ' .
                     '    `Revisions`.`rev_file_path`, ' .
                     '    `Revisions`.`rev_file_version`, ' .
                     '    `Revisions`.`rev_upload_date`, ' .
                     '    `Users`.`user_name` ' .
                     'FROM ' .
                     '    `Users` ' .
                     'LEFT JOIN ' .
                     '    `Files` ON `Users`.`user_pk` = `Files`.`user_fk` ' .
                     'LEFT JOIN ' .
                     '    `Revisions` ON `Files`.`file_pk` = `Revisions`.`file_fk` ' .
                     'WHERE ' .
                     '    `Revisions`.`rev_status_fk` = 1 AND ' .
                     '    `Files`.`file_visible` = 1 ' .
                     'ORDER BY ' .
                     '    `Revisions`.`rev_upload_date` DESC;';
            $dbHandler -> PrepareAndBind ($query);
            $mapListItems = $dbHandler -> ExecuteAndFetchAll();

            $content = '                    <div class="panel panel-default">' . PHP_EOL .
                       '                        <div class="panel-heading">Knights Province Map database</div>' . PHP_EOL .
                       '                        <table class="table table-striped table-bordered table-hover" ' .
                       'data-toggle="table" ' .
                       'data-search="true" ' .
                       'data-sort-name="name" ' .
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
                       '                                    <th class="col-xs-2" data-field="type" data-sortable="true">Type</th>' . PHP_EOL .
                       '                                    <th class="col-xs-2" data-field="name" data-sortable="true">Name</th>' . PHP_EOL .
                       '                                    <th class="col-xs-2" data-field="author" data-sortable="true">Author</th>' . PHP_EOL .
                       '                                    <th class="col-xs-4" data-field="description" data-sortable="false">Description</th>' . PHP_EOL .
                       '                                    <th class="col-xs-1" data-field="rating" data-sortable="true">Rating</th>' . PHP_EOL .
                       '                                    <th class="col-xs-1" data-field="downloads" data-sortable="true">Downloads</th>' . PHP_EOL .
                       '                                </tr>' . PHP_EOL .
                       '                            </thead>' . PHP_EOL .
                       '                            <tbody>' . PHP_EOL;

            if ($mapListItems != null) {
                foreach ($mapListItems as $mapItem) {
                    $content .= '                                <tr>' . PHP_EOL .
                                '                                    <td>Type</td>' . PHP_EOL .
                                '                                    <td><a href="/filedetails?file=' . $mapItem['rev_pk'] . '">' . $mapItem['file_name'] . '</a></td>' . PHP_EOL .
                                '                                    <td>' . $mapItem['user_name'] . '</td>' . PHP_EOL .
                                '                                    <td>Short description</td>' . PHP_EOL .
                                '                                    <td>Rating</td>' . PHP_EOL .
                                '                                    <td style="text-align: right;">' . StrVal($mapItem['file_downloads']) . '</td>' . PHP_EOL .
                                '                                </tr>' . PHP_EOL;
                };
            };

            $content .= '                            </tbody>' . PHP_EOL .
                        '                        </table>' . PHP_EOL .
                        '                    </div>';

            return $content;
        }
    }
