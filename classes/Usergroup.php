<?php
    class Usergroup extends Database {
        protected $variable_errorcode = 5000;
        protected $variable_defaultcolumn = "id";
        protected $variable_log = DEVELOPMENT;
        protected $variable_database = array(
            "id" => array (
                "type" => "int(11)",
                "primary" => true,
                "a_i" => true
            ),
            "name" => array (
                "type" => "varchar(255)"
            ),
            "permissions" => array (
                "type" => "longtext"
            )
        );

        protected $variable_defaultrows = array(
            array(
                "id" => "1",
                "name" => "Administrator",
                "permissions" => "*"
            )
        );

        protected $id, $name, $permissions;

        function __construct($object = NULL) {
            parent::__construct($object);
            parent::setupDatabase($this);
        }

        public function setId($id) {
            $this->id = Clear($id, "int");
        }
        
        public function setName($name, $skipexistcheck = false) {
            if (!empty($name)) {
                if (strlen($name) > 30) {
                    throw new Exception("Usergroup name cannot be more than 30 characters", $this->variable_errorcode + 7);
                }

                if($name !== NULL && !$skipexistcheck) {
                    $temp = new Usergroup();
                    $temp->getFromDB($name, "name");

                    if($temp->getId()) {
                        if($temp->getId() !== $this->id) {
                            throw new Exception('Usergroup name allready in use', $this->variable_errorcode + 43);
                        }
                    }
                }

                $this->name = $name;
            } else {
                throw new Exception('Usergroup name is missing', $this->variable_errorcode + 19);
            }
        }

        public function setPermissions($permissions) {
            $this->permissions = $this->FixEmpty($permissions);
        }

        public function hasPermission($permission) {
            $permission = Clear($permission);
           
            if(!empty($this->permissions) && !is_array($this->permissions)) {
                $permissions = array_map('trim', array_filter(explode(",", $this->permissions)));
            } else {
                $permissions = array();
            }

            return (in_array("*", $permissions) && !in_array("-" . $permission, $permissions)) || ((in_array($permission, $permissions) && !in_array("-" . $permission, $permissions)));
        }
        
        public function getId() {
            return $this->id;
        }
        
        public function getName() {
            return $this->name;
        }

        public function getPermissions() {
            return $this->permissions;
        }
    }