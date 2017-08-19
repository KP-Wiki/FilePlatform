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
         * ImageController map image retrieval funtion.
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
         * ImageController default image retrieval funtion.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function editProfile(Request $request, Response $response, $args) {
            $this->container->logger->info("ManagementTools '/profile/" . $args['userId'] . "/edit' route");
            $this->container->security->checkRememberMe();
            $userId = filter_var($args['userId'], FILTER_SANITIZE_NUMBER_INT);

            if (($userId == null) || ($userId !== $_SESSION['user']->id)) {
                $this->container->logger->error('getProfile -> Invalid user ID or user tried to edit someone else\'s profile');

                return $response->withAddedHeader('Refresh', '1; url=/home')
                                ->withStatus(400);
            } else {
                $pageID               = 0;
                $contentTemplate      = 'profile_edit.phtml';
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
    }
