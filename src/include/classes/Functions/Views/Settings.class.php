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
                       '        <form method="post" action="/updatesettings" id="settingFrm" name="settingFrm" class="form">' . PHP_EOL .
                       '            <div class="panel panel-default">' . PHP_EOL .
                       '                <div class="panel-heading">' . PHP_EOL .
                       '                    <span class="panel-title">Profile Settings</span>' . PHP_EOL .
                       '                    <button type="button" class="close pull-right">×</button>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="panel-body">' . PHP_EOL .
                       '                    <div class="row">' . PHP_EOL .
                       '                        <div class="col-md-3 col-lg-3" align="center">' . PHP_EOL .
                       '                            ' . $this -> utils -> getGravatar($_SESSION['user'] -> emailAddress, 250, 'identicon', 'pg') . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                        <div class="col-md-9 col-lg-9">' . PHP_EOL .
                       '                            <table class="table table-user-information">' . PHP_EOL .
                       '                                <tbody>' . PHP_EOL .
                       '                                    <tr>' . PHP_EOL .
                       '                                        <td><span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;Username</td>' . PHP_EOL .
                       '                                        <td>' . PHP_EOL .
                       '                                            ' . $_SESSION['user'] -> username . PHP_EOL .
                       '                                        </td>' . PHP_EOL .
                       '                                    </tr>' . PHP_EOL .
                       '                                    <tr>' . PHP_EOL .
                       '                                        <td><span class="glyphicon glyphicon-envelope"></span>&nbsp;&nbsp;Email Address</td>' . PHP_EOL .
                       '                                        <td>' . PHP_EOL .
                       '                                            <input type="email" class="form-control input-sm" id="settingEmailAddress" name="settingEmailAddress" ' .
                       'placeholder="' . $_SESSION['user'] -> emailAddress . '">' . PHP_EOL .
                       '                                        </td>' . PHP_EOL .
                       '                                    </tr>' . PHP_EOL .
                       '                                    <tr>' . PHP_EOL .
                       '                                        <td><span class="glyphicon glyphicon-lock"></span>&nbsp;&nbsp;Current Password</td>' . PHP_EOL .
                       '                                        <td>' . PHP_EOL .
                       '                                            <input type="password" class="form-control input-sm" id="settingCurPass" name="settingCurPass">' . PHP_EOL .
                       '                                        </td>' . PHP_EOL .
                       '                                    </tr>' . PHP_EOL .
                       '                                    <tr>' . PHP_EOL .
                       '                                        <td><span class="glyphicon glyphicon-lock"></span>&nbsp;&nbsp;New Password</td>' . PHP_EOL .
                       '                                        <td>' . PHP_EOL .
                       '                                            <input type="password" class="form-control input-sm" id="settingNewPass" name="settingNewPass">' . PHP_EOL .
                       '                                        </td>' . PHP_EOL .
                       '                                    </tr>' . PHP_EOL .
                       '                                    <tr>' . PHP_EOL .
                       '                                        <td><span class="glyphicon glyphicon-lock"></span>&nbsp;&nbsp;Repeat Password</td>' . PHP_EOL .
                       '                                        <td>' . PHP_EOL .
                       '                                            <input type="password" class="form-control input-sm" id="settingRepeatPass" name="settingRepeatPass">' . PHP_EOL .
                       '                                        </td>' . PHP_EOL .
                       '                                    </tr>' . PHP_EOL .
                       '                                </tbody>' . PHP_EOL .
                       '                            </table>' . PHP_EOL .
                       '                        </div>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '                <div class="panel-footer">' . PHP_EOL .
                       '                    <button type="submit" class="btn btn-primary space-right">Submit</button>' . PHP_EOL .
                       '                    <button type="reset" class="btn btn-warning">Reset Fields</button>' . PHP_EOL .
                       '                    <button type="button" class="btn btn-danger pull-right" disabled>Delete Account</button>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '        </form>' . PHP_EOL .
                       '    </div>' . PHP_EOL .
                       '</div>' . PHP_EOL;

            return $content;
        }
    }
