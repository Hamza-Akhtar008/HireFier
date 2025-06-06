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

namespace App\Helpers\Common;

use Illuminate\Http\Response;
use Throwable;

class Cookie
{
	/**
	 * Set cookie
	 *
	 * @param string $name
	 * @param string|null $value
	 * @param int|null $expires
	 * @return void
	 */
	public static function set(string $name, ?string $value, ?int $expires = 0): void
	{
		$expires = !empty($expires) ? $expires : (int)config('settings.other.cookie_expiration');
		$path = !empty(config('session.path')) ? config('session.path') : '/'; // string
		$domain = !empty(config('session.domain')) ? config('session.domain') : getCookieDomain(); // string
		$secure = config('session.secure'); // bool
		$httpOnly = config('session.http_only'); // bool
		$sameSite = config('session.same_site'); // string
		
		try {
			$cookieObj = cookie()->make($name, $value, $expires, $path, $domain, $secure, $httpOnly, false, $sameSite);
			cookie()->queue($cookieObj);
		} catch (Throwable $e) {
			abort(400, $e->getMessage());
		}
	}
	
	/**
	 * Get cookie
	 *
	 * @param string $name
	 * @return array|string|null
	 */
	public static function get(string $name): array|string|null
	{
		return request()->cookie($name);
	}
	
	/**
	 * Check if cookie exists
	 *
	 * @param string $name
	 * @return bool
	 */
	public static function has(string $name): bool
	{
		return request()->hasCookie($name);
	}
	
	/**
	 * Delete cookie
	 *
	 * @param string $name
	 * @return void
	 */
	public static function forget(string $name): void
	{
		if (self::has($name)) {
			$path = !empty(config('session.path')) ? config('session.path') : '/'; // string
			$domain = !empty(config('session.domain')) ? config('session.domain') : getCookieDomain(); // string
			
			$cookieObj = cookie()->forget($name, $path, $domain);
			cookie()->queue($cookieObj);
		}
	}
	
	/**
	 * Delete all cookies (for current domain)
	 *
	 * @return void
	 */
	public static function forgetAll(): void
	{
		$cookies = request()->cookies->all();
		if (!empty($cookies)) {
			foreach ($cookies as $name => $value) {
				self::forget($name);
			}
		}
	}
	
	/**
	 * Send redirect and setting cookie in Laravel
	 *
	 * @param $url
	 * @param $cookie
	 * @param int $status
	 * @param array $headers
	 * @return \Illuminate\Http\Response
	 */
	public static function redirect($url, $cookie = null, int $status = 302, array $headers = []): Response
	{
		if (in_array($status, [301, 302])) {
			$status = 302;
		}
		
		$response = new Response('', $status);
		
		if (!empty($cookie)) {
			$response->withCookie($cookie);
		}
		if (!empty($headers)) {
			$response->withHeaders($headers);
		}
		$response->header('Location', $url);
		
		return $response;
	}
}
