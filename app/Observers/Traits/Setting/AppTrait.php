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

namespace App\Observers\Traits\Setting;

use App\Helpers\Common\Files\Storage\StorageDisk;
use App\Models\Country;
use App\Models\Language;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\LocalizedScope;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

trait AppTrait
{
	/**
	 * Updating
	 *
	 * @param $setting
	 * @param $original
	 */
	public function appUpdating($setting, $original)
	{
		// Storage Disk Init.
		$disk = StorageDisk::getDisk();
		
		$this->removeOldLogoFile($setting, $original, $disk);
		$this->removeOldFaviconFile($setting, $original, $disk);
		$this->removeAutoLanguageDetectedSession($setting, $original);
		
		if (array_key_exists('php_specific_date_format', $setting->value)) {
			if (
				is_array($original['value'])
				&& isset($original['value']['php_specific_date_format'])
				&& $setting->value['php_specific_date_format'] != $original['value']['php_specific_date_format']
			) {
				request()->request->add(['formatTypeFieldWasChanged' => 1]);
			}
		}
	}
	
	/**
	 * Updated
	 *
	 * @param $setting
	 */
	public function appUpdated($setting)
	{
		$this->clearOldDateFormats($setting);
	}
	
	/**
	 * Remove old logo from disk (Don't remove the default logo)
	 *
	 * @param $setting
	 * @param $original
	 * @param $disk
	 */
	private function removeOldLogoFile($setting, $original, $disk): void
	{
		if (array_key_exists('logo', $setting->value)) {
			if (
				is_array($original['value'])
				&& !empty($original['value']['logo'])
				&& $setting->value['logo'] != $original['value']['logo']
				&& !str_contains($original['value']['logo'], config('larapen.media.logo'))
				&& $disk->exists($original['value']['logo'])
			) {
				$disk->delete($original['value']['logo']);
			}
		}
	}
	
	/**
	 * Remove old favicon from disk (Don't remove the default favicon)
	 *
	 * @param $setting
	 * @param $original
	 * @param $disk
	 */
	private function removeOldFaviconFile($setting, $original, $disk): void
	{
		if (array_key_exists('favicon', $setting->value)) {
			if (
				is_array($original['value'])
				&& !empty($original['value']['favicon'])
				&& $setting->value['favicon'] != $original['value']['favicon']
				&& !str_contains($original['value']['favicon'], config('larapen.media.favicon'))
				&& $disk->exists($original['value']['favicon'])
			) {
				$disk->delete($original['value']['favicon']);
			}
		}
	}
	
	/**
	 * Remove the language detection created sessions
	 *
	 * @param $setting
	 * @param $original
	 */
	private function removeAutoLanguageDetectedSession($setting, $original): void
	{
		if (array_key_exists('auto_detect_language', $setting->value)) {
			if (
				empty($original['value'])
				|| (
					is_array($original['value'])
					&& !isset($original['value']['auto_detect_language'])
				)
				|| (
					is_array($original['value'])
					&& isset($original['value']['auto_detect_language'])
					&& $setting->value['auto_detect_language'] != $original['value']['auto_detect_language']
				)
			) {
				if (session()->has('browserLangCode')) {
					session()->forget('browserLangCode');
				}
				if (session()->has('countryLangCode')) {
					session()->forget('countryLangCode');
				}
				$countries = Country::all();
				if ($countries->count() > 0) {
					foreach ($countries as $country) {
						$sessionName = strtolower($country->code) . 'CountryLangCode';
						if (session()->has($sessionName)) {
							session()->forget($sessionName);
						}
					}
				}
			}
		}
	}
	
	/**
	 * Clear all Date formats when the format type has changed
	 *
	 * @param $setting
	 */
	private function clearOldDateFormats($setting): void
	{
		if (request()->has('formatTypeFieldWasChanged') && request()->input('formatTypeFieldWasChanged') == 1) {
			$settingTable = (new Setting)->getTable();
			$appSetting = DB::table($settingTable)->where('key', 'app')->first();
			if (!empty($appSetting)) {
				$appSetting->value = $setting->value;
				$value = jsonToArray($appSetting->value);
				if (array_key_exists('date_format', $value)) {
					unset($value['date_format']);
				}
				if (array_key_exists('datetime_format', $value)) {
					unset($value['datetime_format']);
				}
				$value = json_encode($value);
				DB::table($settingTable)->where('key', 'app')->update([
					'value' => $value,
				]);
			}
			
			$languages = Language::query()->withoutGlobalScopes([ActiveScope::class])->get();
			if ($languages->count() > 0) {
				foreach ($languages as $language) {
					$language->date_format = null;
					$language->datetime_format = null;
					$language->save();
				}
			}
			
			$countries = Country::query()->withoutGlobalScopes([ActiveScope::class, LocalizedScope::class])->get();
			if ($countries->count() > 0) {
				foreach ($countries as $country) {
					$country->date_format = null;
					$country->datetime_format = null;
					$country->save();
				}
			}
		}
	}
}
