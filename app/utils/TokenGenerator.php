<?php

namespace App\Utils;


class TokenGenerator {

    public static function generateToken($length = 32) {
        // Generate a random string of the specified length
        return bin2hex(random_bytes($length));
    }
}
