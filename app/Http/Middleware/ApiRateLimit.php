<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;

class ApiRateLimit extends ThrottleRequests
{
    // 60 requests per minute per IP (customize as needed)
    protected $maxAttempts = 60;
    protected $decayMinutes = 1;
}