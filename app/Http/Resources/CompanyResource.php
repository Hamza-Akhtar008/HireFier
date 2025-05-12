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

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CompanyResource extends BaseResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray(Request $request): array
	{
		if (!isset($this->id)) return [];
		
		$entity = [
			'id' => $this->id,
		];
		
		$columns = $this->getFillable();
		foreach ($columns as $column) {
			$entity[$column] = $this->{$column} ?? null;
		}
		
		$defaultLogo = config('larapen.media.picture');
		$defaultLogoUrl = thumbParam($defaultLogo)->url();
		$entity['logo_url'] = [
			'full'   => $this->logo_url ?? $defaultLogoUrl,
			'small'  => $this->logo_url_small ?? $defaultLogoUrl,
			'medium' => $this->logo_url_medium ?? $defaultLogoUrl,
			'large'  => $this->logo_url_large ?? $defaultLogoUrl,
		];
		$entity['posts_count'] = $this->posts_count ?? 0;
		$entity['country_flag_url'] = $this->country_flag_url ?? null;
		
		if (in_array('user', $this->embed)) {
			$entity['user'] = new UserResource($this->whenLoaded('user'), $this->params);
		}
		if (in_array('city', $this->embed)) {
			$entity['city'] = new CityResource($this->whenLoaded('city'), $this->params);
		}
		
		return $entity;
	}
}
