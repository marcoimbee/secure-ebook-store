<?php

namespace App\Utils;


class AuthUtils {
    
    public static function redirectIfUserLogged() {
        if(isset($_SESSION["user_id"])) {
            header("Location: /");
            exit;
        }
    }
}
