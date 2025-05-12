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

namespace App\Models\Traits;

use App\Helpers\Common\Files\Storage\StorageDisk;

trait CompanyTrait
{
	// ===| ADMIN PANEL METHODS |===
	
	public function getNameHtml(): string
	{
		$company = self::find($this->id);
		
		$out = '';
		if (!empty($company)) {
			$out .= '<a href="' . urlGen()->company($company->id) . '" target="_blank">';
			$out .= $company->name;
			$out .= '</a>';
			$out .= ' <span class="label label-default">' . $company->posts()->count() . ' ' . trans('admin.jobs') . '</span>';
		} else {
			$out .= '--';
		}
		
		return $out;
	}
	
	public function getLogoHtml(): string
	{
		$defaultLogoUrl = thumbParam(config('larapen.media.picture'))->url();
		
		// Get logo
		$logoUrl = $this->logo_url_small ?? $defaultLogoUrl;
		$style = ' style="width:auto; max-height:90px;"';
		$out = '<img src="' . $logoUrl . '" data-bs-toggle="tooltip" title="' . $this->name . '"' . $style . '>';
		
		// Add link to the Ad
		$url = urlGen()->company($this->id);
		
		return '<a href="' . $url . '" target="_blank">' . $out . '</a>';
	}
	
	public function getCountryHtml()
	{
		$country = $this->country ?? null;
		$countryCode = $country->code ?? $this->country_code ?? null;
		$countryFlagUrl = $country->flag_url ?? $this->country_flag_url ?? null;
		
		if (!empty($countryFlagUrl)) {
			return '<img src="' . $countryFlagUrl . '" data-bs-toggle="tooltip" title="' . $countryCode . '">';
		} else {
			return $countryCode;
		}
	}
	
	// ===| OTHER METHODS |===
	
	public static function getLogo($value)
	{
		if (empty($value)) {
			return $value;
		}
		
		$disk = StorageDisk::getDisk();
		
		// OLD PATH
		$oldBase = 'pictures/';
		$newBase = 'files/';
		if (str_contains($value, $oldBase)) {
			$value = $newBase . last(explode($oldBase, $value));
		}
		
		// NEW PATH
		if (str_ends_with($value, '/')) {
			return $value;
		}
		
		if (empty($value) || !$disk->exists($value)) {
			$value = config('larapen.media.picture');
		}
		
		return $value;
	}
}
