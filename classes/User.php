<?php
    class User extends Database {
        protected $variable_errorcode = 3000;
        protected $variable_defaultcolumn = "id";
        protected $variable_log = DEVELOPMENT;
        protected $variable_database = array(
            "id" => array(
                "type" => "int(11)",
                "primary" => true,
                "a_i" => true
            ),
            "username" => array(
                "type" => "varchar(255)"
            ),
            "email" => array(
                "type" => "varchar(255)"
            ),
            "passwordhash" => array(
                "type" => "varchar(255)"
            ),
            "forgotId" => array(
                "type" => "varchar(255)"
            ),
            "steamid" => array(
                "type" => "varchar(255)"
            ),
            "ip" => array(
                "type" => "varchar(255)"
            ),
            "mustchangepassword" => array(
                "type" => "int(5)",
                "default" => "1"
            ),
            "disabled" => array(
                "type" => "int(5)",
                "default" => "0"
            ),
            "usergroup" => array(
                "type" => "int(5)",
                "default" => "2"
            ),
            "custompermissions" => array(
                "type" => "text"
            ),
            "lastlogin" => array(
                "type" => "timestamp",
                "default" => "0000-00-00 00:00:00"
            ),
            "updated" => array(
                "type" => "timestamp",
                "default" => "0000-00-00 00:00:00"
            ),
            "updated_by" => array(
                "type" => "int(11)",
                "default" => "0"
            ),
            "loginattempts" => array(
                "type" => "int(11)",
                "default" => "0"
            ),
            "created" => array(
                "type" => "timestamp",
                "default" => "CURRENT_TIMESTAMP",
                "default_special" => true
            )
        );
        protected $variable_defaultrows = array(
            array(
                'username' => "test",
                'passwordhash' => '$2y$10$dydXCysjZu2h5LhI002wIemZKfsv68Csxb6lSPr1Ci2W3s9oMwSMi',
                'usergroup' => "1"
            )
        );
        protected $variable_password = "";

        protected $id, $username, $email, $passwordhash, $forgotId, $steamid, $ip, $mustchangepassword, $disabled, $usergroup = 1, $custompermissions, $lastlogin, $updated, $updated_by, $loginattempts, $created;

        function __construct($object = NULL) {
            parent::__construct($object);
            parent::setupDatabase($this);
        }

        public function setId($id) {
            $this->id = Clear($id, "int");
        }

        public function setPassword($password, $passwordcheck = NULL) {
            if (!empty($password)) {
                if (!empty($passwordcheck)) {
                    if ($password == $passwordcheck) {
                        $this->variable_password = Encrypt($password);
                    } else {
                        throw new Exception("Password not equal", $this->variable_errorcode + 2);
                    }
                } else {
                    $this->variable_password = Encrypt($password);
                }
            } else {
                throw new Exception("Password missing");
            }
        }

        public function GeneratePasswordHash($logout = false) {
            if(!empty($this->variable_password)) {
                $this->passwordhash = password_hash($this->variable_password, PASSWORD_DEFAULT);
                $this->variable_password = "";

                if($logout) {
                    if($this->id === $me->getId()) {
                        $me->logout();
                    } else {
                        try {
                            $online = new Online();
                            $online->setUid($this->id);
                            $online->delete();
                        } catch (Exception $e) {}
                    }
                }

                return true;
            } else {
                throw new Exception("Password not set");
            }

            return false;
        }

        public function setUsername($username, $skipexistcheck = false) {
            if (!empty($username)) {
                if ($this->username != $username) {
                    if (strlen($name) > 30) {
                        throw new Exception('Username cannot be more than 30 characters', $this->variable_errorcode + 12);
                    }

                    if($username !== NULL && !$skipexistcheck) {
                        $temp = new User();
                        $temp->getFromDB($username, "username");

                        if($temp->getId()) {
                            if($temp->getId() !== $this->id) {
                                throw new Exception('Username allready in use', $this->variable_errorcode + 43);
                            }
                        }
                    }

                    $this->username = $username;
                }
            } else {
                throw new Exception('Username missing', $this->variable_errorcode + 19);
            }
        }

        public function setEmail($email, $emailcheck = NULL, $skipexistcheck = false) {
            if (!empty($email)) {
                if ($this->email != $email) {
                    if($email !== NULL && !$skipexistcheck) {
                        $temp = new User();
                        $temp->getFromDB($email, "email");

                        if($temp->getId()) {
                            if($temp->getId() !== $this->id) {
                                throw new Exception('Email allready in use', $this->variable_errorcode + 43);
                            }
                        }
                    }

                    if($emailcheck == NULL) {
                        $emailcheck = $email;
                    }

                    if ($emailcheck == $email) {
                        $this->email = $email;
                    } else {
                        throw new Exception('Email is not equal', $this->variable_errorcode + 11);
                    }
                }
            } else {
                throw new Exception('Email missing', $this->variable_errorcode + 19);
            }
        }

        function setIp($ip) {
            $this->ip = $ip;
        }

        public function setCreated($created) {
            if (isValidTimeStamp($created)) {
                $this->created = date("Y-m-d H:i:s", $created);
            } else {
                $created = strtotime($created);

                if (isValidTimeStamp($created)) {
                    $this->created = date("Y-m-d H:i:s", $created);
                } else {
                    throw new Exception("Created value not allowed", $this->variable_errorcode + 30);
                }
            }
        }

        public function setLastlogin($lastlogin) {
            if (isValidTimeStamp($lastlogin)) {
                $this->lastlogin = date("Y-m-d H:i:s", $lastlogin);
            } else {
                $lastlogin = strtotime($lastlogin);

                if (isValidTimeStamp($lastlogin)) {
                    $this->lastlogin = date("Y-m-d H:i:s", $lastlogin);
                } else {
                    throw new Exception("Lastlogin value not allowed", $this->variable_errorcode + 31);
                }
            }
        }

        function setUpdated($updated) {
            if (isValidTimeStamp($updated)) {
                $this->updated = date("Y-m-d H:i:s", $updated);
            } else {
                $updated = strtotime($updated);

                if (isValidTimeStamp($updated)) {
                    $this->updated = date("Y-m-d H:i:s", $updated);
                } else {
                    throw new Exception("Updated value not allowed", $this->variable_errorcode + 31);
                }
            }
        }

        function setUpdated_by($updated_by) {
            $this->updated_by = $updated_by;
        }

        public function setUsergroup($usergroup) {
            $this->usergroup = Clear($usergroup, "int");
        }

        public function setCustomPermissions($custompermissions) {
            $this->custompermissions = $this->FixEmpty($custompermissions);
        }

        public function setForgotId($id) {
            $this->forgotId = $this->FixEmpty($id);
        }

        public function GenerateForgotId($autoset = true) {
            $id = str_shuffle(MD5(microtime()));

            $user = new User();

            if ($user->getFromDB($id, "forgotId", "check")) {
                $id = GenerateForgotId(false);
            }

            if ($autoset) {
                $this->setForgotId($id);
            }

            return $id;
        }

        function setMustChangePassword($mustchangepassword) {
            $this->mustchangepassword = $this->FixEmpty(filter_var($mustchangepassword, FILTER_VALIDATE_BOOLEAN));
        }

        function setDisabled($disabled) {
            $disabled = filter_var($disabled, FILTER_VALIDATE_BOOLEAN);

            if($disabled) {
                try {
                    $online = new Online();
                    $online->setUid($_SESSION["uid"]);
                    $online->delete();
                } catch (Exception $e) { }
            }

            $this->disabled = $this->FixEmpty($disabled);
        }

        function setLoginattempts($loginattempts) {
            $this->loginattempts = $loginattempts;
        }

        public function getId() {
            return $this->id;
        }

        public function getUsername() {
            if ($this->id === 0) {
                return 'SYSTEM';
            }

            return $this->username;
        }

        public function getEmail() {
            if ($this->id === 0) {
                return 'SYSTEM';
            }

            return $this->email;
        }

        function getSteamid() {
            return $this->steamid;
        }

        public function getUserTheme() {
            return $this->usertheme;
        }

        function getIp() {
            return $this->ip;
        }

        public function getPasswordHash() {
            return $this->passwordhash;
        }

        function getUpdated($raw = false, $failmsg = "Never") {
            if($raw) {
                return $this->updated;
            } else {
                if($this->updated != "0000-00-00 00:00:00") {
                    $date = new DateTime($this->updated, new DateTimeZone('UTC'));

                    return $date->format('d/m/Y - H:i:s');
                } else {
                    return $failmsg;
                }
            }
        }

        function getUpdated_by() {
            return $this->updated_by;
        }

        public function getCreated($raw = false, $failmsg = "Never") {
            if($raw) {
                return $this->created;
            } else {
                if($this->created != "0000-00-00 00:00:00") {
                    $date = new DateTime($this->created, new DateTimeZone('UTC'));

                    return $date->format('d/m/Y - H:i:s');
                } else {
                    return $failmsg;
                }
            }
        }

        public function getLastlogin($raw = false, $failmsg = "Never") {
            if($raw) {
                return $this->lastlogin;
            } else {
                if($this->lastlogin != "0000-00-00 00:00:00") {
                    $date = new DateTime($this->lastlogin, new DateTimeZone('UTC'));

                    return $date->format('d/m/Y - H:i:s');
                } else {
                    return $failmsg;
                }
            }
        }

        public function getUsergroup() {
            return $this->usergroup;
        }

        public function getUsergroupName() {
            $usergroup = new Usergroup();
            $usergroup->getFromDB($this->usergroup);

            return $usergroup->getName();
        }

        public function getCustomPermissions() {
            return $this->FixEmpty($this->custompermissions, true);
        }

        public function hasPermission($permission, $customonly = false) {
            $permission = Clear($permission);

            if ($customonly === true) {
                $permissions = array_map('trim', array_filter(explode(",", $this->custompermissions)));

                return (in_array($permission, $permissions) && !in_array("-" . $permission, $permissions));
            }

            $usergroup = new Usergroup();
            $usergroup->getFromDB($this->usergroup);

            if($usergroup->getPermissions()) {
                $permissions = array_map('trim', explode(",", $usergroup->getPermissions()));
            } else {
                $permissions = array();
            }
            foreach(explode(",", $this->custompermissions) as $tempperm) {
                if($tempperm != "") {
                    $tempperm = trim($tempperm);

                    if(substr($tempperm, 0, 1) == "-") {
                        if(in_array(substr($tempperm, 1), $permissions)) {
                            if(($key = array_search(substr($tempperm, 1), $permissions)) !== false) {
                                unset($permissions[$key]);
                            }
                        }
                    } else {
                        if(in_array("-".$tempperm, $permissions)) {
                            if(($key = array_search("-".$tempperm, $permissions)) !== false) {
                                unset($permissions[$key]);
                            }
                        }
                    }

                    if(!in_array($tempperm, $permissions)) {
                        $permissions[] = $tempperm;
                    }
                }
            }

            return ((in_array("*", $permissions) && !in_array("-" . $permission, $permissions)) || (in_array($permission, $permissions) && !in_array("-".$permission, $permissions)));
        }

        public function getForgotId() {
            return $this->FixEmpty($this->forgotId, true);
        }

        public function MustChangePassword() {
            $mustchangepassword = $this->mustchangepassword;
            if(is_array($mustchangepassword)) {
                $mustchangepassword = false;
            }

            if($mustchangepassword) {
                return true;
            } else {
                return false;
            }
        }

        public function IsDisabled() {
            $disabled = $this->disabled;
            if(is_array($disabled)) {
                $disabled = false;
            }

            if($disabled) {
                return true;
            } else {
                return false;
            }
        }

        function getLoginattempts() {
            return $this->loginattempts;
        }

        public function IsLoggedIn() {
            return isset($_SESSION['uid']);
        }

        public function login($reason = "Unknown") {
            $username = $this->username;
            $password = $this->variable_password;

            if (isset($username) && !empty($username) && isset($password) && !empty($password)) {
                if ($result = parent::getFromDB(NULL, array("username" => $username), "result")) {
                    if ($result->num_rows > 0) {
                        $user = new User($result->fetch_object());

                        if(!$user->IsDisabled()) {
                            if(password_verify($password, $user->getPasswordHash())) {
                                $_SESSION['uid'] = $user->getId();
                                $_SESSION['rememberme'] = true;

                                if(DEVELOPMENT) {
                                    try {
                                        $log = new Log();
                                        $log->setReason($reason);
                                        $log->setMessage("User logged in from: " . $_SERVER['HTTP_REFERER']);
                                        $log->setType("user_login");
                                        $log->insert();
                                    } catch (Exception $e) { }
                                }

                                $this->getFromDB($_SESSION["uid"]);
                                $this->setLastlogin(time());
                                $this->setLoginattempts(0);
                                $this->setIp(getIp());
                                $this->update(NULL, NULL, NULL, false);

                                try {
                                    $online = new Online();
                                    $result = $online->getFromDB($_SESSION["uid"]);

                                    if ($result->num_rows > 0) {
                                        $online->setKeeponline(time());
                                        $online->setLastactive(time());
                                        $online->update();
                                    } else {
                                        $online->setUid($_SESSION["uid"]);
                                        $online->setKeeponline(time());
                                        $online->setLastactive(time());
                                        $online->insert();
                                    }
                                } catch (Exception $e) { }
                            } else {
                                try {
                                    $user->setLoginattempts($user->getLoginattempts() + 1);
                                    $user->update();
                                } catch (Exception $ex) {}

                                if(DEVELOPMENT) {
                                    try {
                                        $log = new Log();
                                        $log->setReason($reason);
                                        $log->setMessage('Attempted login with wrong password: ' . $username);
                                        $log->setType("fail_login");
                                        $log->insert();
                                    } catch (Exception $e) { }
                                }

                                throw new Exception("Wrong username or password!", $this->variable_errorcode + 44);
                            }
                        } else {
                            throw new Exception("Your account has been disabled!", $this->variable_errorcode + 16);
                        }
                    } else {
                        if(DEVELOPMENT) {
                            try {
                                $log = new Log();
                                $log->setReason($reason);
                                $log->setMessage('Attempted login not existing user: ' . $username);
                                $log->setType("fail_login");
                                $log->insert();
                            } catch (Exception $e) { }
                        }

                        throw new Exception("Wrong username or password!", $this->variable_errorcode + 16);
                    }
                } else {
                    throw new Exception("Cant connect to DB!", $this->variable_errorcode + 1);
                }
            } else {
                throw new Exception("Username or password missing!", $this->variable_errorcode + 17);
            }

            return true;
        }

        public function logout($reason = "Unknown") {
            if (!isset($_SESSION["uid"])) {
                return false;
            }

            if(DEVELOPMENT) {
                try {
                    $log = new Log();
                    $log->setReason($reason);
                    $log->setMessage("User logged out from: " . $_SERVER['HTTP_REFERER']);
                    $log->setType("user_logout");
                    $log->insert();
                } catch (Exception $e) { }
            }

            try {
                $online = new Online();
                $online->setUid($_SESSION["uid"]);
                $online->delete();
            } catch (Exception $e) { }

            unset($_SESSION["uid"]);
            unset($_SESSION["rememberme"]);

            return true;
        }
        
        public function updateLastactive() {
            if($this->IsLoggedIn()) {
                try {
                    $this->setIp(getIP());
                    $this->update();
                } catch (Exception $ex) {}

                try {
                    $online = new Online();
                    $online->getFromDB($this->getId());

                    $offline = false;
                    $doinsert = false;
                    if ($online->getId()) {
                        if($online->getOnline(false) === 0) {
                            if($_SESSION['rememberme']) {
                                try {
                                    $this->setLastlogin(time());
                                    $this->update();
                                } catch (Exception $e) {}
                            } else {
                                $online->delete();

                                $offline = true;
                            }
                        }
                    } else {
                        if($_SESSION['rememberme']) {
                            $online->setUid($this->getId());
                            $doinsert = true;
                        } else {
                            $offline = true;
                        }
                    }

                    if($offline) {
                        $this->logout('Logout - User timeout');

                        header('Location: '.$_SERVER['REQUEST_URI']);

                        die();
                    } else {
                        $online->setLastactive(time());
                        $online->setKeeponline(time());

                        if($doinsert) {
                            $online->insert();
                        } else {
                            $online->update();
                        }
                    }
                } catch (Exception $ex) { }
            }
        }
    }
