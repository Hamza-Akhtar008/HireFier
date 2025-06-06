<?php
/*
 * JobClass - Job Board Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com/jobclass
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NoHttpCache
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		$response = $next($request);
		
		$headers = $this->getNoCacheHeaders();
		
		if (!empty($headers)) {
			foreach ($headers as $key => $value) {
				$response->headers->set($key, $value);
			}
		}
		
		return $response;
	}
	
	/**
	 * Get No Cache Headers
	 *
	 * @return string[]
	 */
	private function getNoCacheHeaders(): array
	{
		// 'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
		return [
			'Cache-Control' => 'no-store, no-cache, must-revalidate', // HTTP 1.1.
			'Pragma'        => 'no-cache', // HTTP 1.0.
			'Expires'       => 'Sun, 02 Jan 1990 05:00:00 GMT', // Proxies. (Date in the past)
			'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT',
		];
	}
}
