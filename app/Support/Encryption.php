<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;

class Encryption
{
    public static function encrypt($value)
    {
        return Crypt::encryptString($value);
    }

    public static function decrypt($value)
    {
        return Crypt::decryptString($value);
    }
}