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

    use InvalidArgumentException;

    /**
     * Security
     *
     * @package    MapPlatform
     * @subpackage Core
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    class Security
    {
		/** @var \Slim\Container $container The framework container */
        private $container;

        /**
         * Security constructor.
         *
         * @param \Slim\Container The application controller.
         */
        public function __construct($aContainer) {
            $this->container = $aContainer;
        }

        /**
         * Initialize the user session
         */
        private function initSession() {
            $_SESSION['user'] = (object)[];
            $_SESSION['user']->group = 0;
        }

        /**
         * Destroy the user session
         */
        private function destroySession() {
            setcookie('userId', '', time() - 3600, '/');
            setcookie('token', '', time() - 3600, '/');
            session_unset();
            session_destroy();
        }

        /**
         * Restart the user session
         */
        private function restartSession() {
            $this->destroySession();
            session_start();
            $this->initSession();
        }

        /**
         * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
         * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
         *
         * @param string The hash algorithm to use. Recommended: SHA256
         * @param string The password.
         * @param string A salt that is unique to the password.
         * @param int Iteration count. Higher is better, but slower. Recommended: At least 1000.
         * @param int The length of the derived key in bytes.
         * @param boolean If true, the key is returned in raw binary format. Hex encoded otherwise.
         *
         * @return mixed A $keyLength-byte key derived from the password and salt.
         */
        private function pbkdf2($algorithm, $password, $salt, $count, $keyLength, $rawOutput = False) {
            $algorithm = strtolower($algorithm);

            if (!in_array($algorithm, hash_algos(), True))
                trigger_error('PBKDF2 ERROR: Invalid hash algorithm.', E_USER_ERROR);

            if ($count <= 0 || $keyLength <= 0)
                trigger_error('PBKDF2 ERROR: Invalid parameters.', E_USER_ERROR);

            if (function_exists('hash_pbkdf2')) {
                // The output length is in NIBBLES (4-bits) if $rawOutput is false!
                if (!$rawOutput)
                    $keyLength = $keyLength * 2;

                return hash_pbkdf2($algorithm, $password, $salt, $count, $keyLength, $rawOutput);
            };

            $hashLength = StrLen(hash($algorithm, "", True));
            $blockCount = ceil($keyLength / $hashLength);

            $output = "";

            for ($i = 1; $i <= $blockCount; $i++) {
                // $i encoded as 4 bytes, big endian.
                $last = $salt . pack('N', $i);
                // first iteration
                $last = $xorSum = hash_hmac($algorithm, $last, $password, True);

                // perform the other $count - 1 iterations
                for ($j = 1; $j < $count; $j++) {
                    $xorSum ^= ($last = hash_hmac($algorithm, $last, $password, True));
                };

                $output .= $xorSum;
            };

            return ($rawOutput ? SubStr($output, 0, $keyLength) : bin2hex(SubStr($output, 0, $keyLength)));
        }
		
		/**
         * Compare the given password and salt with the given hash
         *
         * @param string The raw password to compare with
         * @param string The salt to use for the comparison
         * @param string The hash to compare against
         *
         * @return boolean
         */
        public function isValidPassword($password, $salt, $hash) {
            $this->container->logger->debug('isValidPassword -> start( salt = ' . $salt . ' )');
            $algorithm      = 'sha512';
            $iterationCount = 1024;
            $keyLength      = 1024;
            $outputRaw      = False;
            $hashVal        = $this->pbkdf2($algorithm, $password, $salt, $iterationCount, $keyLength, $outputRaw);

            // We can't use Levenshtein because our hashes are too large. :-(
            return ($hashVal === $hash);
        }

        /**
         * Check validity of ReCaptcha response value
         *
         * @param string The response provided by Google
         *
         * @return boolean
         */
        private function isValidReCaptcha($reCaptchaResponse) {
            $this->container->logger->debug('isValidReCaptcha -> start( reCaptchaResponse = ' . $reCaptchaResponse . ' )');
            $config = $this->container->get('settings')['reCaptcha'];

            if (Empty($reCaptchaResponse))
                return False;

            $secret = $config['secretKey'];
            $curl   = curl_init(); // Create curl resource

            curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => 1, // Return the server's response data as a string rather then a boolean
                                           CURLOPT_URL            => 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret .
                                                                     '&response=' . $reCaptchaResponse .
                                                                     '&remoteip=' . $_SERVER['REMOTE_ADDR'],
                                           CURLOPT_USERAGENT      => 'Maps_Platform/v' . APP_VERSION));
            $response = json_decode(curl_exec($curl), True);
            curl_close($curl); // Close curl resource to free up system resources
            $this->container->logger->debug('isValidReCaptcha -> response = ' . print_r($response, True));

            return $response['success'];
        }

        /**
         * Check validity of ReCaptcha response value
         *
         * @param array POST values as an array
         *
         * @return array The status repsresented as an array
         */
        public function register($aDataArray) {
            $database       = $this->container->dataBase->PDO;
            $algorithm      = 'sha512';
            $iterationCount = 1024;
            $keyLength      = 1024;
            $outputRaw      = False;
            $defaultGroup   = 1;

            try {
                $username          = filter_var($aDataArray['username'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW || 
                                                                                                 FILTER_FLAG_STRIP_HIGH ||
                                                                                                 FILTER_FLAG_STRIP_BACKTICK);
                $emailAddress      = filter_var($aDataArray['emailAddress'], FILTER_SANITIZE_EMAIL);
                $password          = filter_var($aDataArray['password'], FILTER_UNSAFE_RAW);             // Don't clean this, passwords should be left untouched as they are hashed
                $confirmPassword   = filter_var($aDataArray['confirmPassword'], FILTER_UNSAFE_RAW);      // Don't clean this, passwords should be left untouched as they are hashed
                $reCaptchaResponse = filter_var($aDataArray['g-recaptcha-response'], FILTER_UNSAFE_RAW); // Don't clean this, it's provided by Google

                $this->container->logger->debug('Register -> start( username = ' . $username .
                                                ', emailAddress = '. $emailAddress .
                                                ', reCaptchaResponse = '. $reCaptchaResponse . ' )');

                $query = $database->select()
                                  ->count('*', 'user_count')
                                  ->from('Users')
                                  ->where('user_name', '=', $username, 'OR')
                                  ->where('user_email_address', '=', $emailAddress);
                $stmt = $query->execute();

                $userCount               = $stmt->fetch();
                $LevenshteinForpasswords = Levenshtein($password, $confirmPassword);
                $googleResponse          = $this->isValidReCaptcha($reCaptchaResponse);

                $this->container->logger->debug('Register -> userCount = ' . print_r($userCount, True) .
                                                ', LevenshteinForpasswords = ' . $LevenshteinForpasswords .
                                                ', googleResponse = ' . $googleResponse);

                if ($userCount['user_count'] != 0) {
                    $this->container->logger->debug('Register -> User already exists');

                    return [
                        'status' => 'Error',
                        'message' => 'User already exists'
                    ];
                };

                if (($LevenshteinForpasswords !== 0) || !$googleResponse) {
                    $this->container->logger->debug('Register -> Passwords don\'t match');

                    return [
                        'status' => 'Error',
                        'message' => 'Bad Request'
                    ];
                };

                $bytes   = openssl_random_pseudo_bytes(128, $crypto_strong);
                $salt    = bin2hex($bytes);
                $hashVal = $this->pbkdf2($algorithm, $password, $salt, $iterationCount, $keyLength, $outputRaw);

                $query = $database->insert(['user_name', 'user_password', 'user_salt', 'user_email_address', 'group_fk'])
                                  ->into('Users')
                                  ->values([$username, $hashVal, $salt, $emailAddress, $defaultGroup]);
                $database->beginTransaction();
                $insertID = $query->execute(True);

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
                };
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
        public function login($aDataArray) {
            $database = $this->container->dataBase->PDO;
            $config   = $this->container->get('settings')['security'];
            $this->initSession();

            try {
                $this->container->logger->debug(print_r($aDataArray, True));
                $username  = filter_var($aDataArray['username'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW || 
                                                                                         FILTER_FLAG_STRIP_HIGH ||
                                                                                         FILTER_FLAG_STRIP_BACKTICK);
                $password  = filter_var($aDataArray['password'], FILTER_DEFAULT);
                $ipAddress = $this->container->miscUtils->getClientIp();
                $this->container->logger->debug('Login -> start( username = ' . $username . ', ipAddress = ' . $ipAddress . ' )');

                $query   = $database->select(['user_pk', 'user_name', 'user_password', 'user_salt', 'user_email_address', 'group_fk'])
                                    ->from('Users')
                                    ->where('user_name', '=', $username)
                                    ->where('user_email_address', '=', $username, 'OR');
                $stmt    = $query->execute();
                $userArr = $stmt->fetchall();

                if (count($userArr) < 1) {
                    $this->container->logger->error('Login -> Invalid credentials');

                    return [
                        'status' => 'Error',
                        'message' => 'Invalid credentials, try again.'
                    ];
                };

                $user = $userArr[0];
                $this->container->logger->debug('Login -> user : ' . print_r($user, True));
                $passwordCheck = $this->isValidPassword($password, $user['user_salt'], $user['user_password']);

                if (!$passwordCheck) {
                    $this->container->logger->debug('Login -> Invalid credentials');

                    return [
                        'status' => 'Error',
                        'message' => 'Invalid credentials, try again.'
                    ];
                };

                $bytes = openssl_random_pseudo_bytes(32, $crypto_strong);
                $token = bin2hex($bytes);

                $query = $database->insert(['user_fk', 'token', 'ip_address'])
                                  ->into('RememberMe')
                                  ->values([$user['user_pk'], $token, $ipAddress]);
                $database->beginTransaction();
                $insertID = $query->execute(True);
                $this->container->logger->debug('Login -> insertId : ' . $insertID);

                if ($insertID <= 0) {
                    $database->rollBack();
                    $this->container->logger->debug('Login -> Unable to create remember-me token');

                    return [
                        'status' => 'Error',
                        'message' => 'Unable to authenticate user, please try again later.'
                    ];
                };

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
        public function checkRememberMe() {
            $database = $this->container->dataBase->PDO;
            $config   = $this->container->get('settings')['security'];
            $this->initSession();

            if (isset($_COOKIE['userId']) && isset($_COOKIE['token'])) {
                $userId = filter_input(INPUT_COOKIE, 'userId', FILTER_SANITIZE_NUMBER_INT);
                $token  = filter_input(INPUT_COOKIE, 'token', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW || 
                                                                                      FILTER_FLAG_STRIP_HIGH ||
                                                                                      FILTER_FLAG_STRIP_BACKTICK);
                $ipAddress = $this->container->miscUtils->getClientIp();
                $this->container->logger->debug('checkRememberMe -> start( userId = ' . $userId .
                                                ', token = ' . $token .
                                                ', ipAddress = ' . $ipAddress . ' )');

                try {
                    $query   = $database->select(['user_pk', 'user_name', 'user_email_address', 'group_fk'])
                                        ->from('Users')
                                        ->where('user_pk', '=', $userId);
                    $stmt    = $query->execute();
                    $userArr = $stmt->fetchall();

                    if (count($userArr) < 1) {
                        $this->container->logger->error('checkRememberMe -> Invalid cookie');
                        $this->restartSession();

                        return;
                    };

                    $user = $userArr[0];
                    $this->container->logger->debug('checkRememberMe -> user = ' . print_r($user, True));

                    $query         = $database->select(['rememberme_pk', 'date'])
                                              ->from('RememberMe')
                                              ->where('user_fk', '=', $userId)
                                              ->where('token', '=', $token, 'AND')
                                              ->where('ip_address', '=', $ipAddress, 'AND');
                    $stmt          = $query->execute();
                    $rememberMeArr = $stmt->fetchall();

                    if (count($rememberMeArr) < 1) {
                        $this->container->logger->error('checkRememberMe -> Invalid cookie');
                        $this->restartSession();

                        return;
                    };

                    $rememberMe = $rememberMeArr[0];
                    $this->container->logger->debug('checkRememberMe -> rememberMe = ' . print_r($rememberMe, True));

                    $now      = date('yyyy/mm/dd hh:ii:ss', time());
                    $dateDiff = intval($this->container->formattingUtils->dateDifference($rememberMe['date'], $now, '%a'));

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
                        };
                    };

                    $_SESSION['user']->id           = $user['user_pk'];
                    $_SESSION['user']->username     = $user['user_name'];
                    $_SESSION['user']->emailAddress = $user['user_email_address'];
                    $_SESSION['user']->group        = $user['group_fk'];
                    $_SESSION['user']->token        = $token;
                    $this->container->logger->debug('checkRememberMe -> _SESSION[user] = ' . print_r($_SESSION['user'], True));

                    setcookie('userId', $user['user_pk'], time() + $config['cookieLifetime'], '/');
                    setcookie('token', $newToken, time() + $config['cookieLifetime'], '/');
                } catch (Exception $ex) {
                    $this->container->logger->error('checkRememberMe -> ex = ' . $ex);

                    return;
                }
            } else {
                $this->container->logger->debug('checkRememberMe -> No rememberme cookie set');

                return;
            };
        }

        /**
         * Deauthorize a user
         *
         * @return array The status repsresented as an array
         */
        public function logout() {
            $database = $this->container->dataBase->PDO;
			
			try {
				$userId    = $_SESSION['user']->id;
				$token     = $_SESSION['user']->token;
				$ipAddress = $this->container->miscUtils->getClientIp();
				
				$this->container->logger->debug("MapPlatform 'MapPlatform\Core\Security\logout' data: " . print_r($data, True));
				$stmt = $database->delete()
                                 ->from('RememberMe')
                                 ->where('user_fk', '=', $userId)
                                 ->where('token', '=', $token, 'AND')
                                 ->where('ip_address', '=', $ipAddress, 'AND');
                $database->beginTransaction();
                $affectedRows = $stmt2->execute();

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
                };
            } catch (Exception $ex) {
                $this->container->logger->error('logout -> ex = ' . $ex);
                $this->destroySession();

				return [
					'status' => 'Error',
					'message' => 'Exception while trying to logout' . PHP_EOL . print_r($ex, True)
				];
            };
        }
    }
