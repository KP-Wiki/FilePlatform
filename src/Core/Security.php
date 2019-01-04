<?php
    /**
     * The internal security class
     *
     * This package should be used for all security features
     *
     * PHP version 7
     *
     * @package MapPlatform
     * @subpackage Core
     * @author  Thimo Braker <thibmorozier@gmail.com>
     * @version 1.0.0
     * @since   First available since Release 1.0.0
     */
    namespace MapPlatform\Core;

    use \InvalidArgumentException;
    use \MapPlatform\Core\Constants;
    use \Slim\Container;

    /**
     * Security
     *
     * @package    MapPlatform
     * @subpackage Core
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    final class Security
    {
        /** @var \Slim\Container $container The framework container */
        private $container;

        /**
         * Security constructor.
         *
         * @param \Slim\Container The application controller.
         */
        public function __construct(Container &$aContainer)
        {
            $this->container = $aContainer;
        }

        /**
         * Initialize the user session
         */
        private function initSession()
        {
            $_SESSION['user'] = (object)[];
            $_SESSION['user']->group = 0;
            $_SESSION['user']->id = -1;
        }

        /**
         * Destroy the user session
         */
        private function destroySession()
        {
            setcookie('userId', '', time() - 3600, '/');
            setcookie('token', '', time() - 3600, '/');
            session_unset();
            session_destroy();
        }

        /**
         * Restart the user session
         */
        private function restartSession()
        {
            $this->destroySession();
            session_start();
            $this->initSession();
        }

        /**
         * Determine if the provided password checks up with the hash in the database
         *
         * @param int $aUserId The user ID
         * @param string $aPassword The user provided password
         * @param string $aHash The hash to compare against
         *
         * @return boolean
         *
         * @author  Thimo (thibmoRozier) Braker <thibmorozier@gmail.com>
         * @version 1.0.0
         */
        public function verifyPassword($aUserId, string $aPassword, string $aHash)
        {
            if ($this->container->miscUtils->isNullOrEmpty($aPassword) || $this->container->miscUtils->isNullOrEmpty($aHash))
                return false;

            if (password_verify($aPassword, $aHash)) {
                if (password_needs_rehash($aHash, Constants::HASH_ALGO, Constants::HASH_OPTIONS)) {
                    $database = $this->container->dataBase->PDO;

                    try {
                        $query = $database->update()
                            ->table('Users')
                            ->set(['user_password' => $this->setPassword($password)])
                            ->where('user_pk', '=', $aUserId);
                        $database->beginTransaction();
                        $affectedRows = $query->execute();

                        if ($affectedRows === 1) {
                            $database->commit();
                        } else {
                            $database->rollBack();
                        }
                    } catch (Exception $ex) {
                        $this->container->logger->error('verifyPassword -> ' . $ex->getMessage());
                    }
                }

                return true;
            }

            return false;
        }

        /**
         * Set a new password and save it to the database
         *
         * @param string $aPassword The user provided password
         *
         * @return string
         *
         * @author  Thimo (thibmoRozier) Braker <thibmorozier@gmail.com>
         * @version 1.0.0
         */
        public function setPassword(string $aPassword)
        {
            if ($this->container->miscUtils->isNullOrEmpty($aPassword))
                return '';

            $hash = password_hash($aPassword, Constants::HASH_ALGO, Constants::HASH_OPTIONS);
            return $this->container->miscUtils->isNullOrEmpty($hash) ? '' : $hash;
        }

        /**
         * Check validity of ReCaptcha response value
         *
         * @param string The response provided by Google
         *
         * @return boolean
         */
        private function isValidReCaptcha($reCaptchaResponse)
        {
            $this->container->logger->debug('isValidReCaptcha -> start( reCaptchaResponse = ' . $reCaptchaResponse . ' )');
            $config = $this->container->get('settings')['reCaptcha'];

            if (empty($reCaptchaResponse))
                return false;

            $secret = $config['secretKey'];
            $curl = curl_init(); // Create curl resource
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => 1, // Return the server's response data as a string rather then a boolean
                CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $reCaptchaResponse .
                               '&remoteip=' . $_SERVER['REMOTE_ADDR'],
                CURLOPT_USERAGENT => 'Maps_Platform/v' . APP_VERSION
            ]);
            $response = json_decode(curl_exec($curl), true);
            curl_close($curl); // Close curl resource to free up system resources
            $this->container->logger->debug('isValidReCaptcha -> response = ' . print_r($response, true));
            return $response['success'];
        }

        /**
         * Check validity of ReCaptcha response value
         *
         * @param array POST values as an array
         *
         * @return array The status repsresented as an array
         */
        public function register($aDataArray)
        {
            $database = $this->container->dataBase->PDO;
            $defaultGroup = 1;

            try {
                $username = filter_var($aDataArray['username'], FILTER_SANITIZE_STRING, Constants::STRING_FILTER_FLAGS_SHORT);
                $emailAddress = filter_var($aDataArray['emailAddress'], FILTER_SANITIZE_EMAIL);
                $password = filter_var($aDataArray['password'], FILTER_UNSAFE_RAW);                      // Don't clean this, passwords should be left untouched as they are hashed
                $confirmPassword = filter_var($aDataArray['confirmPassword'], FILTER_UNSAFE_RAW);        // Don't clean this, passwords should be left untouched as they are hashed
                $reCaptchaResponse = filter_var($aDataArray['g-recaptcha-response'], FILTER_UNSAFE_RAW); // Don't clean this, it's provided by Google
                $this->container->logger->debug('Register -> start( username = ' . $username . ', emailAddress = '. $emailAddress .
                                                ', reCaptchaResponse = '. $reCaptchaResponse . ' )');
                $query = $database->select()
                    ->count('*', 'user_count')
                    ->from('Users')
                    ->where('user_name', '=', $username)
                    ->where('user_email_address', '=', $emailAddress, 'OR');
                $stmt = $query->execute();
                $userCount = $stmt->fetch();
                $LevenshteinForpasswords = levenshtein($password, $confirmPassword);
                $googleResponse = $this->isValidReCaptcha($reCaptchaResponse);
                $this->container->logger->debug('Register -> userCount = ' . print_r($userCount, true) . ', LevenshteinForpasswords = ' . $LevenshteinForpasswords .
                                                ', googleResponse = ' . $googleResponse);

                if ($userCount['user_count'] != 0) {
                    $this->container->logger->debug('Register -> User already exists');
                    return [
                        'status' => 'Error',
                        'message' => 'User already exists'
                    ];
                }

                if (($LevenshteinForpasswords !== 0) || !$googleResponse) {
                    $this->container->logger->debug('Register -> Passwords don\'t match');
                    return [
                        'status' => 'Error',
                        'message' => 'Bad Request'
                    ];
                }

                $query = $database->insert(['user_name', 'user_password', 'user_email_address', 'group_fk'])
                    ->into('Users')
                    ->values([
                        $username,
                        $this->setPassword($password),
                        $emailAddress,
                        $defaultGroup
                    ]);
                $database->beginTransaction();
                $insertID = $query->execute(true);

                if ($insertID > 0) {
                    $database->commit();
                    $this->container->logger->debug('Register -> Registration successful');
                    $this->login($aDataArray);
                    return [
                        'status' => 'Success',
                        'message' => 'Registration successful!'
                    ];
                } else {
                    $database->rollBack();
                    $this->container->logger->debug('Register -> Unable to create user');
                    return [
                        'status' => 'Error',
                        'message' => 'Unable to create user, please try again later'
                    ];
                }
            } catch (Exception $ex) {
                $this->container->logger->error('Register -> ex = ' . $ex);
                return [
                    'status' => 'Error',
                    'message' => 'Unable to create user, please try again later'
                ];
            }
        }

        /**
         * Check validity of ReCaptcha response value
         *
         * @param array POST values as an array
         *
         * @return array The status repsresented as an array
         */
        public function login($aDataArray)
        {
            $database = $this->container->dataBase->PDO;
            $config = $this->container->get('settings')['security'];
            $this->initSession();

            try {
                $this->container->logger->debug(print_r($aDataArray, true));
                $username = filter_var($aDataArray['username'], FILTER_SANITIZE_STRING, Constants::STRING_FILTER_FLAGS_SHORT);
                $password = filter_var($aDataArray['password'], FILTER_DEFAULT);
                $ipAddress = $this->container->miscUtils->getClientIp();
                $this->container->logger->debug('Login -> start( username = ' . $username . ', ipAddress = ' . $ipAddress . ' )');
                $query = $database->select(['user_pk', 'user_name', 'user_password', 'user_email_address', 'group_fk'])
                    ->from('Users')
                    ->where('user_name', '=', $username)
                    ->where('user_email_address', '=', $username, 'OR');
                $stmt = $query->execute();
                $userArr = $stmt->fetchall();

                if (count($userArr) < 1) {
                    $this->container->logger->error('Login -> Invalid credentials');
                    return [
                        'status' => 'Error',
                        'message' => 'Invalid credentials, try again.'
                    ];
                }

                $user = $userArr[0];
                $this->container->logger->debug('Login -> user : ' . print_r($user, true));
                $passwordCheck = $this->verifyPassword($user['user_pk'], $password, $user['user_password']);

                if (!$passwordCheck) {
                    $this->container->logger->debug('Login -> Invalid credentials');
                    return [
                        'status' => 'Error',
                        'message' => 'Invalid credentials, try again.'
                    ];
                }

                $bytes = openssl_random_pseudo_bytes(32, $crypto_strong);
                $token = bin2hex($bytes);
                $query = $database->insert(['user_fk', 'token', 'ip_address'])
                    ->into('RememberMe')
                    ->values([$user['user_pk'], $token, $ipAddress]);
                $database->beginTransaction();
                $insertID = $query->execute(true);
                $this->container->logger->debug('Login -> insertId : ' . $insertID);

                if ($insertID <= 0) {
                    $database->rollBack();
                    $this->container->logger->debug('Login -> Unable to create remember-me token');
                    return [
                        'status' => 'Error',
                        'message' => 'Unable to authenticate user, please try again later.'
                    ];
                }

                $database->commit();
                setcookie('userId', $user['user_pk'], time() + $config['cookieLifetime'], '/');
                setcookie('token', $token, time() + $config['cookieLifetime'], '/');
                $this->container->logger->debug('Login -> Login successful');
                return [
                    'status' => 'Success',
                    'message' => 'Login successful, redirecting you to the homepage.'
                ];
            } catch (Exception $ex) {
                $this->container->logger->error('Login -> ex = ' . $ex);
                return [
                    'status' => 'Error',
                    'message' => 'Unable to authenticate user, please try again later.'
                ];
            }
        }

        /**
         * Check existance and validity of a user's remember-me cookie
         */
        public function checkRememberMe()
        {
            $database = $this->container->dataBase->PDO;
            $config = $this->container->get('settings')['security'];
            $this->initSession();

            if (isset($_COOKIE['userId']) && isset($_COOKIE['token'])) {
                $userId = filter_input(INPUT_COOKIE, 'userId', FILTER_SANITIZE_NUMBER_INT);
                $token = filter_input(INPUT_COOKIE, 'token', FILTER_SANITIZE_STRING, Constants::STRING_FILTER_FLAGS_SHORT);
                $ipAddress = $this->container->miscUtils->getClientIp();
                $this->container->logger->debug('checkRememberMe -> start( userId = ' . $userId . ', token = ' . $token .
                                                ', ipAddress = ' . $ipAddress . ' )');

                try {
                    $query = $database->select(['user_pk', 'user_name', 'user_email_address', 'group_fk'])
                        ->from('Users')
                        ->where('user_pk', '=', $userId);
                    $stmt = $query->execute();
                    $userArr = $stmt->fetchall();

                    if (count($userArr) < 1) {
                        $this->container->logger->error('checkRememberMe -> Invalid cookie');
                        $this->restartSession();
                        return;
                    }

                    $user = $userArr[0];
                    $this->container->logger->debug('checkRememberMe -> user = ' . print_r($user, true));
                    $query = $database->select(['rememberme_pk', 'date'])
                        ->from('RememberMe')
                        ->where('user_fk', '=', $userId)
                        ->where('token', '=', $token, 'AND')
                        ->where('ip_address', '=', $ipAddress, 'AND');
                    $stmt = $query->execute();
                    $rememberMeArr = $stmt->fetchall();

                    if (count($rememberMeArr) < 1) {
                        $this->container->logger->error('checkRememberMe -> Invalid cookie');
                        $this->restartSession();
                        return;
                    }

                    $rememberMe = $rememberMeArr[0];
                    $this->container->logger->debug('checkRememberMe -> rememberMe = ' . print_r($rememberMe, true));
                    $then = date_create($rememberMe['date']);
                    $now = date_create();
                    $dateDiff = intval($this->container->formattingUtils->dateDifference($then, $now, '%a'));

                    if ($dateDiff > 30) {
                        $bytes = openssl_random_pseudo_bytes(32, $crypto_strong);
                        $token = bin2hex($bytes);
                        $query = $database->update()
                            ->table('RememberMe')
                            ->set(['token' => $token])
                            ->where('rememberme_pk', '=', $rememberMe['rememberme_pk']);
                        $database->beginTransaction();
                        $affectedRows = $query->execute();

                        if ($affectedRows === 1) {
                            $database->commit();
                        } else {
                            $database->rollBack();
                            $this->container->logger->debug('checkRememberMe -> Unable to update rememberMe token');
                            return;
                        }
                    }

                    $_SESSION['user']->id = $user['user_pk'];
                    $_SESSION['user']->username = $user['user_name'];
                    $_SESSION['user']->emailAddress = $user['user_email_address'];
                    $_SESSION['user']->group = $user['group_fk'];
                    $_SESSION['user']->token = $token;
                    $this->container->logger->debug('checkRememberMe -> _SESSION[user] = ' . print_r($_SESSION['user'], true));
                    setcookie('userId', $user['user_pk'], time() + $config['cookieLifetime'], '/');
                    setcookie('token', $token, time() + $config['cookieLifetime'], '/');
                } catch (Exception $ex) {
                    $this->container->logger->error('checkRememberMe -> ex = ' . $ex);
                    return;
                }
            } else {
                $this->container->logger->debug('checkRememberMe -> No rememberme cookie set');
                return;
            }
        }

        /**
         * Deauthorize a user
         *
         * @return array The status repsresented as an array
         */
        public function logout()
        {
            $database = $this->container->dataBase->PDO;

            try {
                $userId = $_SESSION['user']->id;
                $token = $_SESSION['user']->token;
                $ipAddress = $this->container->miscUtils->getClientIp();
                $this->container->logger->debug("MapPlatform 'MapPlatform\Core\Security\logout' data: " . print_r($data, true));
                $stmt = $database->delete()
                    ->from('RememberMe')
                    ->where('user_fk', '=', $userId)
                    ->where('token', '=', $token, 'AND')
                    ->where('ip_address', '=', $ipAddress, 'AND');
                $database->beginTransaction();
                $affectedRows = $stmt->execute();

                if ($affectedRows === 1) {
                    $database->commit();
                    $this->container->logger->error('logout -> Successfully logged out');
                    $this->destroySession();
                    return [
                        'status' => 'Success',
                        'message' => 'Successfully logged out, redirecting you to the homepage'
                    ];
                } else {
                    $database->rollBack();
                    $this->container->logger->error('logout -> Unable to logout');
                    $this->destroySession();
                    return [
                        'status' => 'Error',
                        'message' => 'Unable to logout'
                    ];
                }
            } catch (Exception $ex) {
                $this->container->logger->error('logout -> ex = ' . $ex);
                $this->destroySession();
                return [
                    'status' => 'Error',
                    'message' => 'Exception while trying to logout' . PHP_EOL . print_r($ex, true)
                ];
            }
        }
    }
