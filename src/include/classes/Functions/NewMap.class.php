<?php
    namespace Functions;

    class NewMap
    {
        private $utils = null;

        public function __construct(&$utilsClass) {
            $this -> utils = $utilsClass;
        }

        public function getContent(&$dbHandler) {
            $mapListItems = null;

            $content = '                    <div class="panel panel-default col-md-6 col-md-offset-3">' . PHP_EOL .
                       '                        <div class="panel-heading">Upload A New Map</div>' . PHP_EOL .
                       '                        <div class="panel-body">' . PHP_EOL .
                       '                            <div class="col-md-12">' . PHP_EOL .
                       '                                <h4>.MAP File</h4>' . PHP_EOL .
                       '                                <div class="input-group">' . PHP_EOL .
                       '                                    <label class="input-group-btn">' . PHP_EOL .
                       '                                        <span class="btn btn-primary">' . PHP_EOL .
                       '                                            Browse&hellip; <input type="file" id="mapFile" name="mapFile" style="display: none;">' . PHP_EOL .
                       '                                        </span>' . PHP_EOL .
                       '                                    </label>' . PHP_EOL .
                       '                                    <input type="text" class="form-control" readonly>' . PHP_EOL .
                       '                                </div>' . PHP_EOL .
                       '                            </div>' . PHP_EOL .
                       '                            <div class="col-md-12">' . PHP_EOL .
                       '                                <h4>.DAT File</h4>' . PHP_EOL .
                       '                                <div class="input-group">' . PHP_EOL .
                       '                                    <label class="input-group-btn">' . PHP_EOL .
                       '                                        <span class="btn btn-primary">' . PHP_EOL .
                       '                                            Browse&hellip; <input type="file" id="datFile" name="datFile" style="display: none;">' . PHP_EOL .
                       '                                        </span>' . PHP_EOL .
                       '                                    </label>' . PHP_EOL .
                       '                                    <input type="text" class="form-control" readonly>' . PHP_EOL .
                       '                                </div>' . PHP_EOL .
                       '                            </div>' . PHP_EOL .
                       '                            <div class="col-md-12">' . PHP_EOL .
                       '                                <h4>.SCRIPT File</h4>' . PHP_EOL .
                       '                                <div class="input-group">' . PHP_EOL .
                       '                                    <label class="input-group-btn">' . PHP_EOL .
                       '                                        <span class="btn btn-primary">' . PHP_EOL .
                       '                                            Browse&hellip; <input type="file" id="scriptFile" name="scriptFile" style="display: none;">' . PHP_EOL .
                       '                                        </span>' . PHP_EOL .
                       '                                    </label>' . PHP_EOL .
                       '                                    <input type="text" class="form-control" readonly>' . PHP_EOL .
                       '                                </div>' . PHP_EOL .
                       '                            </div>' . PHP_EOL .
                       '                            <div class="col-md-12">' . PHP_EOL .
                       '                                <h4>.INFO File</h4>' . PHP_EOL .
                       '                                <div class="input-group">' . PHP_EOL .
                       '                                    <label class="input-group-btn">' . PHP_EOL .
                       '                                        <span class="btn btn-primary">' . PHP_EOL .
                       '                                            Browse&hellip; <input type="file" id="infoFile" name="infoFile" style="display: none;">' . PHP_EOL .
                       '                                        </span>' . PHP_EOL .
                       '                                    </label>' . PHP_EOL .
                       '                                    <input type="text" class="form-control" readonly>' . PHP_EOL .
                       '                                </div>' . PHP_EOL .
                       '                            </div>' . PHP_EOL .
                       '                            <div class="col-md-12">' . PHP_EOL .
                       '                                <h4>.*.LIBX Files</h4>' . PHP_EOL .
                       '                                <div class="input-group">' . PHP_EOL .
                       '                                    <label class="input-group-btn">' . PHP_EOL .
                       '                                        <span class="btn btn-primary">' . PHP_EOL .
                       '                                            Browse&hellip; <input type="file" id="libxFiles" name="libxFiles" style="display: none;" multiple>' . PHP_EOL .
                       '                                        </span>' . PHP_EOL .
                       '                                    </label>' . PHP_EOL .
                       '                                    <input type="text" class="form-control" readonly>' . PHP_EOL .
                       '                                </div>' . PHP_EOL .
                       '                                <span class="help-block">These are files that hold the translations for your dynamic script texts</span>' . PHP_EOL .
                       '                            </div>' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL;
            return $content;
        }
    }
