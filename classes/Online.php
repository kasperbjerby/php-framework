<?php
    class Online extends Database {
        protected $variable_errorcode = 4000;
        protected $variable_defaultcolumn = "uid";
        protected $variable_log = DEVELOPMENT;
        protected $variable_database = array(
            "id" => array (
                "type" => "int(11)",
                "primary" => true,
                "a_i" => true
            ),
            "uid" => array (
                "type" => "int(11)"
            ),
            "ip" => array (
                "type" => "varchar(255)"
            ),
            "lastactive" => array (
                "type" => "timestamp",
                "default" => "0000-00-00 00:00:00"
            ),
            "keeponline" => array (
                "type" => "timestamp",
                "default" => "0000-00-00 00:00:00"
            )
        );

        protected $variable_defaultrows = array();
        protected $id, $uid, $ip, $lastactive, $keeponline;

        function __construct($object = NULL) {
            parent::__construct($object);
            parent::setupDatabase($this);
        }

        public function setId($id) {
            $this->id = Clear($id, "int");
        }

        public function setUid($uid) {
            $this->uid = Clear($uid, "int");
        }

        public function setIp($ip) {
            $this->ip = $ip;
        }

        public function setLastactive($lastactive) {
            $lastactive = Clear($lastactive);

            if (isValidTimeStamp($lastactive)) {
                $this->lastactive = date("Y-m-d H:i:s", $lastactive);
            } else {
                $lastactive = strtotime($lastactive);
                if (isValidTimeStamp($lastactive)) {
                    $this->lastactive = date("Y-m-d H:i:s", $lastactive);
                } else {
                    throw new Exception("Lastactive value not allowed", $this->variable_errorcode + 20);
                }
            }
        }

        public function setKeeponline($keeponline) {
            $keeponline = Clear($keeponline);

            if (isValidTimeStamp($keeponline)) {
                $this->keeponline = date("Y-m-d H:i:s", $keeponline);
            } else {
                $keeponline = strtotime($keeponline);
                if (isValidTimeStamp($keeponline)) {
                    $this->keeponline = date("Y-m-d H:i:s", $keeponline);
                } else {
                    throw new Exception("Keeponline value not allowed", $this->variable_errorcode + 21);
                }
            }
        }

        public function getId() {
            return $this->id;
        }

        public function getUid() {
            return $this->uid;
        }

        function getIp() {
            return $this->ip;
        }

        public function getLastactive($raw = false, $failmsg = "Aldrig") {
            if($raw) {
                return $this->lastactive;
            } else {
                if($this->lastactive != "0000-00-00 00:00:00") {
                    $date = new DateTime($this->lastactive, new DateTimeZone('UTC'));
                    
                    return $date->format('H:i:s');
                } else {
                    return $failmsg;
                }
            }
        }

        public function getKeeponline() {
            return $this->keeponline;
        }

        public function getOnline($autocleanup = true) {
            if(!empty($this->uid)) {
                $user = new User();
                $user->getFromDB($this->uid);

                if((strtotime($this->keeponline)-strtotime("-600 seconds")) > 0) {
                    if((strtotime($this->lastactive)-strtotime("-600 seconds")) > 0) {
                        return 1;
                    }

                    return 2;
                } else {
                    if($autocleanup) {
                        try {
                            $this->delete();
                        } catch (Exception $e) {}
                    }

                    return 0;
                }
            }

            return false;
        }

        public function getOnlineText() {
            $online = $this->getOnline();

            if($online == 1) {
                return "<span class='online' title='Online - Last active: ".$this->getLastactive()."'>Online</span>";
            } elseif($online == 2) {
                return "<span class='away' title='Away - Last active: ".$this->getLastactive()."'>Away</span>";
            }

            return "<span class='offline'>Offline</span>";
        }
    }
