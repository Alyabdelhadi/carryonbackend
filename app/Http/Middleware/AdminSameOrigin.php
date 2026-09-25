<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CSRF guard for the admin's state-changing GET links (…/delete/{id},
 * …Status?id=, parcel_order/notify, userVerification). A link on another
 * site would otherwise act with the admin's session cookie. The browser
 * says where a request came from (Sec-Fetch-Site, else Referer); anything
 * that is not this site is refused.
 */
class AdminSameOrigin
{
    private const ACTION = '#(^|/)(delete|notify)(/|$)|Status$|status$|^userVerification$#';

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && preg_match(self::ACTION, $request->path()) && !$this->fromThisSite($request)) {
            abort(403, 'Open this action from the admin panel.');
        }
        return $next($request);
    }

    private function fromThisSite(Request $request): bool
    {
        $site = $request->headers->get('Sec-Fetch-Site');
        if ($site !== null) {
            // "none": typed or bookmarked by the admin themselves
            return in_array($site, ['same-origin', 'none'], true);
        }
        $referer = $request->headers->get('referer');
        return $referer !== null && parse_url($referer, PHP_URL_HOST) === $request->getHost();
    }
}
