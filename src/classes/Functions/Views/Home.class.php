<?php
    namespace Functions\Views;

    class Home
    {
        public function __construct() {
        }

        public function getContent(&$dbHandler) {
            $mapListItems = null;

            $content = '                    <div class="panel panel-default">' . PHP_EOL .
                       '                        <div class="panel-heading">Knights Province Map database</div>' . PHP_EOL .
                       '                        <div class="panel-body">' . PHP_EOL .
                       '                            <table id="homeTable" class="table table-striped table-bordered table-hover" ' .
                       'data-toggle="table" ' .
                       'data-search="true" ' .
                       'data-sort-name="avg_rating" ' .
                       'data-sort-order="desc" ' .
                       'data-show-toggle="true" ' .
                       'data-show-columns="true" ' .
                       'data-show-export="true" ' .
                       'data-minimum-count-columns="2" ' .
                       'data-url="/api/v1/maps" ' .
                       'data-show-refresh="true" ' .
                       'data-show-pagination-switch="true" ' .
                       'data-pagination="true" ' .
                       'data-page-list="[25, 50, 100, ALL]" ' .
                       'data-page-size="25" ' .
                       'data-show-footer="false" ' .
                       'data-side-pagination="client">' . PHP_EOL .
                       '                                <thead>' . PHP_EOL .
                       '                                    <tr>' . PHP_EOL .
                       '                                        <th class="col-xs-2" data-field="map_type_name" data-sortable="true">Type</th>' . PHP_EOL .
                       '                                        <th class="col-xs-2" data-field="map_name" data-formatter="mapNameFormatter" data-sortable="true">Name</th>' . PHP_EOL .
                       '                                        <th class="col-xs-2" data-field="user_name" data-formatter="mapAuthorFormatter" data-sortable="true">Author</th>' . PHP_EOL .
                       '                                        <th class="col-xs-4" data-field="rev_map_description_short" data-sortable="false">Description</th>' . PHP_EOL .
                       '                                        <th class="col-xs-1" data-field="avg_rating" data-sortable="true">Rating</th>' . PHP_EOL .
                       '                                        <th class="col-xs-1" data-field="map_downloads" data-sortable="true">Downloads</th>' . PHP_EOL .
                       '                                    </tr>' . PHP_EOL .
                       '                                </thead>' . PHP_EOL .
                       '                            </table>' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL;

            return $content;
        }
    }