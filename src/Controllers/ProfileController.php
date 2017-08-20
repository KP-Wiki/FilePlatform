<?php
    /**
     * The central controller for all user profile pages
     *
     * PHP version 7
     *
     * @package    MapPlatform
     * @subpackage Controllers
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    namespace MapPlatform\Controllers;

    use \Slim\Http\Request;
    use \Slim\Http\Response;
    use \MapPlatform\Core;
    use \MapPlatform\AbstractClasses\PageController;
    use \Exception;
    use \InvalidArgumentException;

    /**
     * Profile controller
     *
     * @package    MapPlatform
     * @subpackage Controllers
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    class ProfileController extends PageController
    {
        /**
         * ProfileController invoker.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function __invoke(Request $request, Response $response, $args) {
            return $response;
        }

        /**
         * Show the default page.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function home(Request $request, Response $response, $args) {
            return $response;
        }

        /**
         * Show the user profile.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function getProfile(Request $request, Response $response, $args) {
            $this->container->logger->info("ManagementTools '/profile/" . $args['userId'] . "' route");
            $this->container->security->checkRememberMe();
            $userId = filter_var($args['userId'], FILTER_SANITIZE_NUMBER_INT);

            if ($userId == null) {
                $this->container->logger->error('getProfile -> Invalid user ID');

                return $response->withAddedHeader('Refresh', '1; url=/home')
                                ->withStatus(404, 'User not found.');
            } else {
                $pageID               = 0;
                $contentTemplate      = 'profile.phtml';
                $values['PageCrumbs'] = "<ol class=\"breadcrumb\">" . PHP_EOL .
                                        "    <li><a href=\"/home\">Home</a></li>" . PHP_EOL .
                                        "    <li class=\"active\">Profile</li>" . PHP_EOL .
                                        "</ol>";
                $values['userId']     = $userId;
                $values['user']       = $this->getUserProfile($userId);
                $pageTitle            = $values['user']['user_name'] . '\'s profile';

                if ($values['user'] == null)
                    return $response->withAddedHeader('Refresh', '1; url=/home')
                                    ->withStatus(404, 'User not found.');

                return $this->container->renderUtils->render($pageTitle, $pageID, $contentTemplate, $response, $values);
            };
        }

        /**
         * Show the edit user profile/settings page.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function editProfile(Request $request, Response $response, $args) {
            $this->container->logger->info("ManagementTools '/profile/edit' GET route");
            $this->container->security->checkRememberMe();
            $userId = $_SESSION['user']->id;

            if ($userId == -1) {
                $this->container->logger->error('editProfile -> Invalid user ID');

                return $response->withAddedHeader('Refresh', '1; url=/home')
                                ->withStatus(400);
            } else {
                $pageTitle            = 'Edit profile settings';
                $pageID               = 2;
                $contentTemplate      = 'profile_edit.phtml';
                $values['PageCrumbs'] = "<ol class=\"breadcrumb\">" . PHP_EOL .
                                        "    <li><a href=\"/home\">Home</a></li>" . PHP_EOL .
                                        "    <li><a href=\"/profile/" . $userId . "\">Profile</a></li>" . PHP_EOL .
                                        "    <li class=\"active\">Profile Settings</li>" . PHP_EOL .
                                        "</ol>";
                $values['userId']     = $userId;
                $values['user']       = $this->getUserProfile($userId);

                if ($values['user'] == null)
                    return $response->withAddedHeader('Refresh', '1; url=/home')
                                    ->withStatus(404, 'User not found.');

                return $this->container->renderUtils->render($pageTitle, $pageID, $contentTemplate, $response, $values);
            };
        }

        /**
         * Save the user profile/settings changes.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function saveProfile(Request $request, Response $response, $args) {
            $this->container->logger->info("ManagementTools '/profile/edit' POST route");
            $this->container->security->checkRememberMe();
            $userId = $_SESSION['user']->id;

            if ($userId == -1) {
                $this->container->logger->error('editProfile -> Invalid user ID');

                return $response->withAddedHeader('Refresh', '1; url=/home')
                                ->withStatus(400);
            } else {
                $data   = $request->getParsedBody();
                $result = $this->saveUserSettings($userId, $data);

                $pageTitle               = 'Edit profile settings';
                $pageID                  = 2;
                $contentTemplate         = 'result.phtml';
                $values['PageCrumbs']    = "<ol class=\"breadcrumb\">" . PHP_EOL .
                                           "    <li><a href=\"/home\">Home</a></li>" . PHP_EOL .
                                           "    <li><a href=\"/profile/" . $userId . "\">Profile</a></li>" . PHP_EOL .
                                           "    <li class=\"active\">Profile Settings</li>" . PHP_EOL .
                                           "</ol>";
                $values['resultType']    = $result['status'];
                $values['resultMessage'] = $result['message'];

                return $this->container->renderUtils->render($pageTitle, $pageID, $contentTemplate, $response, $values)
                                                    ->withAddedHeader('Refresh', '5; url=/home');
            };
        }

        private function getUserProfile($aUserId) {
            $database = $this->container->dataBase->PDO;

            try {
                $query = $database->select(['Users.user_name', 'Users.user_email_address', 'Groups.group_name'])
                                  ->from('Users')
                                  ->leftJoin('Groups', 'Groups.group_pk', '=', 'Users.group_fk')
                                  ->where('Users.user_pk', '=', $aUserId);
                $stmt  = $query->execute();
                $user  = $stmt->fetch();

                $user['gravatar'] = $this->container->miscUtils->getGravatar(
                    $user['user_email_address'],
                    250,
                    'identicon',
                    'pg'
                );

                if (Empty($user) || !$user)
                    throw new Exception('User not found');

                return $user;
            } catch (Exception $ex) {
                $this->container->logger->error('getUserProfile -> ' . $ex->getMessage());

                return null;
            };
        }

        private function saveUserSettings($aUserId, &$data) {
            $database       = $this->container->dataBase->PDO;
            $algorithm      = 'sha512';
            $iterationCount = 1024;
            $keyLength      = 1024;
            $outputRaw      = False;

            try {
                if (!isset($data['settingCurPass']) ||
                    Empty($data['settingCurPass']))
                    throw new Exception('Invalid request, inputs missing');

                $query = $database->select(['user_password', 'user_salt'])
                                  ->from('Users')
                                  ->where('user_pk', '=', $aUserId);
                $stmt  = $query->execute();
                $user  = $stmt->fetch();

                $passwordCheck = $this->container->security->isValidPassword($data['settingCurPass'], $user['user_salt'], $user['user_password']);

                if (!$passwordCheck)
                    throw new Exception('Invalid password.<br />' . PHP_EOL . 'Please try again.');

                if (isset($data['settingEmailAddress']) && !Empty($data['settingEmailAddress'])) {
                    $emailAddress = filter_var($data['settingEmailAddress'], FILTER_SANITIZE_EMAIL);

                    if (Empty($emailAddress))
                        throw new Exception('Invalid email address.<br />' . PHP_EOL . 'Please try again.');

                    $query = $database->update()
                                      ->table('Users')
                                      ->set(['user_email_address' => $emailAddress])
                                      ->where('user_pk', '=', $aUserId);
                    $database->beginTransaction();
                    $affectedRows = $query->execute();

                    if ($affectedRows === 1) {
                        $database->commit();
                    } else {
                        $database->rollBack();
                        throw new Exception('Unable to update your email address.<br />' . PHP_EOL . 'Please try again.');
                    };
                };

                if ((isset($data['settingNewPass'])    && !Empty($data['settingNewPass'])) &&
                    (isset($data['settingRepeatPass']) && !Empty($data['settingRepeatPass']))) {
                    $password                = filter_var($data['settingNewPass'], FILTER_UNSAFE_RAW);
                    $confirmPassword         = filter_var($data['settingRepeatPass'], FILTER_UNSAFE_RAW);
                    $LevenshteinForpasswords = Levenshtein($password, $confirmPassword);

                    if ($LevenshteinForpasswords !== 0)
                        throw new Exception('Passwords do not match.<br />' . PHP_EOL . 'Please try again.');

                    $hashVal = $this->container->security->pbkdf2(
                        $algorithm,
                        $password,
                        $user['user_salt'],
                        $iterationCount,
                        $keyLength,
                        $outputRaw
                    );

                    $query = $database->update()
                                      ->table('Users')
                                      ->set(['user_password' => $hashVal])
                                      ->where('user_pk', '=', $aUserId);
                    $database->beginTransaction();
                    $affectedRows = $query->execute();

                    if ($affectedRows === 1) {
                        $database->commit();
                    } else {
                        $database->rollBack();
                        throw new Exception('Unable to update your password.<br />' . PHP_EOL . 'Please try again.');
                    };
                };

                return [
                    'status' => 'Success',
                    'message' => 'User information updated successfully!<br />' . PHP_EOL .
                                 'Redirecting you now.'
                ];
            } catch (Exception $ex) {
                $this->container->logger->error('saveUserSettings -> ' . $ex->getMessage());

                return [
                    'status' => 'Error',
                    'message' => $ex->getMessage()
                ];
            };
        }
    }
