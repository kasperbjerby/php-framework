<?php
    class Database {
        private $variable_errorcode = 1000;
        private static $conn;

        function __construct($object = NULL) {
            if(!self::$conn) {
                $mysqli = @new mysqli("HOST", "USERNAME", "PASSWORD", "DB_NAME");

                if (@$mysqli->connect_errno) {
                    throw new Exception("Cant connect to DB!", $this->variable_errorcode);
                }

                if (!@$mysqli->set_charset("utf8")) {
                    throw new Exception("Cant set DB charset!", $this->variable_errorcode + 1);
                }

                if (!@$mysqli->query("SET time_zone = '+00:00'")) {
                    throw new Exception("Cant set DB timezone!", $this->variable_errorcode + 2);
                }

                self::$conn = $mysqli;
            }

            if (is_object($object) && $this->tableName($this) != "Database") {
                foreach ($object as $key => $value) {
                    $this->$key = $value;
                }
            }
        }

        public function FixEmpty($var, $reverse = false) {
            if (empty($var) && !is_int($var)) {
                $var = array(true);
            }

            if ($reverse && $var === array(true)) {
                $var = NULL;
            }

            return $var;
        }
        
        
        public function tableName($table) {
            if (is_object($table)) {
                $table = strtolower(get_class($table));
            }

            return $table;
        }

        public function DBClear($string, $trim = "string") {
            if ($trim) {
                $string = Clear($string, $trim);
            }

            return self::$conn->real_escape_string($string);
        }

        public function insert($values = NULL, $log = NULL, $reason = "Unknown", $table_name = NULL) {
            if (!is_array($values)) {
                $temparray = array();
                foreach ($this as $key => $value) {
                    if (substr($key, 0, 9) != "variable_" && $value != "") {
                        $temparray[$key] = $value;
                    }
                }

                $values = $temparray;
            }

            $table_name = (is_null($table_name)) ? $this->tableName($this) : $this->tableName($table_name);
            $log = (is_null($log)) ? $this->variable_log : $log;

            return $this->_insert($table_name, $values, $log, $reason);
        }

        public function update($values = NULL, $value = NULL, $name = NULL, $log = NULL, $reason = "Unknown", $table_name = NULL) {
            $temparray = $values;

            if (!is_array($values)) {
                $temparray = array();
                foreach ($this as $key => $val) {
                    if (substr($key, 0, 9) != "variable_") {
                        $temparray[$key] = $val;
                    }
                }
            }

            $name = (is_null($name)) ? $this->variable_defaultcolumn : $name;

            if (is_null($value)) {
                unset($temparray[$name]);
            }

            $table_name = (is_null($table_name)) ? $this->tableName($this) : $this->tableName($table_name);
            $log = (is_null($log)) ? $this->variable_log : $log;
            $value = (is_null($value)) ? $this->$name : $value;

            return $this->_update($table_name, $temparray, $value, $name, $log, $reason);
        }

        public function delete($value = NULL, $name = NULL, $log = NULL, $reason = "Unknown", $table_name = NULL) {
            $table_name = (is_null($table_name)) ? $this->tableName($this) : $this->tableName($table_name);
            $log = (is_null($log)) ? $this->variable_log : $log;
            $name = (is_null($name)) ? $this->variable_defaultcolumn : $name;
            $value = (is_null($value)) ? $this->$name : $value;

            return $this->_delete($table_name, $value, $name, $log, $reason);
        }

        public function getFromDB($value = NULL, $colum = NULL, $return = "auto", $limit_start = false, $limit_end = false, $order = false, $desc = false, $operator = "=", $select = NULL, $case = true, $table_name = NULL) {
            $table_name = ($table_name === NULL) ? $this->tableName($this) : $this->tableName($table_name);
            $colum = (is_null($colum) && !is_null($value)) ? $this->variable_defaultcolumn : $colum;

            $newreturn = $return;

            if ($return === "auto") {
                if ($value === false) {
                    throw new Exception('Value can not be empty when using "auto" as return', $this->variable_errorcode + 3);
                }

                $newreturn = "object";
            }

            if (is_null($select)) {
                foreach ($this as $key => $val) {
                    if (substr($key, 0, 9) != "variable_") {
                        $select[] = $key;
                    }
                }
            }

            $result = $this->_getFromDB($table_name, $colum, $value, $newreturn, $limit_start, $limit_end, $order, $desc, $operator, $case, $select);

            if ($return === "auto") {
                if (is_object($result)) {
                    foreach ($result as $key => $value) {
                        $this->$key = $value;
                    }

                    return true;
                } else {
                    return false;
                }
            } else {
                return $result;
            }
        }

        public function runSQL($sql, $return = "result", $log = false, $reason = "Unknown") {
            if ($result = self::$conn->query($sql)) {
                if ($log) {
                    $log = new Log();
                    $log->setReason($reason);
                    $log->setMessage($sql);
                    $log->setType("SQL_query");
                    $log->insert();
                }

                if ($return == "count") {
                    return $result->num_rows;
                } else {
                    if ($result->num_rows) {
                        if ($return == "check") {
                            return true;
                        } else {
                            if ($return == "object") {
                                return $result->fetch_object();
                            } elseif ($return == "array") {
                                return $result->fetch_array();
                            } elseif ($return == "row") {
                                return $result->fetch_row();
                            } elseif ($return == "assoc") {
                                return $result->fetch_assoc();
                            } else {
                                return $result;
                            }
                        }
                    } else {
                        return $result;
                    }
                }
            } else {
                if ($log) {
                    $log = new Log();
                    $log->setReason(self::$conn->error);
                    $log->setMessage($sql);
                    $log->setType("SQL_fail");
                    $log->insert();
                }

                throw new Exception("Failed to run SQL!", $this->variable_errorcode + 4);
            }
        }

        private function _insert($table_name, $fields_and_values, $log = false, $reason = "Unknown") {
            foreach ($fields_and_values as $field_name => $field_value) {
                $field_names .= $this->DBClear($field_name) . ", ";
                $field_values .= "'" . $this->DBClear($field_value) . "', ";
            }

            $field_names = rtrim($field_names, ', ');
            $field_values = rtrim($field_values, ', ');
            $sql = "INSERT INTO ".$this->DBClear($table_name)." (".$field_names.") VALUES (".$field_values.")";

            //echo $sql;
            
            if (self::$conn->query($sql)) {
                $id = self::$conn->insert_id;

                if ($log) {
                    $log = new Log();
                    $log->setReason($reason);
                    $log->setMessage($sql);
                    $log->setType("SQL_insert");
                    $log->insert();
                }

                if (is_numeric($id)) {
                    return $id;
                } else {
                    return true;
                }
            } else {
                if ($log) {
                    $log = new Log();
                    $log->setReason(self::$conn->error);
                    $log->setMessage($sql);
                    $log->setType("SQL_fail");
                    $log->insert();
                }

                throw new Exception("Failed to insert to DB!", $this->variable_errorcode + 5);
            }
        }

        private function _update($table_name, $to_be_updated, $record_identifyer_value, $record_identifyer_name, $log, $reason) {
            $to_be_updated = array_filter($to_be_updated, function($value) {
                return ($value || is_numeric($value));
            });

            foreach ($to_be_updated as $key => $val) {
                if (is_array($val) && count($val) == 1 && $val[0] === true) {
                    $to_be_updated[$key] = "";
                }
            }



            if (count($to_be_updated)) {
                foreach ($to_be_updated as $field_name => $field_value) {
                    $temp .= $this->DBClear($field_name) . " = '" . $this->DBClear($field_value) . "', ";
                }

                $temp = rtrim($temp, ', ');
                $sql = "UPDATE " . $this->DBClear($table_name) . " SET $temp WHERE " . $this->DBClear($record_identifyer_name) . " = '" . $this->DBClear($record_identifyer_value) . "'";

                //echo $sql;
                
                if ($result = self::$conn->query($sql)) {
                    if ($log) {
                        $log = new Log();
                        $log->setReason($reason);
                        $log->setMessage($sql);
                        $log->setType("SQL_update");
                        $log->insert();
                    }

                    $affected_rows = self::$conn->affected_rows;
                    if($affected_rows) {
                        return self::$conn->affected_rows;
                    } else {
                        return true;
                    }
                } else {
                    if ($log) {
                        $log = new Log();
                        $log->setReason(self::$conn->error);
                        $log->setMessage($sql);
                        $log->setType("SQL_fail");
                        $log->insert();
                    }

                    throw new Exception("Failed to update in DB!", $this->variable_errorcode + 6);
                }
            }
        }

        private function _delete($table_name, $record_identifyer_value, $record_identifyer_name, $log, $reason) {
            $sql = "DELETE FROM " . $this->DBClear($table_name) . " WHERE " . $this->DBClear($record_identifyer_name) . " = '" . $this->DBClear($record_identifyer_value) . "'";
            if ($result = self::$conn->query($sql)) {
                if ($log) {
                    $log = new Log();
                    $log->setReason($reason);
                    $log->setMessage($sql);
                    $log->setType("SQL_delete");
                    $log->insert();
                }

                return self::$conn->affected_rows;
            } else {
                if ($log) {
                    $log = new Log();
                    $log->setReason(self::$conn->error);
                    $log->setMessage($sql);
                    $log->setType("SQL_fail");
                    $log->insert();
                }

                throw new Exception("Failed to delete from DB!", $this->variable_errorcode + 7);
            }
        }

        private function _getFromDB($table_name, $colum, $value, $return, $limit_start, $limit_end, $order, $desc, $operator, $case, $select) {
            $sql = "SELECT ";

            if ($return == "count" || $return == "check") {
                if (!is_array($colum) && !is_null($colum)) {
                    $sql .= $this->DBClear($colum);
                } elseif (!is_array($value) && !is_null($value)) {
                    $sql .= $this->DBClear($value);
                } elseif (is_array($select) && count($select)) {
                    $sql .= implode(", ", $select);
                } else {
                    $sql .= "*";
                }
            } elseif (is_array($select) && count($select)) {
                $sql .= implode(", ", $select);
            } else {
                $sql .= "*";
            }

            $sql .= " FROM " . $this->DBClear($table_name);

            $casestart = "";
            $caseend = "";

            if(!$case) {
                $casestart = "LOWER(";
                $caseend = ")";
            }

            if (!is_null($colum)) {
                $sql .= " WHERE ";
                if (is_array($colum) && is_array($value)) {
                    // Colum array - Value array
                    if (count($colum) == count($value)) {
                        for ($i = 0; $i <= count($colum) - 1; $i++) {
                            $temp .= $casestart.$this->DBClear($colum[$i]).$caseend." ".$operator." ".$casestart."'".$this->DBClear($value[$i])."'".$caseend." && ";
                        }

                        $sql .= rtrim($temp, ' && ');
                    } else {
                        foreach ($colum as $where) {
                            foreach ($value as $value) {
                                $temp .= $casestart.$this->DBClear($where).$caseend." ".$operator." ".$casestart."'".$this->DBClear($value)."'".$caseend." || ";
                            }
                        }

                        $sql .= rtrim($temp, ' || ');
                    }
                } elseif (is_array($colum) && is_null($value)) {
                    // Colum array - Value nothing
                    foreach ($colum as $field_name => $field_value) {
                        $temp .= $casestart.$this->DBClear($field_name).$caseend." ".$operator." ".$casestart."'".$this->DBClear($field_value)."'".$caseend." && ";
                    }

                    $sql .= rtrim($temp, ' && ');
                } elseif (is_array($colum)) {
                    // Colum array - Value string
                    foreach ($colum as $field_value) {
                        $temp .= $casestart.$this->DBClear($field_value).$caseend." ".$operator." ".$casestart."'".$this->DBClear($value)."'".$caseend." || ";
                    }

                    $sql .= rtrim($temp, ' || ');
                } elseif (is_array($value)) {
                    // Colum string - Value array
                    foreach ($value as $field_value) {
                        $temp .= $casestart.$this->DBClear($colum).$caseend." ".$operator." ".$casestart."'".$this->DBClear($field_value)."'".$caseend." || ";
                    }

                    $sql .= rtrim($temp, ' || ');
                } else {
                    // Colum string - Value string
                    $sql .= $casestart.$this->DBClear($colum).$caseend." ".$operator." ".$casestart."'".$this->DBClear($value)."'".$caseend;
                }
            } elseif (!is_null($value)) {
                if (is_array($value)) {
                    // Colum nothing - Value array
                    $sql .= " WHERE ";

                    foreach ($value as $field_name => $field_value) {
                        $temp .= $casestart.$this->DBClear($field_name).$caseend." ".$operator." ".$casestart."'".$this->DBClear($field_value)."'".$caseend." && ";
                    }

                    $sql .= rtrim($temp, ' && ');
                }
            }

            if ($order != false) {
                if(is_array($order)) {
                    $temp_order_sql = "";
                    if(!empty($order)) {
                        $found = false;
                        foreach($order as $value) {
                            if(is_array($value) && !empty($value)) {
                                $found = true;
                                $temp_order_sql .= $value["name"];

                                if ($value["reverse"]) {
                                    $temp_order_sql .= " DESC, ";
                                } else {
                                    $temp_order_sql .= ", ";
                                }
                            }
                        }
                    }

                    if($temp_order_sql !== "") {
                        $sql .= " ORDER BY ".substr($temp_order_sql, 0, -2);
                    }
                } else {
                    $sql .= " ORDER BY ".$order;

                    if ($desc) {
                        $sql .= " DESC";
                    }
                }
            }

            if (is_numeric($limit_start)) {
                $sql .= " LIMIT " . $limit_start;
                if (is_numeric($limit_end)) {
                    $sql .= ", " . $limit_end;
                }
            }

            //echo "<pre>".$sql."</pre>";

            if ($result = self::$conn->query($sql)) {
                if ($return == "count") {
                    return $result->num_rows;
                } elseif ($return == "check") {
                    if ($result->num_rows) {
                        return true;
                    } else {
                        return false;
                    }
                } elseif ($return == "object") {
                    return $result->fetch_object();
                } elseif ($return == "array") {
                    return $result->fetch_array();
                } elseif ($return == "row") {
                    return $result->fetch_row();
                } elseif ($return == "assoc") {
                    return $result->fetch_assoc();
                } else {
                    return $result;
                }
            } else {
                throw new Exception("Failed to select in DB!", $this->variable_errorcode + 8);
            }
        }

        public function setupDatabase($class, $log = false) {
            $tablename = $this->tableName($class);
            
            $tabledata = $class->variable_database;
            $defaultrows = $class->variable_defaultrows;
            
            if(!DEVELOPMENT) {
                return true;
            }
            
            $tablefound = false;
            if ($this->runSQL("SHOW TABLES LIKE '".$tablename."'", "count", false) !== 0) {
                $tablefound = true;
            }

            if($tablefound && $this->variable_reset_dbtable) {
                if ($result = $this->runSQL("DROP TABLE ".$tablename, "result", $log, "Database table reset")) {
                    $tablefound = false;
                }
            }

            if(!$tablefound) {
                try {
                    $primary = NULL;
                    $unique = array();
                    $sql = "CREATE TABLE IF NOT EXISTS `".$tablename."` (";

                    foreach ($tabledata as $key => $array) {
                        $sql .= "`" . $key . "` " . $array["type"];

                        $null = "NOT NULL";
                        if (isset($array["null"])) {
                            if ($array["null"] == true) {
                                $null = "NULL";
                            }
                        }

                        if (isset($array["default"])) {
                            $sql .= " DEFAULT ";
                            $special = false;

                            if (isset($array["default_special"])) {
                                if ($array["default_special"] == true) {
                                    $special = true;
                                }
                            }

                            if ($special) {
                                $sql .= $array["default"];
                            } else {
                                $sql .= "'" . $array["default"] . "'";
                            }
                        }

                        if (isset($array["a_i"])) {
                            $sql .= " AUTO_INCREMENT";
                        }

                        $sql .= " " . $null . ",";

                        if (isset($array["primary"])) {
                            $primary = $key;
                        }

                        if (isset($array["unique"])) {
                            $unique[] = $key;
                        }
                    }

                    if (count($unique) > 0) {
                        foreach ($unique as $key) {
                            if ($key != $primary) {
                                $sql .= "UNIQUE KEY `" . $key . "` (`" . $key . "`),";
                            }
                        }
                    }

                    if (!is_null($primary)) {
                        $sql .= "PRIMARY KEY (`" . $primary . "`)";
                    } else {
                        $sql = substr($sql, 0, -1);
                    }
                    $sql .= ")";

                    $this->runSQL($sql, "result", $log, "Database setup");
                } catch (Exception $exc) {
                    throw new Exception("Database setup failed! A class must be setup wrong...", $this->variable_errorcode + 9);
                }

                if (is_array($defaultrows) && count($defaultrows) > 0) {
                    foreach ($defaultrows as $array) {
                        $this->_insert($tablename, $array, $log, "Database setup");
                    }
                }
            }
        }
    }
