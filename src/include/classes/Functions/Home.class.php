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

            $content = '<div class="panel panel-default">' . PHP_EOL .
                       '    <div class="panel-heading">Knights Province Map database</div>' . PHP_EOL .
                       '    <table class="table .filetable">' . PHP_EOL .
                       '        <thead>' . PHP_EOL .
                       '            <tr>' . PHP_EOL .
                       '                <th class="col-xs-2">Name</th>' . PHP_EOL .
                       '                <th class="col-xs-2">Author</th>' . PHP_EOL .
                       '                <th class="col-xs-1">Version</th>' . PHP_EOL .
                       '                <th class="col-xs-3">Description</th>' . PHP_EOL .
                       '                <th class="col-xs-2">Last Updated</th>' . PHP_EOL .
                       '                <th class="col-xs-2">Screenshot</th>' . PHP_EOL .
                       '                <th class="col-xs-1">Downloads</th>' . PHP_EOL .
                       '                <th class="col-xs-1"></th>' . PHP_EOL .
                       '                <th class="col-xs-1"></th>' . PHP_EOL .
                       '            </tr>' . PHP_EOL .
                       '        </thead>' . PHP_EOL .
                       '        <tbody>' . PHP_EOL;

            if ($mapListItems != null) {
                foreach ($mapListItems as $mapItem) {
                    $content .= '            <tr>' . PHP_EOL .
                                '                <td>' . $mapItem['file_name'] . '</td>' . PHP_EOL .
                                '                <td>' . $mapItem['user_name'] . '</td>' . PHP_EOL .
                                '                <td>' . $mapItem['rev_file_version'] . '</td>' . PHP_EOL .
                                '                <td>Short description</td>' . PHP_EOL .
                                '                <td>' . $mapItem['rev_upload_date'] . '</td>' . PHP_EOL .
                                '                <td>Thumbnail</td>' . PHP_EOL .
                                '                <td style="text-align: right;">' . StrVal($mapItem['file_downloads']) . '</td>' . PHP_EOL .
                                '                <td>' . PHP_EOL .
                                '                    <button type="submit" title="Download this map">' . PHP_EOL .
                                '                        <span class="glyphicon glyphicon-download-alt" onclick="window.open(\'/download?file=' . $mapItem['rev_pk'] .
                                '\', \'popUpWindow\', \'height=400, width=600, left=10, top=10, , scrollbars=yes, menubar=no\'); return false;"></span>' . PHP_EOL .
                                '                    </button>' . PHP_EOL .
                                '                </td>' . PHP_EOL .
                                '                <td><button title="Flag this map"><span class="glyphicon glyphicon-flag"></span></button></td>' . PHP_EOL .
                                '            </tr>' . PHP_EOL;
                };
            };

            $content .= '        </tbody>' . PHP_EOL .
                        '    </table>' . PHP_EOL .
                        '</div>';

            return $content;
        }
    }
