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

namespace App\Http\Controllers\Web\Setup\Update\Traits;

use App\Helpers\Common\PhpArrayFile;
use App\Models\Setting;
use Throwable;

trait RoutesTrait
{
	/**
	 * (Try to) Sync. the multi-country URLs with the dynamics routes
	 */
	private function syncMultiCountryUrlsAndRoutes()
	{
		// Get the SEO settings
		$seoSetting = Setting::where('key', 'seo')->first();
		if (empty($seoSetting)) {
			return;
		}
		
		if (!is_array($seoSetting->value)) {
			return;
		}
		
		$seoSettingValue = $seoSetting->value;
		
		// Check & update the 'multi_country_urls' value from 'config/routes.php' file
		$dynamicRoutesIsForMultiCountryUrl = (
			str_starts_with(config('routes.search'), '{countryCode}')
			|| str_starts_with(config('routes.searchPostsByCat'), '{countryCode}')
			|| str_starts_with(config('routes.searchPostsByCity'), '{countryCode}')
		);
		$multiCountryUrls = ($dynamicRoutesIsForMultiCountryUrl) ? '1' : '0';
		
		if (!isset($seoSettingValue['multi_country_urls'])) {
			$seoSettingValue['multi_country_urls'] = '0';
		}
		
		if ($seoSettingValue['multi_country_urls'] != $multiCountryUrls) {
			$seoSettingValue['multi_country_urls'] = $multiCountryUrls;
			
			$seoSetting->value = $seoSettingValue;
			$seoSetting->save();
		}
		
		// Check & update the 'config/routes.php' file that has been updated during upgrade process
		if (isset($seoSettingValue['listing_permalink']) && !empty($seoSettingValue['listing_permalink'])) {
			$settingsSeoListingPermalink = $seoSettingValue['listing_permalink'] . ($seoSettingValue['listing_permalink_ext'] ?? '');
			if ($settingsSeoListingPermalink != config('routes.post')) {
				try {
					config()->set('settings.seo.listing_permalink', $seoSettingValue['listing_permalink']);
					config()->set('settings.seo.listing_permalink_ext', ($seoSettingValue['listing_permalink_ext'] ?? null));
					
					// Get current values of "config/larapen/routes.php" (Original version)
					$origRoutes = PhpArrayFile::getFileContent(config_path('larapen/routes.php'));
					
					// Create or Update the "config/routes.php" file
					$filePath = config_path('routes.php');
					PhpArrayFile::writeFile($filePath, $origRoutes);
				} catch (Throwable $e) {
				}
			}
		}
	}
}
