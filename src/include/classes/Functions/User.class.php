<?php
    namespace Functions;
    use \Exception;

    class User
    {
        private $utils = null;

        public function __construct(&$utilsClass) {
            $this -> utils = $utilsClass;
        }

        public function updateUserInfo(&$securityClass, &$dbHandler) {
            global $request;

            $algorithm       = 'sha512';
            $iterationCount  = 1024;
            $keyLength       = 1024;
            $outputRaw       = False;
            $userId          = $_SESSION['user'] -> id;

            try {
                if (!isset($_POST['settingCurPass']) ||
                     Empty($_POST['settingCurPass']))
                    throw new Exception('Invalid request, inputs missing');

                $selectQuery = 'SELECT ' . PHP_EOL .
                               '    `user_password`, ' . PHP_EOL .
                               '    `user_salt` ' . PHP_EOL .
                               'FROM ' . PHP_EOL .
                               '    `Users` ' . PHP_EOL .
                               'WHERE ' . PHP_EOL .
                               '    `user_pk` = :userid;';
                $dbHandler -> PrepareAndBind($selectQuery, Array('userid' => $userId));
                $userInfo      = $dbHandler -> ExecuteAndFetch();
                $passwordCheck = $securityClass -> isValidPassword($_POST['settingCurPass'], $userInfo['user_salt'], $userInfo['user_password']);

                if (!$passwordCheck) {
                    $this -> utils -> http_response_code(401);
                    $content['status']  = 'Error';
                    $content['message'] = 'Invalid password.<br />' . PHP_EOL .
                                          'Please try again.';

                    return $content;
                };

                if (isset($_POST['settingEmailAddress']) && !Empty($_POST['settingEmailAddress'])) {
                    $emailAddress = $this -> utils -> cleanInput($_POST['settingEmailAddress']);

                    if (Empty($emailAddress)) {
                        $this -> utils -> http_response_code(400);
                        $content['status']  = 'Error';
                        $content['message'] = 'Invalid email address.<br />' . PHP_EOL .
                                              'Please try again.';

                        return $content;
                    };

                    $updateQuery = 'UPDATE ' . PHP_EOL .
                                   '    `Users` ' . PHP_EOL .
                                   'SET ' . PHP_EOL .
                                   '    `user_email_address` = :emailaddress' . PHP_EOL .
                                   'WHERE ' . PHP_EOL .
                                   '    `user_pk` = :userid;';
                    $dbHandler -> PrepareAndBind($updateQuery, Array('userid'       => $userId,
                                                                     'emailaddress' => $emailAddress));
                    $dbHandler -> Execute();
                } else {
                    $emailQuery = '';
                };

                if ((isset($_POST['settingNewPass'])    && !Empty($_POST['settingNewPass'])) &&
                    (isset($_POST['settingRepeatPass']) && !Empty($_POST['settingRepeatPass']))) {
                    $LevenshteinForpasswords = Levenshtein($_POST['settingNewPass'], $_POST['settingRepeatPass']);

                    if ($LevenshteinForpasswords !== 0) {
                        $this -> utils -> http_response_code(400);
                        $content['status']  = 'Error';
                        $content['message'] = 'Passwords do not match.<br />' . PHP_EOL .
                                              'Please try again.';

                        return $content;
                    };

                    $hashVal     = $securityClass -> pbkdf2($algorithm, $_POST['settingNewPass'], $userInfo['user_salt'], $iterationCount, $keyLength, $outputRaw);
                    $updateQuery = 'UPDATE ' . PHP_EOL .
                                   '    `Users` ' . PHP_EOL .
                                   'SET ' . PHP_EOL .
                                   '    `user_password` = :password' . PHP_EOL .
                                   'WHERE ' . PHP_EOL .
                                   '    `user_pk` = :userid;';
                    $dbHandler -> PrepareAndBind($updateQuery, Array('userid'   => $userId,
                                                                     'password' => $hashVal));
                    $dbHandler -> Execute();
                } else {
                    $passwordQuery = '';
                };

                $this -> utils -> http_response_code(200);
                $content['status']  = 'Success';
                $content['message'] = 'User information updated successfully!<br />' . PHP_EOL .
                                      'Redirecting you now.';

                return $content;
            } catch (Exception $e) {
                $this -> utils -> http_response_code(400);
                $content['status']  = 'Error';
                $content['message'] = 'Unable to update user information.<br />' . PHP_EOL .
                                      'Please try again.';

                return $content;
            };
        }
    }
