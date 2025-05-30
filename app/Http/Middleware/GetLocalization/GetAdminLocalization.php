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

namespace App\Http\Middleware\GetLocalization;

use App\Helpers\Services\Localization\Country as CountryHelper;
use Illuminate\Support\Collection;

trait GetAdminLocalization
{
	/**
	 * Load Localization Data for the Admin Panel,
	 * When the Domain Mapping plugin is installed
	 *
	 * @return void
	 */
	private function loadAdminLocalizationData(): void
	{
		if (config('plugins.domainmapping.installed')) {
			if (!config('settings.domainmapping.share_session')) {
				// Country
				$country = $this->getCountryFromDomain();
				
				// Config: Country
				if ($country->isNotEmpty() && $country->has('code')) {
					config()->set('country.locale', config('app.locale'));
					config()->set('country.lang', []);
					
					if ($country->has('lang')) {
						$countryLang = $country->get('lang');
						if ($countryLang instanceof Collection) {
							if ($countryLang->has('code')) {
								config()->set('country.locale', $countryLang->get('code'));
							}
							config()->set('country.lang', $countryLang->toArray());
						}
					}
					
					config()->set('country.code', $country->get('code'));
					config()->set('country.icode', $country->get('icode'));
					config()->set('country.name', $country->get('name'));
					
					// Update the default country to prevent its removal
					config()->set('settings.localization.default_country_code', $country->get('code'));
					
					// Config: Domain Mapping Plugin
					applyDomainMappingConfig(config('country.code'));
				}
			}
		}
		
		// Apply the default app's language to the system (if its locale is available on the server)
		// Don't need to apply the app's locale for the Admin Panel.
		// Check the 'app/Providers/AppService/ConfigTrait.php' file for more information.
		systemLocale()->setLocale(config('appLang.locale'));
	}
	
	/**
	 * Get country from Domain
	 * Only when the Domain Mapping plugin is installed
	 *
	 * @return \Illuminate\Support\Collection
	 */
	private function getCountryFromDomain(): Collection
	{
		if (config('plugins.domainmapping.installed')) {
			if (!config('settings.domainmapping.share_session')) {
				$host = parse_url(url()->current(), PHP_URL_HOST);
				
				$domain = collect((array)config('domains'))->firstWhere('host', $host);
				$domain = ($domain instanceof Collection) ? $domain->toArray() : $domain;
				
				if (is_array($domain) && !empty($domain)) {
					if (!empty($domain['country_code'])) {
						return CountryHelper::getCountryInfo($domain['country_code']);
					}
				}
			}
		}
		
		return collect();
	}
}
