<?php
    if(isset($_POST["ping"])) {
        if($me->IsLoggedIn()) {
            try {
                $online = new Online();
                $online->getFromDB($me->getId());

                if ($online->getId()) {
                    $online->setKeeponline(time());
                    $online->update();
                    
                    die("true");
                } else {
                    $me->logout("Timeout");
                }
            } catch (Exception $ex) { }
        }
        
        die();
    }