<?php

namespace App\Service;

class StringUtils {

    public function capitalize($str) {
        return ucfirst(mb_strtolower($str));
    }


}