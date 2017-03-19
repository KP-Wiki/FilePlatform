<?php
    namespace App;

    class Security
    {
        private $utils = null;

        public function __construct(&$utils) {
            $this -> utils = $utils;
        }

        /**
         ** PBKDF2 key derivation function as defined by RSA's
         ** PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
         **
         ** $algorithm - The hash algorithm to use. Recommended: SHA256
         ** $password - The password.
         ** $salt - A salt that is unique to the password.
         ** $count - Iteration count. Higher is better, but slower.
         **          Recommended: At least 1000.
         ** $keyLength - The length of the derived key in bytes.
         ** $rawOutput - If true, the key is returned in raw binary format.
         **               Hex encoded otherwise.
         ** Returns: A $keyLength-byte key derived from the password and salt.
         **
         ** Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
         **/
        public function pbkdf2($algorithm, $password, $salt, $count, $keyLength, $rawOutput = False) {
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

            if ($rawOutput) {
                return SubStr($output, 0, $keyLength);
            } else {
                return bin2hex(SubStr($output, 0, $keyLength));
            };
        }

        /**
         ** Compare the given password and salt with the given hash
         **
         ** $password - The raw password to compare with
         ** $salt - The salt to use for the comparison
         ** $hash - The hash to compare against
         **
         ** Returns: A boolean to indicate if the password with salt is equal to the hash
         **/
        private function isValidPassword($password, $salt, $hash) {
            global $logger;

            $logger -> log('isValidPassword -> start( salt = ' . $salt . ' )', Logger::DEBUG);
            $algorithm      = 'sha512';
            $iterationCount = 1024;
            $keyLength      = 1024;
            $outputRaw      = False;

            $hashVal = $this -> pbkdf2($algorithm, $password, $salt, $iterationCount, $keyLength, $outputRaw);

            // We can't use Levenshtein because our hashes are too large. :-(
            if ($hashVal === $hash)
                return True;

            return False;
        }

        private function isValidReCaptcha($reCaptchaResponse) {
            global $config, $logger;

            $logger -> log('isValidReCaptcha -> start( reCaptchaResponse = ' . $reCaptchaResponse . ' )', Logger::DEBUG);

            if (Empty($reCaptchaResponse))
                return False;

            $secret  = $config['reCaptcha']['secretKey'];
            $curl    = curl_init(); // Create curl resource

            curl_setopt_array($curl, Array(CURLOPT_RETURNTRANSFER => 1, // Return the server's response data as a string rather then a boolean
                                           CURLOPT_URL            => 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret .
                                                                     '&response=' . $reCaptchaResponse .
                                                                     '&remoteip=' . $_SERVER['REMOTE_ADDR'],
                                           CURLOPT_USERAGENT      => 'Maps_Platform/v' . APP_VERSION));
            $response = json_decode(curl_exec($curl), True);
            curl_close($curl); // Close curl resource to free up system resources
            $logger -> log('isValidReCaptcha -> response = ' . print_r($response, True), Logger::DEBUG);

            return $response['success'];
        }

        public function register(&$dbHandler) {
            global $config, $logger;

            $algorithm      = 'sha512';
            $iterationCount = 1024;
            $keyLength      = 1024;
            $outputRaw      = False;
            $defaultGroup   = 1;

            $username          = $this -> utils -> cleanInput($_POST['username']);
            $emailAddress      = $this -> utils -> cleanInput($_POST['emailAddress']);
            $password          = $this -> utils -> cleanInput($_POST['password']);
            $confirmPassword   = $this -> utils -> cleanInput($_POST['confirmPassword']);
            $reCaptchaResponse = $_POST['g-recaptcha-response']; // Don't clean this, it's provided by Google
            $logger -> log('Register -> start( username = ' . $username .
                           ', emailAddress = '. $emailAddress .
                           ', reCaptchaResponse = '. $reCaptchaResponse . ' )', Logger::DEBUG);

            $query = 'SELECT ' . PHP_EOL .
                     '    COUNT(*) AS user_count ' . PHP_EOL .
                     'FROM ' . PHP_EOL .
                     '    `Users` ' . PHP_EOL .
                     'WHERE ' . PHP_EOL .
                     '    `user_name` = :username OR' . PHP_EOL .
                     '    `user_email_address` = :emailaddress;';
            $dbHandler -> PrepareAndBind($query, Array('username'     => $username,
                                                       'emailaddress' => $emailAddress));
            $userCount               = $dbHandler -> ExecuteAndFetch();
            $LevenshteinForpasswords = Levenshtein ($password, $confirmPassword);
            $googleResponse          = $this -> isValidReCaptcha($reCaptchaResponse);
            $dbHandler -> Clean();

            $logger -> log('Register -> userCount = ' . print_r($userCount, True), Logger::DEBUG);
            $logger -> log('Register -> LevenshteinForpasswords = ' . $LevenshteinForpasswords, Logger::DEBUG);
            $logger -> log('Register -> googleResponse = ' . $googleResponse, Logger::DEBUG);

            if ($userCount['user_count'] != 0) {
                header('Refresh:5; url=/home');
                $this -> utils -> http_response_code(403);
                $logger -> log('Register -> User already exists', Logger::DEBUG);
                Die('User already exists');
            };

            if (($LevenshteinForpasswords !== 0) ||
                !$googleResponse) {
                header('Refresh:5; url=/home');
                $this -> utils -> http_response_code(400);
                $logger -> log('Register -> Passwords don\'t match', Logger::DEBUG);
                Die($this -> utils -> http_code_to_text(400));
            };

            $bytes   = openssl_random_pseudo_bytes(128, $crypto_strong);
            $salt    = bin2hex($bytes);
            $hashVal = $this -> pbkdf2($algorithm, $password, $salt, $iterationCount, $keyLength, $outputRaw);

            $insertQuery = 'INSERT INTO `Users` ' . PHP_EOL .
                           '    (`user_name`, `user_password`, `user_salt`, `user_email_address`, `group_fk`) ' . PHP_EOL .
                           'VALUES ' . PHP_EOL .
                           '    (:username, :password, :salt, :emailaddress, :groupid);';
            $dbHandler -> PrepareAndBind($insertQuery, Array('username'     => $username,
                                                             'password'     => $hashVal,
                                                             'salt'         => $salt,
                                                             'emailaddress' => $emailAddress,
                                                             'groupid'      => $defaultGroup));
            $dbHandler -> Execute();
            $insertId = $dbHandler -> GetLastInsertId();
            $dbHandler -> Clean();
            $logger -> log('Register -> googleResponse = ' . $googleResponse, Logger::DEBUG);

            if (Empty($insertId)) {
                header('Refresh:5; url=/home');
                $this -> utils -> http_response_code(500);
                $logger -> log('Register -> Unable to create user', Logger::DEBUG);
                Die('Unable to create user, please try again later');
            };

            $logger -> log('Register -> Registration successful', Logger::DEBUG);
            $this -> login($dbHandler);
        }

        public function login(&$dbHandler) {
            global $config, $logger;

            $_SESSION['user'] = (object)[];
            $username         = $this -> utils -> cleanInput($_POST['username']);
            $password         = $this -> utils -> cleanInput($_POST['password']);
            $ipAddress        = $this -> utils -> getClientIp();
            $logger -> log('Login -> start( username = ' . $username . ', ipAddress = ' . $ipAddress . ' )', Logger::DEBUG);

            $query1 = 'SET @username = :username;';
            $dbHandler -> PrepareAndBind ($query1, Array('username' => $username));
            $dbHandler -> Execute();

            $query2 = 'SELECT ' . PHP_EOL .
                      '    `user_pk`, ' . PHP_EOL .
                      '    `user_name`, ' . PHP_EOL .
                      '    `user_password`, ' . PHP_EOL .
                      '    `user_salt`, ' . PHP_EOL .
                      '    `user_email_address`, ' . PHP_EOL .
                      '    `group_fk` ' . PHP_EOL .
                      'FROM ' . PHP_EOL .
                      '    `Users` ' . PHP_EOL .
                      'WHERE ' . PHP_EOL .
                      '    `user_name` = @username OR ' . PHP_EOL .
                      '    `user_email_address` = @username;';
            $dbHandler -> PrepareAndBind ($query2);
            $user = $dbHandler -> ExecuteAndFetch();
            $logger -> log('Login -> user : ' . print_r($user, True), Logger::DEBUG);

            if (!isset($user['user_pk'])) {
                header('Refresh:5; url=/home');
                $this -> utils -> http_response_code(401);
                $logger -> log('Login -> Invalid credentials', Logger::DEBUG);
                Die('Invalid credentials, try again.');
            };

            $passwordCheck = $this -> isValidPassword($password, $user['user_salt'], $user['user_password']);

            if (!$passwordCheck) {
                header('Refresh:5; url=/home');
                $this -> utils -> http_response_code(401);
                $logger -> log('Login -> Invalid credentials', Logger::DEBUG);
                Die('Invalid credentials, try again.');
            };

            $bytes = openssl_random_pseudo_bytes(32, $crypto_strong);
            $token = bin2hex($bytes);

            $insertQuery = 'INSERT INTO `RememberMe` ' . PHP_EOL .
                           '    (`user_fk`, `token`, `ip_address`) ' . PHP_EOL .
                           'VALUES ' . PHP_EOL .
                           '    (:userid, :token, :ipaddr);';
            $dbHandler -> PrepareAndBind($insertQuery, Array('userid' => $user['user_pk'],
                                                             'token'  => $token,
                                                             'ipaddr' => $ipAddress));
            $dbHandler -> Execute();
            $insertId = $dbHandler -> GetLastInsertId();
            $logger -> log('Login -> insertId : ' . $insertId, Logger::DEBUG);
            $dbHandler -> Clean();

            if (Empty($insertId)) {
                header('Refresh:5; url=/home');
                $this -> utils -> http_response_code(500);
                $logger -> log('Login -> Unable to create rememberme token', Logger::DEBUG);
                Die('Unable to create rememberme token, please try again later');
            };

            $_SESSION['user'] -> id           = $user['user_pk'];
            $_SESSION['user'] -> username     = $user['user_name'];
            $_SESSION['user'] -> emailAddress = $user['user_email_address'];
            $_SESSION['user'] -> group        = $user['group_fk'];
            $logger -> log('Login -> _SESSION[user] : ' . print_r($_SESSION['user'], True), Logger::DEBUG);

            setcookie('userId', $user['user_pk'], time() + $config['security']['cookieLifetime'], '/');
            setcookie('token', $token, time() + $config['security']['cookieLifetime'], '/');

            header('Refresh:5; url=/home');
            $this -> utils -> http_response_code(200);
            $logger -> log('Login -> Login successful', Logger::DEBUG);
            Die('Login successful, redirecting you to the homepage');
        }

        public function checkRememberMe(&$dbHandler) {
            global $config, $logger;

            $_SESSION['user'] = (object)[];

            if (isset($_COOKIE['userId']) && isset($_COOKIE['token'])) {
                $userId    = $_COOKIE['userId'];
                $token     = $_COOKIE['token'];
                $ipAddress = $this -> utils -> getClientIp();
                $logger -> log('checkRememberMe -> start( userId = ' . $userId .
                               ', token = ' . $token .
                               ', ipAddress = ' . $ipAddress . ' )', Logger::DEBUG);

                $query1 = 'SELECT ' . PHP_EOL .
                          '    `user_pk`, ' . PHP_EOL .
                          '    `user_name`, ' . PHP_EOL .
                          '    `user_email_address`, ' . PHP_EOL .
                          '    `group_fk` ' . PHP_EOL .
                          'FROM ' . PHP_EOL .
                          '    `Users` ' . PHP_EOL .
                          'WHERE ' . PHP_EOL .
                          '    `user_pk` = :userid;';
                $dbHandler -> PrepareAndBind ($query1, Array('userid' => $userId));
                $user = $dbHandler -> ExecuteAndFetch();
                $dbHandler -> Clean();
                $logger -> log('checkRememberMe -> user = ' . print_r($user, True), Logger::DEBUG);

                $query2 = 'SELECT ' . PHP_EOL .
                         '    `rememberme_pk` ' . PHP_EOL .
                         'FROM ' . PHP_EOL .
                         '    `RememberMe` ' . PHP_EOL .
                         'WHERE ' . PHP_EOL .
                         '    `user_fk` = :userid AND ' . PHP_EOL .
                         '    `token` = :token AND ' . PHP_EOL .
                         '    `ip_address` = :ipaddress;';
                $dbHandler -> PrepareAndBind ($query2, Array('userid'    => $userId,
                                                             'token'     => $token,
                                                             'ipaddress' => $ipAddress));
                $rememberMe = $dbHandler -> ExecuteAndFetch();
                $dbHandler -> Clean();
                $logger -> log('checkRememberMe -> rememberMe = ' . print_r($rememberMe, True), Logger::DEBUG);

                if (!isset($user['user_pk']) || !isset($rememberMe['rememberme_pk'])) {
                    setcookie('userId', '', time() - 3600, '/');
                    setcookie('token', '', time() - 3600, '/');
                    $_SESSION['user'] -> group = 0;
                    $logger -> log('checkRememberMe -> User not found', Logger::DEBUG);
                    return;
                };

                $bytes = openssl_random_pseudo_bytes(32, $crypto_strong);
                $newToken = bin2hex($bytes);

                $query3 = 'UPDATE ' . PHP_EOL .
                          '    `RememberMe` ' . PHP_EOL .
                          'SET ' . PHP_EOL .
                          '    `token` = :token ' . PHP_EOL .
                          'WHERE ' . PHP_EOL .
                          '    `rememberme_pk` = :remembermeid;';
                $dbHandler -> PrepareAndBind ($query3, Array('token' => $newToken,
                                                             'remembermeid' => $rememberMe['rememberme_pk']));
                $dbHandler -> Execute();
                $dbHandler -> Clean();

                $_SESSION['user'] -> id           = $user['user_pk'];
                $_SESSION['user'] -> username     = $user['user_name'];
                $_SESSION['user'] -> emailAddress = $user['user_email_address'];
                $_SESSION['user'] -> group        = $user['group_fk'];
                $_SESSION['user'] -> token        = $newToken;
                $logger -> log('checkRememberMe -> _SESSION[user] = ' . print_r($_SESSION['user'], True), Logger::DEBUG);

                setcookie('userId', $user['user_pk'], time() + $config['security']['cookieLifetime'], '/');
                setcookie('token', $newToken, time() + $config['security']['cookieLifetime'], '/');
            } else {
                $_SESSION['user'] -> group = 0;
                $logger -> log('checkRememberMe -> No rememberme cookie set', Logger::DEBUG);
                return;
            };
        }

        public function logout(&$dbHandler) {
            global $logger;
            $userId    = $_SESSION['user'] -> id;
            $token     = $_SESSION['user'] -> token;
            $ipAddress = $this -> utils -> getClientIp();
            $logger -> log('logout -> start( userId = ' . $userId .
                           ', token = ' . $token .
                           ', ipAddress = ' . $ipAddress . ' )', Logger::DEBUG);

            $query = 'DELETE FROM ' .
                     '    `RememberMe`' . 
                     'WHERE ' .
                     '    `user_fk` = :userid AND ' .
                     '    `token` = :token AND ' .
                     '    `ip_address` = :ipaddr;';
            $dbHandler -> PrepareAndBind($query, Array('userid' => $userId,
                                                       'token'  => $token,
                                                       'ipaddr' => $ipAddress));
            $dbHandler -> Execute();

            // Unset the 'remember me' cookies
            setcookie('userId', '', time() - 3600, '/');
            setcookie('token', '', time() - 3600, '/');

            session_unset();   // Remove all session variables
            session_destroy(); // Destroy the session
            $logger -> log('logout -> End', Logger::DEBUG);

            header('Refresh:5; url=/home');
            Die('Successfully logged out, redirecting you to the homepage');
        }
    }
