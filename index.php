<?php
    /*
     * Basic setup
     * Set error_reporting, utf-8, timezone to UTC
     * Include global functions and setup AutoLoader
     * Get page and underpages from url
     * Get information about current logged in user
     * Include aditional function files and all postandget files
    */

    define('DEVELOPMENT', true);
    
    // Set error reporting
        if(DEVELOPMENT) {
            ini_set('display_errors', 1);
            error_reporting(E_ERROR | E_WARNING | E_PARSE);
        } else {
            ini_set('display_errors', 0);
            error_reporting(NONE);
        }

    // Set utf-8, start session and set timezone to UTC
        header('Content-Type: text/html; charset=utf-8');
        session_start();
        date_default_timezone_set('UTC');

    // Set include path(s)
        define('APPLICATION_PATH', '/home/www/');

        $paths = array(APPLICATION_PATH . '/classes');
        set_include_path(implode(PATH_SEPARATOR, $paths));

    // Require global functions file
        require_once "global/functions.php";

    // Setup autoloader
        function AutoLoader($class) {
            require_once($class.'.php');
        }
        spl_autoload_register('AutoLoader');

    // Get page and under pages from url
        $str = rtrim(strtok($_SERVER['REQUEST_URI'], '?'), "/");
        $temp = explode("/", $str);
        unset($temp[0]);
        $parts = array_values($temp);

        if ($parts[0] != "") {
            $page = urldecode(htmlspecialchars($parts[0]));
        } else {
            $page = "frontpage";
        }

        $under = array();
        if (isset($parts[1]) && $parts[1] != "") {
            foreach ($parts as $key => $underpage) {
                if ($key != 0) {
                    $under[] = urldecode(htmlspecialchars($underpage));
                }
            }
        }

    // Try and fetch info about the logged in user, if it fails log them out
        $me = new User();
        if ($me->IsLoggedIn()) {
            if(!$me->getFromDB($_SESSION['uid'])) {
                $me->logout();
                header('Location: ' . $_SERVER['REQUEST_URI']);

                die();
            }
        }

    // Include page specific functions
        foreach (glob('pages/'.$page.'/functions/*.php', GLOB_BRACE) as $file) {
            require_once $file;
        }

    // Include page specific postandget scripts
        foreach (glob('pages/'.$page.'/postandget/*.php', GLOB_BRACE) as $file) {
            require_once $file;
        }

    // Include everything else in the global folder
        foreach (glob('global/*.php', GLOB_BRACE) as $file) {
            require_once $file;
        }

    // Set the page title based on the current page name
        if(empty($pagetitle)) {
            $pagetitle = ucfirst(strtolower(str_replace("_", " ", $page)));
            if (empty($pagetitle)) {
                $pagetitle = "Error";
            }
        }

    // Update logged in's users last active
        $me->updateLastactive();
    
    // Manually add css or script files here
        $_styles = array();
        $_scripts = array();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta name="google" content="notranslate" />
        <meta http-equiv="Content-Language" content="en_US" />

        <META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">

        <title>Framework - <?=$pagetitle?></title>

        <?php
            $disabledcaching = DEVELOPMENT;

            // Include all styles. Vendors first, then manually added once, then all global and last page specific
                foreach (glob('vendor/css/*.css', GLOB_BRACE) as $file) {
                    echo "<link href='/".$file.($disabledcaching ? "?".time() : "")."' rel='stylesheet'>\n";
                }

                foreach ($_styles as $file) {
                    if (substr($file, 0, 7) == "http://" || substr($file, 0, 8) == "https://") {
                        echo "<link href='".$file."' rel='stylesheet'>\n";
                    } else {
                        echo "<link href='/".$file."' rel='stylesheet'>\n";
                    }
                }

                foreach (glob('css/global/*.css', GLOB_BRACE) as $file) {
                    echo "<link href='/".$file.($disabledcaching ? "?".time() : "")."' rel='stylesheet'>\n";
                }

                foreach (glob('css/'.$page.'/*.css', GLOB_BRACE) as $file) {
                    echo "<link href='/".$file.($disabledcaching ? "?".time() : "")."' rel='stylesheet'>\n";
                }

                if (file_exists("css/".$page.".css")) {
                    echo "<link href='/css/".$page.".css".($disabledcaching ? "?".time() : "")."' rel='stylesheet'>\n";
                }

            // Include all scripts. Vendors first, then manually added once, then all global and last page specific
                foreach (glob('vendor/scripts/*.js', GLOB_BRACE) as $file) {
                    echo "<script src='/".$file.($disabledcaching ? "?".time() : "")."'></script>\n";
                }

                foreach ($_scripts as $file) {
                    if (substr($file, 0, 7) == "http://" || substr($file, 0, 8) == "https://") {
                        echo "<script src='".$file."'></script>\n";
                    } else {
                        echo "<script src='/".$file."'></script>\n";
                    }
                }

                foreach (glob('scripts/global/*.js', GLOB_BRACE) as $file) {
                    echo "<script src='/".$file.($disabledcaching ? "?".time() : "")."'></script>\n";
                }

                foreach (glob('scripts/'.$page.'/*.js', GLOB_BRACE) as $file) {
                    echo "<script src='/".$file.($disabledcaching ? "?".time() : "")."'></script>\n";
                }

                if (file_exists("scripts/".$page.".js")) {
                    echo "<script src='/scripts/".$page.".js".($disabledcaching ? "?".time() : "")."'></script>\n";
                }
        ?>
    </head>

    <body>
        <?php
            // Include top of page
                if (file_exists("layout/header.php")) {
                    require_once "layout/header.php";
                }
        
            // Include page content
                if (file_exists("pages/".$page."/".$under[0].".php")) {
                    require_once "pages/".$page."/".$under[0].".php";
                } elseif (file_exists("pages/".$page."/index.php")) {
                    require_once "pages/".$page."/index.php";
                } elseif (file_exists("pages/".$page.".php")) {
                    require_once "pages/".$page.".php";
                } else {
                    require_once "pages/404.php";
                }
            
            // Include bottom of page
                if (file_exists("layout/footer.php")) {
                    require_once "layout/footer.php";
                }
            
                
            // Include small script to ping the server every 30 seconds
                if($me->IsLoggedIn()) { ?>
                    <script>
                        setInterval(function() {
                            $.post("#", {
                                ping: true
                            }).done(function(data) {
                                if(data !== "true") {
                                    location.reload();
                                }
                            });
                        }, 30000);
                    </script> <?php
                }
        ?>
    </body>
</html>