<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*'  // This will exclude all routes that start with /api/
    ];

    /**
     * Determine if the request has a valid CSRF token.
     *
     * @param  Request  $request
     * @return bool
     */
    protected function tokensMatch($request)
    {
        // Get the token from the request
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

        if (!$token && $header = $request->header('X-XSRF-TOKEN')) {
            $token = $this->encrypter->decrypt($header, static::serialized());
        }

        return is_string($token) && hash_equals($request->session()->token(), $token);
    }
}
