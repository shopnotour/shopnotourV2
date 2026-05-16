<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{

    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
	protected $except = [
		//
//		'*/gateway_callback/*','/pay-via-ajax', '/success','/cancel','/fail','/ipn'
        '/pay-via-ajax', 'ssl/success','/cancel','/fail','/ipn','/pay'
	];
}
