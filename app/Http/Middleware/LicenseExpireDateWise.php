<?php

namespace App\Http\Middleware;

use App\Models\SubscriptionPlan;
use Carbon\Carbon;
use Closure;
use Examyou\RestAPI\Exceptions\ApiException;

class LicenseExpireDateWise
{

    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
