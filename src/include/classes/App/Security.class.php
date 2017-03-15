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
            $algorithm      = 'sha512';
            $iterationCount = 1024;
            $keyLength      = 2048;
            $outputRaw      = False;

            $hashVal = $this -> pbkdf2($algorithm, $password, $salt, $iterationCount, $keyLength, $outputRaw);

            // Levenshtein compares two strings to determine how many characters need
            // to be changed to get the same string
            // This method is more secure then str_comp etc, also about 2x faster.
            $levenshteinForHashes = Levenshtein($hash, $hashVal);

            if ($levenshteinForHashes === 0)
                return True;

            return False;
        }

        public function login($username, $password, &$dbHandler) {
            $query1 = 'SET @username = :username;';
            $dbHandler -> PrepareAndBind ($query1, Array('username' => $username);
            $user = $dbHandler -> Execute();

            $query2 = 'SELECT ' .
                     '    `user_password`, ' .
                     '    `user_salt` ' .
                     'FROM ' .
                     '    `Users` ' .
                     'WHERE ' .
                     '    `user_name` = @username OR ' .
                     '    `user_email_address` = @username;';
            $dbHandler -> PrepareAndBind ($query2, Array('userName' => $username);
            $user = $dbHandler -> ExecuteAndFetch();

            // Maybe change this to something with more logic?
            return $this -> isValidPassword($password, $user['user_salt'], $user['user_password']);
        }

        public function register($username, $password, $emailAddress, &$dbHandler) {
            //TODO: Check if the user exists
            //TODO: Create the user in the DB
        }

        public function checkRememberMe($username, $token, &$dbHandler) {
            //TODO: Check if the user exists
            //TODO: Check if the token is correct
        }
    }
