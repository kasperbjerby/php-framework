<?php
    if(isset($_POST["login"])) {
        if(!$me->IsLoggedIn()) {
            $username = $_POST["username"];
            $password = $_POST["password"];

            try {
                $me->setUsername($username, true);
                $me->setPassword($password, true);
                
                if($me->login()) {
                    die("true");
                }
            } catch (Exception $ex) {
                die($ex->getMessage());
            }
        } else {
            if($me->logout()) {
                die("false");
            }
        }
        
        die("Unknown error");
    }