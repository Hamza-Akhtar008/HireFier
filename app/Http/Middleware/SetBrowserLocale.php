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

use App\Helpers\Services\Localization\Language;
use App\Models\Language as LanguageModel;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class SetBrowserLocale
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return \Illuminate\Http\RedirectResponse|mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		// Exception for Install & Upgrade Routes
		if (isFromInstallOrUpgradeProcess()) {
			return $next($request);
		}
		
		// Exception for Bots & Admin panel
		$crawler = new CrawlerDetect();
		if ($crawler->isCrawler() || isAdminPanel()) {
			return $next($request);
		}
		
		// Detect the user's browser language (If the option is activated in the system)
		if (config('settings.localization.auto_detect_language') == 'from_browser') {
			$cacheExpiration = config('settings.optimization.cache_expiration');
			
			if (!session()->has('browserLangCode')) {
				
				// Get the user's browser language
				$acceptLanguage = request()->server('HTTP_ACCEPT_LANGUAGE');
				$acceptLanguageTab = explode(',', $acceptLanguage);
				$langTab = [];
				if (!empty($acceptLanguageTab)) {
					foreach ($acceptLanguageTab as $key => $value) {
						$tmp = explode(';', $value);
						if (empty($tmp)) continue;
						
						if (isset($tmp[0]) && isset($tmp[1])) {
							$q = str_replace('q=', '', $tmp[1]);
							$langTab[$value] = ['code' => $tmp[0], 'q' => (double)$q];
						} else {
							$langTab[$value] = ['code' => $tmp[0], 'q' => 1];
						}
					}
				}
				
				// Get the user's country info (by the user's IP address) \w the country's language
				try {
					$country = Language::getCountryFromIP();
				} catch (Exception $e) {
					$country = collect();
				}
				
				// Get the country's language
				$countryLang = collect();
				if ($country->isNotEmpty() && $country->has('lang')) {
					$countryLang = $country->get('lang');
					$countryLang = ($countryLang instanceof Collection) ? $countryLang : collect();
				}
				
				// Search the default language (Intersection Browser & User's Country language OR First Browser language)
				// Prevent to always select "en" or "en-US" as First Browser Language Code
				$browserLangCode = '';
				if (!empty($langTab)) {
					foreach ($langTab as $value) {
						if ($countryLang->isNotEmpty() && $countryLang->has('code')) {
							if ($value['code'] == $countryLang->get('code')) {
								$browserLangCode = $value['code'];
								break;
							}
							if (str_contains($value['code'], $countryLang->get('code'))) {
								$browserLangCode = substr($value['code'], 0, 2);
								break;
							}
						} else {
							if ($browserLangCode == '') {
								$browserLangCode = substr($value['code'], 0, 2);
							}
						}
					}
				}
				
				// Check if the language is available in the system
				if ($browserLangCode != '') {
					// Get the Language details
					if ($countryLang->has('code') && $countryLang->get('code') == $browserLangCode) {
						$isAvailableLang = $country->get('lang');
					} else {
						try {
							$cacheId = 'language.' . $browserLangCode;
							$isAvailableLang = cache()->remember($cacheId, $cacheExpiration, function () use ($browserLangCode) {
								return LanguageModel::where('code', '=', $browserLangCode)->first();
							});
						} catch (Exception $e) {
							$isAvailableLang = [];
						}
						$isAvailableLang = collect($isAvailableLang);
					}
					
					if ($isAvailableLang->isNotEmpty()) {
						// If language found and is available in the system,
						// And if the browser language redirection has not been done yet,
						// Save it in session or in cookie and select it by making a redirect to the homepage (with the language code)
						if ($isAvailableLang->has('code')) {
							$langCode = $isAvailableLang->get('code');
							if (!request()->filled('bl')) {
								session()->put('browserLangCode', $langCode);
								
								// If the browser language is different to the system language,
								// Make a redirection for language auto-selection
								if ($browserLangCode != config('app.locale')) {
									$queryString = request()->getQueryString();
									$queryString = !empty($queryString) ? $queryString . '&bl=1' : 'bl=1';
									
									$url = url('locale/' . $langCode);
									$url = $url . '?' . $queryString;
									
									return redirect()->to($url)->withHeaders(config('larapen.core.noCacheHeaders'));
								}
							}
						}
					}
				}
				
			}
		}
		
		return $next($request);
	}
}
