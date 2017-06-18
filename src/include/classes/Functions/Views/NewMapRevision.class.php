<?php
    namespace Functions\Views;

    class NewMapRevision
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

            $content = '<div class="row">' . PHP_EOL .
                       '    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-6 col-xs-offset-0 col-sm-offset-0 col-md-offset-2 col-lg-offset-3 toppad">' . PHP_EOL .
                       '        <form method="post" enctype="multipart/form-data" action="/uploadmaprev/' . $mapId . '" id="newMapRevFrm" role="form">' . PHP_EOL .
                       '            <div class="panel panel-default">' . PHP_EOL .
                       '                <div class="panel-heading">' . PHP_EOL .
                       '                    <h4>' . $mapItem['data']['map_name'] . '</h4>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <table class="table table-user-information">' . PHP_EOL .
                       '                    <tbody>' . PHP_EOL .
                       '                        <tr>' . PHP_EOL .
                       '                            <td class="col-sm-3"><b>Map Version</b></td>' . PHP_EOL .
                       '                            <td class="col-sm-9">' . PHP_EOL .
                       '                                <input type="text" id="newMapRevVersion" name="newMapRevVersion" class="form-control" value="' .
                       $mapItem['data']['rev_map_version'] . '" required>' . PHP_EOL .
                       '                            </td>' . PHP_EOL .
                       '                        </tr>' . PHP_EOL .
                       '                        <tr>' . PHP_EOL .
                       '                            <td class="col-sm-3"><b>.MAP File</b></td>' . PHP_EOL .
                       '                            <td class="col-sm-9">' . PHP_EOL .
                       '                                <input type="file" id="newMapRevMapFile" name="newMapRevMapFile" class="filestyle" data-buttonName="btn-primary"' .
                       ' data-buttonBefore="true" data-iconName="glyphicon glyphicon-open-file" accept=".map" required>' . PHP_EOL .
                       '                            </td>' . PHP_EOL .
                       '                        </tr>' . PHP_EOL .
                       '                        <tr>' . PHP_EOL .
                       '                            <td class="col-sm-3"><b>.DAT File</b></td>' . PHP_EOL .
                       '                            <td class="col-sm-9">' . PHP_EOL .
                       '                                <input type="file" id="newMapRevDatFile" name="newMapRevDatFile" class="filestyle" data-buttonName="btn-primary"' .
                       ' data-buttonBefore="true" data-iconName="glyphicon glyphicon-open-file" accept=".dat" required>' . PHP_EOL .
                       '                            </td>' . PHP_EOL .
                       '                        </tr>' . PHP_EOL .
                       '                        <tr>' . PHP_EOL .
                       '                            <td class="col-sm-3"><b>.SCRIPT File</b></td>' . PHP_EOL .
                       '                            <td class="col-sm-9">' . PHP_EOL .
                       '                                <input type="file" id="newMapRevScriptFile" name="newMapRevScriptFile" class="filestyle" data-buttonName="btn-primary"' .
                       ' data-buttonBefore="true" data-iconName="glyphicon glyphicon-open-file" accept=".script">' . PHP_EOL .
                       '                            </td>' . PHP_EOL .
                       '                        </tr>' . PHP_EOL .
                       '                        <tr>' . PHP_EOL .
                       '                            <td class="col-sm-3"><b>.LIBX Files</b></td>' . PHP_EOL .
                       '                            <td class="col-sm-9">' . PHP_EOL .
                       '                                <input type="file" id="newMapRevLibxFiles" name="newMapRevLibxFiles[]" class="filestyle" data-buttonName="btn-primary"' .
                       ' data-buttonBefore="true" data-iconName="glyphicon glyphicon-open-file" accept=".libx" multiple>' . PHP_EOL .
                       '                            </td>' . PHP_EOL .
                       '                        </tr>' . PHP_EOL .
                       '                        <tr>' . PHP_EOL .
                       '                            <td class="col-lg-12" colspan="2">' . PHP_EOL .
                       '                                <button class="btn btn-success" type="submit" title="Submit new Map Information">' . PHP_EOL .
                       '                                    <span class="glyphicon glyphicon-floppy-save"></span>&nbsp;&nbsp;Submit' . PHP_EOL .
                       '                                </button>' . PHP_EOL .
                       '                                <a class="btn btn-danger pull-right" id="btnCancelUpdateMapInfo" title="Cancel"' .
                       ' href="/mapdetails/' . $mapId . '" role="button">' . PHP_EOL .
                       '                                    <span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;Cancel' . PHP_EOL .
                       '                                </a>' . PHP_EOL .
                       '                            </td>' . PHP_EOL .
                       '                        </tr>' . PHP_EOL .
                       '                    </tbody>' . PHP_EOL .
                       '                </table>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '        </form>' . PHP_EOL .
                       '    </div>' . PHP_EOL .
                       '</div>';

            return $content;
        }
    }
