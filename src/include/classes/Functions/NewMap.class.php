<?php
    namespace Functions;

    class NewMap
    {
        public function __construct() {
        }

        public function getContent(&$dbHandler) {
            $mapListItems = null;

            $selectQuery = 'SELECT ' .
                           '    * ' .
                           'FROM ' .
                           '    `MapTypes` ' .
                           'ORDER BY ' .
                           '    `map_type_pk` ASC;';
            $dbHandler -> PrepareAndBind($selectQuery);
            $mapTypes = $dbHandler -> ExecuteAndFetchAll();
            $dbHandler -> Clean();

            $content = '                    <div class="stepwizard col-md-offset-3">' . PHP_EOL .
                       '                        <div class="stepwizard-row setup-panel">' . PHP_EOL .
                       '                            <div class="stepwizard-step">' . PHP_EOL .
                       '                                <a href="#step-1" type="button" class="btn btn-primary btn-circle">' . PHP_EOL .
                       '                                    <span class="glyphicon glyphicon-folder-open"></span>' . PHP_EOL .
                       '                                </a>' . PHP_EOL .
                       '                                <p>Step 1</p>' . PHP_EOL .
                       '                            </div>' . PHP_EOL .
                       '                            <div class="stepwizard-step">' . PHP_EOL .
                       '                                <a href="#step-2" type="button" class="btn btn-default btn-circle disabled" disabled="disabled">' . PHP_EOL .
                       '                                    <span class="glyphicon glyphicon-edit"></span>' . PHP_EOL .
                       '                                </a>' . PHP_EOL .
                       '                                <p>Step 2</p>' . PHP_EOL .
                       '                            </div>' . PHP_EOL .
                       '                            <div class="stepwizard-step">' . PHP_EOL .
                       '                                <a href="#step-3" type="button" class="btn btn-default btn-circle disabled" disabled="disabled">' . PHP_EOL .
                       '                                    <span class="glyphicon glyphicon-picture"></span>' . PHP_EOL .
                       '                                </a>' . PHP_EOL .
                       '                                <p>Step 3</p>' . PHP_EOL .
                       '                            </div>' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                    <form method="post" enctype="multipart/form-data" action="/upload" id="uploadMapFrm" role="form">' . PHP_EOL .
                       '                        <div class="row setup-content" id="step-1">' . PHP_EOL .
                       '                            <div class="col-xs-6 col-md-offset-3">' . PHP_EOL .
                       '                                <div class="col-md-12">' . PHP_EOL .
                       '                                    <h3>Select the map files</h3>' . PHP_EOL .
                       '                                    <div class="form-group required">' . PHP_EOL .
                       '                                        <label for="mapFile" class="control-label">.MAP File</label> ' . PHP_EOL .
                       '                                        <input type="file" id="mapFile" name="mapFile" class="filestyle" data-buttonName="btn-primary" data-buttonBefore="true" ' .
                       'data-iconName="glyphicon glyphicon-open-file" accept=".map" required>' . PHP_EOL .
                       '                                    </div>' . PHP_EOL .
                       '                                    <div class="form-group required">' . PHP_EOL .
                       '                                        <label for="datFile" class="control-label">.DAT File</label> ' . PHP_EOL .
                       '                                        <input type="file" id="datFile" name="datFile" class="filestyle" data-buttonName="btn-primary" data-buttonBefore="true" ' .
                       'data-iconName="glyphicon glyphicon-open-file" accept=".dat" required>' . PHP_EOL .
                       '                                    </div>' . PHP_EOL .
                       '                                    <div class="form-group">' . PHP_EOL .
                       '                                        <label for="scriptFile">.SCRIPT File</label> ' . PHP_EOL .
                       '                                        <input type="file" id="scriptFile" name="scriptFile" class="filestyle" data-buttonName="btn-primary" data-buttonBefore="true" ' .
                       'data-iconName="glyphicon glyphicon-open-file" accept=".script">' . PHP_EOL .
                       '                                    </div>' . PHP_EOL .
                       '                                    <div class="form-group">' . PHP_EOL .
                       '                                        <label for="libxFiles">.LIBX File</label>' . PHP_EOL .
                       '                                        <span class="help-block helpermarginfix">' . PHP_EOL .
                       '                                            These are files that hold the translations for your dynamic script texts<br />' . PHP_EOL .
                       '                                            More information <a href="https://github.com/Kromster80/knights_province/wiki" target="_blank">Github wiki</a>' . PHP_EOL .
                       '                                        </span> ' . PHP_EOL .
                       '                                        <input type="file" id="libxFiles" name="libxFiles[]" class="filestyle" data-buttonName="btn-primary" data-buttonBefore="true" ' .
                       'data-iconName="glyphicon glyphicon-open-file" accept=".libx" multiple>' . PHP_EOL .
                       '                                    </div>' . PHP_EOL .
                       '                                    <button class="btn btn-primary nextBtn btn-lg pull-right" type="button" >Next</button>' . PHP_EOL .
                       '                                </div>' . PHP_EOL .
                       '                            </div>' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                        <div class="row setup-content" id="step-2">' . PHP_EOL .
                       '                            <div class="col-xs-6 col-md-offset-3">' . PHP_EOL .
                       '                                <div class="col-md-12">' . PHP_EOL .
                       '                                    <h3>Provide information about the map</h3>' . PHP_EOL .
                       '                                    <div class="form-group required">' . PHP_EOL .
                       '                                        <label for="mapName" class="control-label">Map Name</label> ' . PHP_EOL .
                       '                                        <input type="text" id="mapName" name="mapName" class="form-control" required>' . PHP_EOL .
                       '                                    </div>' . PHP_EOL .
                       '                                    <div class="form-group required">' . PHP_EOL .
                       '                                        <label for="mapType" class="control-label">Map Type</label> ' . PHP_EOL .
                       '                                        <select id="mapType" name="mapType" class="form-control" required>' . PHP_EOL;

            foreach ($mapTypes as $maptype) {
                $content .= '                                            <option value="' . $maptype['map_type_pk'] . '">' . $maptype['map_type_name'] . '</option>' . PHP_EOL;
            };

            $content .= '                                        </select>' . PHP_EOL .
                        '                                    </div>' . PHP_EOL .
                        '                                    <div class="form-group required">' . PHP_EOL .
                        '                                        <label for="mapVersion" class="control-label">Map Version</label> ' . PHP_EOL .
                        '                                        <input type="text" id="mapVersion" name="mapVersion" class="form-control" required>' . PHP_EOL .
                        '                                    </div>' . PHP_EOL .
                        '                                    <div class="form-group required">' . PHP_EOL .
                        '                                        <label for="mapDescShort" class="control-label">Short Description</label> ' . PHP_EOL .
                        '                                        <input type="text" id="mapDescShort" name="mapDescShort" class="form-control" required>' . PHP_EOL .
                        '                                    </div>' . PHP_EOL .
                        '                                    <div class="form-group required">' . PHP_EOL .
                        '                                        <label for="mapDescFull" class="control-label">Full Description</label> ' . PHP_EOL .
                        '                                        <textarea id="mapDescFull" name="mapDescFull" class="form-control" rows="3" required></textarea>' . PHP_EOL .
                        '                                    </div>' . PHP_EOL .
                        '                                    <button class="btn btn-primary nextBtn btn-lg pull-right" type="button" >Next</button>' . PHP_EOL .
                        '                                </div>' . PHP_EOL .
                        '                            </div>' . PHP_EOL .
                        '                        </div>' . PHP_EOL .
                        '                        <div class="row setup-content" id="step-3">' . PHP_EOL .
                        '                            <div class="col-xs-6 col-md-offset-3">' . PHP_EOL .
                        '                                <div class="col-md-12">' . PHP_EOL .
                        '                                    <h3>Add some screenshots for the map</h3>' . PHP_EOL .
                        '                                    <div class="panel panel-default">'. PHP_EOL .
                        '                                        <div class="panel-heading">Screenshot 1</div>'. PHP_EOL .
                        '                                        <div class="panel-body">' . PHP_EOL .
                        '                                            <div class="form-group">' . PHP_EOL .
                        '                                                <label for="screenshotTitleOne">Title</label> ' . PHP_EOL .
                        '                                                <input type="text" id="screenshotTitleOne" name="screenshotTitleOne" class="form-control">' . PHP_EOL .
                        '                                            </div>' . PHP_EOL .
                        '                                            <div class="form-group">' . PHP_EOL .
                        '                                                <label for="screenshotFileOne">File</label> ' . PHP_EOL .
                        '                                                <input type="file" id="screenshotFileOne" name="screenshotFileOne" class="filestyle" data-buttonName="btn-primary" data-buttonBefore="true" ' .
                        'data-iconName="glyphicon glyphicon-open-file" accept="image/*">' . PHP_EOL .
                        '                                            </div>' . PHP_EOL .
                        '                                        </div>' . PHP_EOL .
                        '                                    </div>' . PHP_EOL .
                        '                                    <div class="panel panel-default">'. PHP_EOL .
                        '                                        <div class="panel-heading">Screenshot 2</div>'. PHP_EOL .
                        '                                        <div class="panel-body">' . PHP_EOL .
                        '                                            <div class="form-group">' . PHP_EOL .
                        '                                                <label for="screenshotTitleTwo">Title</label> ' . PHP_EOL .
                        '                                                <input type="text" id="screenshotTitleTwo" name="screenshotTitleTwo" class="form-control">' . PHP_EOL .
                        '                                            </div>' . PHP_EOL .
                        '                                            <div class="form-group">' . PHP_EOL .
                        '                                                <label for="screenshotFileTwo">File</label> ' . PHP_EOL .
                        '                                                <input type="file" id="screenshotFileTwo" name="screenshotFileTwo" class="filestyle" data-buttonName="btn-primary" data-buttonBefore="true" ' .
                        'data-iconName="glyphicon glyphicon-open-file" accept="image/*">' . PHP_EOL .
                        '                                            </div>' . PHP_EOL .
                        '                                        </div>' . PHP_EOL .
                        '                                    </div>' . PHP_EOL .
                        '                                    <div class="panel panel-default">'. PHP_EOL .
                        '                                        <div class="panel-heading">Screenshot 3</div>'. PHP_EOL .
                        '                                        <div class="panel-body">' . PHP_EOL .
                        '                                            <div class="form-group">' . PHP_EOL .
                        '                                                <label for="screenshotTitleThree">Title</label> ' . PHP_EOL .
                        '                                                <input type="text" id="screenshotTitleThree" name="screenshotTitleThree" class="form-control">' . PHP_EOL .
                        '                                            </div>' . PHP_EOL .
                        '                                            <div class="form-group">' . PHP_EOL .
                        '                                                <label for="screenshotFileThree">File</label> ' . PHP_EOL .
                        '                                                <input type="file" id="screenshotFileThree" name="screenshotFileThree" class="filestyle" data-buttonName="btn-primary" data-buttonBefore="true" ' .
                        'data-iconName="glyphicon glyphicon-open-file" accept="image/*">' . PHP_EOL .
                        '                                            </div>' . PHP_EOL .
                        '                                        </div>' . PHP_EOL .
                        '                                    </div>' . PHP_EOL .
                        '                                    <div class="panel panel-default">'. PHP_EOL .
                        '                                        <div class="panel-heading">Screenshot 4</div>'. PHP_EOL .
                        '                                        <div class="panel-body">' . PHP_EOL .
                        '                                            <div class="form-group">' . PHP_EOL .
                        '                                                <label for="screenshotTitleFour">Title</label> ' . PHP_EOL .
                        '                                                <input type="text" id="screenshotTitleFour" name="screenshotTitleFour" class="form-control">' . PHP_EOL .
                        '                                            </div>' . PHP_EOL .
                        '                                            <div class="form-group">' . PHP_EOL .
                        '                                                <label for="screenshotFileFour">File</label> ' . PHP_EOL .
                        '                                                <input type="file" id="screenshotFileFour" name="screenshotFileFour" class="filestyle" data-buttonName="btn-primary" data-buttonBefore="true" ' .
                        'data-iconName="glyphicon glyphicon-open-file" accept="image/*">' . PHP_EOL .
                        '                                            </div>' . PHP_EOL .
                        '                                        </div>' . PHP_EOL .
                        '                                    </div>' . PHP_EOL .
                        '                                    <div class="panel panel-default">'. PHP_EOL .
                        '                                        <div class="panel-heading">Screenshot 5</div>'. PHP_EOL .
                        '                                        <div class="panel-body">' . PHP_EOL .
                        '                                            <div class="form-group">' . PHP_EOL .
                        '                                                <label for="screenshotTitleFive">Title</label> ' . PHP_EOL .
                        '                                                <input type="text" id="screenshotTitleFive" name="screenshotTitleFive" class="form-control">' . PHP_EOL .
                        '                                            </div>' . PHP_EOL .
                        '                                            <div class="form-group">' . PHP_EOL .
                        '                                                <label for="screenshotFileFive">File</label> ' . PHP_EOL .
                        '                                                <input type="file" id="screenshotFileFive" name="screenshotFileFive" class="filestyle" data-buttonName="btn-primary" data-buttonBefore="true" ' .
                        'data-iconName="glyphicon glyphicon-open-file" accept="image/*">' . PHP_EOL .
                        '                                            </div>' . PHP_EOL .
                        '                                        </div>' . PHP_EOL .
                        '                                    </div>' . PHP_EOL .
                        '                                    <button class="btn btn-success btn-lg pull-right" type="submit">Submit</button>' . PHP_EOL .
                        '                                </div>' . PHP_EOL .
                        '                            </div>' . PHP_EOL .
                        '                        </div>' . PHP_EOL .
                        '                    </form>' . PHP_EOL;
            return $content;
        }
    }
