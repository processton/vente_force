<?php

namespace Crater\Http\Middleware;

use Closure;
use Crater\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CompanyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('company', Company::first()->id);

        return $next($request);
    }
}
