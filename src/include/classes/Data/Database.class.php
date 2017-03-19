<?php
    namespace Data;
    use PDO;

    class Database
    {
        private $DSN  = '';
        private $PDO  = null;
        private $STMT = null;

        public function __construct() {
            global $config;

            $this -> DSN = 'mysql:host=' . $config['db']['server'] . ';dbname=' . $config['db']['database'];
            $this -> PDO = new PDO($this -> DSN, $config['db']['username'], $config['db']['password']);

            $this -> PDO -> setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
            $this -> PDO -> setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this -> PDO -> setAttribute(PDO::ATTR_EMULATE_PREPARES,   False);
        }

        public function PrepareAndBind($Statement, $Params = null) {
            $this -> STMT = $this -> PDO -> prepare ($Statement);

            if ($Params != null && is_array($Params) && !Empty($Params)) {
                foreach ($Params as $K => $V) {
                    $this -> STMT -> bindParam(':' . $K, $Params [$K]);
                };
            };
        }

        public function Execute() {
            return $this -> STMT -> execute();
        }

        public function ExecuteAndFetch() {
            $this -> STMT -> execute();
            $Result = $this -> STMT -> fetch();

            return $Result;
        }

        public function ExecuteAndFetchAll() {
            $this -> STMT -> execute();
            $Result = $this -> STMT -> fetchAll();

            return $Result;
        }

        public function GetLastInsertId() {
            return $this -> PDO -> lastInsertId();
        }

        public function Clean() {
            global $config;

            $this -> STMT = null;
            $this -> PDO  = null;

            $this -> PDO = new PDO($this -> DSN, $config['db']['username'], $config['db']['password']);
            $this -> PDO -> setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
            $this -> PDO -> setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this -> PDO -> setAttribute(PDO::ATTR_EMULATE_PREPARES,   False);
        }

        public function Destroy() {
            $this -> STMT     = null;
            $this -> PDO      = null;
            $this -> DSN      = '';
        }
    }
