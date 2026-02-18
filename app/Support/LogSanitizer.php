<?php

namespace App\Support;

class LogSanitizer
{
    public static function sanitize($data)
    {
        // Remove or mask sensitive fields
        if (is_array($data)) {
            foreach (["session_uuid", "fingerprint", "api_key"] as $key) {
                if (isset($data[$key])) {
                    $data[$key] = '[REDACTED]';
                }
            }
        }
        return $data;
    }
}