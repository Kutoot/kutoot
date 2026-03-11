<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Force all generated URLs to use HTTPS (fixes Mixed Content / Livewire behind proxy).
     * Runs early so Livewire and other packages get correct URLs.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appUrl = config('app.url');
        $forceHttps = app()->environment('production')
            || (bool) config('app.force_https', false)
            || ($appUrl && str_starts_with($appUrl, 'https://'));

        if ($forceHttps) {
            URL::forceScheme('https');
            $rootUrl = $appUrl ? rtrim($appUrl, '/') : null;
            if ($rootUrl && str_starts_with($rootUrl, 'http://')) {
                $rootUrl = 'https://' . substr($rootUrl, 7);
            }
            if ($rootUrl) {
                URL::forceRootUrl($rootUrl);
            }
            // Make request appear as HTTPS (Livewire may use $request->getScheme())
            $request->server->set('HTTPS', 'on');
            $request->server->set('SERVER_PORT', '443');
        }

        return $next($request);
    }
}
