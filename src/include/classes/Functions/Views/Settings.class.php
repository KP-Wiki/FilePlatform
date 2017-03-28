<?php
    namespace Functions\Views;

    class Settings
    {
        private $utils = null;

        public function __construct(&$utilsClass) {
            $this -> utils = $utilsClass;
        }

        public function getContent(&$dbHandler) {
            global $request;


            $content = '<div class="row">' . PHP_EOL .
                       '    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-6 col-xs-offset-0 col-sm-offset-0 col-md-offset-2 col-lg-offset-3 toppad">' . PHP_EOL .
                       '        <div class="panel panel-default">' . PHP_EOL .
                       '            <div class="panel-heading">' . PHP_EOL .
                       '                <h4>Profile Settings</h4>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '            <div class="panel-body">' . PHP_EOL .
                       '                <div class="well well-sm">' . PHP_EOL .
                       '                    <div class="row">' . PHP_EOL .
                       '                        <div class="col-sx-6 col-sm-4 col-md-3">' . PHP_EOL .
                       '                            ' . $this -> utils -> getGravatar($_SESSION['user'] -> emailAddress, 140, 'identicon', 'pg') . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                        <div class="col-sx-6 col-sm-8 col-md-9">' . PHP_EOL .
                       '                            <h4>' . $_SESSION['user'] -> username . '</h4><br />' . PHP_EOL .
                       '                            <div class="col-md-11">' . PHP_EOL .
                       '                                <form class="form-horizontal">' . PHP_EOL .
                       '                                    <div class="form-group">' . PHP_EOL .
                       '                                        <label for="settingEmailAddress" class="control-label">Email Address</label>' . PHP_EOL .
                       '                                        <div class="input-group">' . PHP_EOL .
                       '                                            <span class="input-group-addon" id="emailAddon">' . PHP_EOL .
                       '                                                <i class="glyphicon glyphicon-envelope"></i>' . PHP_EOL .
                       '                                            </span>' . PHP_EOL .
                       '                                            <input type="email" class="form-control" id="settingEmailAddress" name="settingEmailAddress" ' .
                       'placeholder="' . $_SESSION['user'] -> emailAddress . '" aria-describedby="emailAddon">' . PHP_EOL .
                       '                                        </div>' . PHP_EOL .
                       '                                    </div>' . PHP_EOL .
                       '                                    <div class="form-group">' . PHP_EOL .
                       '                                        <label for="settingCurPass" class="control-label">Current Password</label>' . PHP_EOL .
                       '                                        <div class="input-group">' . PHP_EOL .
                       '                                            <span class="input-group-addon" id="curPassAddon">' . PHP_EOL .
                       '                                                <i class="glyphicon glyphicon-asterisk"></i>' . PHP_EOL .
                       '                                            </span>' . PHP_EOL .
                       '                                            <input type="password" class="form-control" id="settingCurPass" name="settingCurPass" ' .
                       'aria-describedby="curPassAddon">' . PHP_EOL .
                       '                                        </div>' . PHP_EOL .
                       '                                    </div>' . PHP_EOL .
                       '                                    <div class="form-group">' . PHP_EOL .
                       '                                        <label for="settingNewPass" class="control-label">New Password</label>' . PHP_EOL .
                       '                                        <div class="input-group">' . PHP_EOL .
                       '                                            <span class="input-group-addon" id="newPassAddon">' . PHP_EOL .
                       '                                                <i class="glyphicon glyphicon-asterisk"></i>' . PHP_EOL .
                       '                                            </span>' . PHP_EOL .
                       '                                            <input type="password" class="form-control" id="settingNewPass" name="settingNewPass" ' .
                       'aria-describedby="newPassAddon">' . PHP_EOL .
                       '                                        </div>' . PHP_EOL .
                       '                                    </div>' . PHP_EOL .
                       '                                    <div class="form-group">' . PHP_EOL .
                       '                                        <label for="settingRepeatPass" class="control-label">Repeat Password</label>' . PHP_EOL .
                       '                                        <div class="input-group">' . PHP_EOL .
                       '                                            <span class="input-group-addon" id="repeatPassAddon">' . PHP_EOL .
                       '                                                <i class="glyphicon glyphicon-asterisk"></i>' . PHP_EOL .
                       '                                            </span>' . PHP_EOL .
                       '                                            <input type="password" class="form-control" id="settingRepeatPass" name="settingRepeatPass" ' .
                       'aria-describedby="repeatPassAddon">' . PHP_EOL .
                       '                                        </div>' . PHP_EOL .
                       '                                    </div>' . PHP_EOL .
                       '                                    <button type="submit" class="btn btn-primary pull-left">Submit</button>' . PHP_EOL .
                       '                                </form>' . PHP_EOL .
                       '                            </div>' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '        </div>' . PHP_EOL .
                       '    </div>' . PHP_EOL .
                       '</div>' . PHP_EOL;

            return $content;
        }
    }
