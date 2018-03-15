<?php
    class Log extends Database {
        protected $variable_errorcode = 2000;
        protected $variable_defaultcolumn = "id";
        protected $variable_database = array(
            "id" => array(
                "type" => "int(11)",
                "primary" => true,
                "a_i" => true
            ),
            "reason" => array(
                "type" => "varchar(255)"
            ),
            "message" => array(
                "type" => "text"
            ),
            "type" => array(
                "type" => "varchar(255)"
            ),
            "timestamp" => array(
                "type" => "timestamp",
                "default" => "0000-00-00 00:00:00"
            ),
            "ip" => array(
                "type" => "varchar(255)"
            ),
            "lang" => array(
                "type" => "varchar(255)"
            ),
            "hostname" => array(
                "type" => "varchar(255)"
            )
        );
        protected $variable_defaultrows = array();
        protected $id, $reason, $message, $type, $timestamp, $ip, $lang, $hostname;

        function __construct() {
            parent::__construct();
            parent::setupDatabase($this);

            $this->type = "info";
            $this->reason = "Unknown";
            $this->timestamp = date("Y-m-d H:i:s");
            $this->ip = getIP();

            $this->lang = getLang();
            $this->hostname = gethostbyaddr($this->ip);
        }

        public function setId($id) {
            $this->id = Clear($id, "int");
        }

        public function setMessage($message) {
            $this->message = $message;
        }

        public function setType($type) {
            $this->type = $type;
        }

        public function setReason($reason) {
            $this->reason = $reason;
        }

        public function setTimestamp($timestamp) {
            $this->timestamp = $timestamp;
        }

        public function setIp($ip) {
            $this->ip = $ip;
        }

        public function setLang($lang) {
            $this->lang = $lang;
        }

        public function setHostname($hostname) {
            $this->hostname = $hostname;
        }

        public function getId() {
            return $this->id;
        }

        public function getMessage() {
            return $this->message;
        }

        public function getType() {
            return $this->type;
        }

        public function getReason() {
            return $this->reason;
        }

        public function getTimestamp() {
            return $this->timestamp;
        }

        public function getIp() {
            return $this->ip;
        }

        public function getLang() {
            return $this->lang;
        }

        public function getHostname() {
            return $this->hostname;
        }
    }
