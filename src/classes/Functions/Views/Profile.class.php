<?php
    namespace Functions\Views;

    class Profile
    {
        private $utils = null;

        public function __construct(&$utilsClass) {
            $this -> utils = $utilsClass;
        }

        public function getContent(&$dbHandler) {
            global $request;

            $userId = IntVal($request['call_parts'][1]);

            $query = 'SELECT ' . PHP_EOL .
                     '    `Users`.`user_name`, ' . PHP_EOL .
                     '    `Users`.`user_email_address`, ' . PHP_EOL .
                     '    `Groups`.`group_name` ' . PHP_EOL .
                     'FROM ' . PHP_EOL .
                     '    `Users` ' . PHP_EOL .
                     'LEFT JOIN ' . PHP_EOL .
                     '    `Groups` ON `Users`.`group_fk` = `Groups`.`group_pk` ' . PHP_EOL .
                     'WHERE ' . PHP_EOL .
                     '    `Users`.`user_pk` = :userid;';
            $dbHandler -> PrepareAndBind($query, Array('userid' => $userId));
            $user = $dbHandler -> ExecuteAndFetch();

            $content = '<div class="row">' . PHP_EOL .
                       '    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-6 col-xs-offset-0 col-sm-offset-0 col-md-offset-2 col-lg-offset-3 toppad">' . PHP_EOL .
                       '        <div class="panel panel-default">' . PHP_EOL .
                       '            <div class="panel-heading">' . PHP_EOL .
                       '                <span class="panel-title">Info Card</span>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '            <div class="panel-body">' . PHP_EOL .
                       '                <div class="row">' . PHP_EOL .
                       '                    <div class="col-md-3 col-lg-3" align="center">' . PHP_EOL .
                       '                        ' . $this -> utils -> getGravatar($user['user_email_address'],
                                                                                  250,
                                                                                  'identicon',
                                                                                  'pg') . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                    <div class="col-md-9 col-lg-9">' . PHP_EOL .
                       '                        <table class="table table-user-information">' . PHP_EOL .
                       '                            <tbody>' . PHP_EOL .
                       '                                <tr>' . PHP_EOL .
                       '                                    <td><span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;Username</td>' . PHP_EOL .
                       '                                    <td>' . PHP_EOL .
                       '                                        ' . $user['user_name'] . PHP_EOL .
                       '                                    </td>' . PHP_EOL .
                       '                                </tr>' . PHP_EOL .
                       '                                <tr>' . PHP_EOL .
                       '                                    <td><span class="glyphicon glyphicon-tag"></span>&nbsp;&nbsp;Group</td>' . PHP_EOL .
                       '                                    <td>' . PHP_EOL .
                       '                                        ' . $user['group_name'] . PHP_EOL .
                       '                                    </td>' . PHP_EOL .
                       '                                </tr>' . PHP_EOL .
                       '                            </tbody>' . PHP_EOL .
                       '                        </table>' . PHP_EOL .
                       '                    </div>' . PHP_EOL .
                       '                </div>' . PHP_EOL .
                       '            </div>' . PHP_EOL .
                       '        </div>' . PHP_EOL .
                       '    </div>' . PHP_EOL .
                       '</div>' . PHP_EOL;

            return $content;
        }
    }
