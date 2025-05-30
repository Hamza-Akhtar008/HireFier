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

class TipsMessages
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
		// Exception for Install & Upgrade Routes
		if (isFromInstallOrUpgradeProcess()) {
			return $next($request);
		}
		
		if (!config('settings.other.show_tips_messages')) {
			return $next($request);
		}
		
		// SHOW MESSAGE... (About Login) If user not logged
		if (
			!auth()->check()
			&& request()->segment(1) !== null
			&& !str_contains(currentRouteAction(), '\Auth\\')
			&& !str_contains(currentRouteAction(), 'Post\CreateOrEdit\\')
			&& !str_contains(currentRouteAction(), 'Search\\')
			&& !str_contains(currentRouteAction(), 'SitemapController')
			&& !str_contains(currentRouteAction(), 'PageController@cms')
			&& !str_contains(currentRouteAction(), 'PageController@contact')
		) {
			$msg = 'login_for_faster_access_to_the_best_deals';
			$siteCountryInfo = t($msg, [
				'login_url'    => urlGen()->signIn(),
				'register_url' => urlGen()->signUp(),
			]);
			$paddingTopExists = true;
		}
		
		// SHOW MESSAGE... (About Location)
		// - If we know the user IP country
		// - and if user visiting another country's website
		// - and if Geolocation is activated
		$countryCode = config('country.code');
		$ipCountryCode = config('ipCountry.code');
		$ipCountryName = config('ipCountry.name');
		if (config('settings.localization.geoip_activation')) {
			if (!empty($ipCountryCode) && !empty($countryCode)) {
				if ($ipCountryCode != $countryCode) {
					$msg = 'app_is_also_available_in_your_country';
					$siteCountryInfo = t($msg, [
						'appName' => config('settings.app.name'),
						'country' => getColumnTranslation($ipCountryName),
						'url'     => dmUrl($ipCountryCode, '/', true, true),
					]);
					$paddingTopExists = true;
				}
			}
		}
		
		// Share vars to views
		if (isset($siteCountryInfo) && $siteCountryInfo != '') {
			view()->share('siteCountryInfo', $siteCountryInfo);
		}
		if (isset($paddingTopExists)) {
			// On search results page, the search form is always the first row
			if (str_contains(currentRouteAction(), 'Search\\')) {
				$paddingTopExists = false;
			}
			view()->share('paddingTopExists', $paddingTopExists);
		}
		
		return $next($request);
	}
}
