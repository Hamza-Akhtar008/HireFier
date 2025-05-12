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

class PostResource extends BaseResource
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
			if ($column == 'title') {
				$entity['excerpt'] = $this->excerpt ?? null;
			}
		}
		
		$entity['reference'] = $this->reference ?? null;
		$entity['slug'] = $this->slug ?? null;
		$entity['url'] = $this->url ?? null;
		$entity['phone_intl'] = $this->phone_intl ?? null;
		$entity['created_at_formatted'] = $this->created_at_formatted ?? null;
		$entity['updated_at_formatted'] = $this->updated_at_formatted ?? null;
		$entity['archived_at_formatted'] = $this->archived_at_formatted ?? null;
		$entity['archived_manually_at_formatted'] = $this->archived_manually_at_formatted ?? null;
		$entity['user_photo_url'] = $this->user_photo_url ?? null;
		$entity['country_flag_url'] = $this->country_flag_url ?? null;
		$entity['salary_formatted'] = $this->salary_formatted ?? null;
		$entity['visits_formatted'] = $this->visits_formatted ?? null;
		$entity['distance_info'] = $this->distance_info ?? null;
		
		$defaultLogo = config('larapen.media.picture');
		$defaultLogoUrl = thumbParam($defaultLogo)->url();
		$entity['logo_url'] = [
			'full'   => $this->logo_url ?? $defaultLogoUrl,
			'small'  => $this->logo_url_small ?? $defaultLogoUrl,
			'medium' => $this->logo_url_medium ?? $defaultLogoUrl,
			'large'  => $this->logo_url_large ?? $defaultLogoUrl,
		];
		
		if (in_array('country', $this->embed)) {
			$entity['country'] = new CountryResource($this->whenLoaded('country'), $this->params);
		}
		if (in_array('user', $this->embed)) {
			$entity['user'] = new UserResource($this->whenLoaded('user'), $this->params);
		}
		if (in_array('category', $this->embed)) {
			$entity['category'] = new CategoryResource($this->whenLoaded('category'), $this->params);
		}
		if (in_array('postType', $this->embed)) {
			$entity['postType'] = new PostTypeResource($this->whenLoaded('postType'), $this->params);
		}
		if (in_array('city', $this->embed)) {
			$entity['city'] = new CityResource($this->whenLoaded('city'), $this->params);
		}
		if (in_array('currency', $this->embed)) {
			$entity['currency'] = new CurrencyResource($this->whenLoaded('currency'), $this->params);
		}
		if (in_array('payment', $this->embed)) {
			$entity['payment'] = new PaymentResource($this->whenLoaded('payment'), $this->params);
		}
		if (in_array('possiblePayment', $this->embed)) {
			$entity['possiblePayment'] = new PaymentResource($this->whenLoaded('possiblePayment'), $this->params);
		}
		if (in_array('company', $this->embed)) {
			$entity['company'] = new CompanyResource($this->whenLoaded('company'), $this->params);
		}
		
		if (isset($this->distance)) {
			$entity['distance'] = $this->distance;
		}
		
		if (in_array('savedByLoggedUser', $this->embed)) {
			if (auth(getAuthGuard())->check()) {
				// Reloads the relation from the database
				// i.e. Prevent the relationship from being cached
				// $savedByLoggedUser = $this->fresh()->savedByLoggedUser ?? null;
				$this->load('savedByLoggedUser');
				$savedByLoggedUser = $this->savedByLoggedUser ?? null;
				
				$entity['p_saved_by_logged_user'] = !empty($savedByLoggedUser) ? 1 : 0;
			}
		}
		
		// From SavedPostResource
		if (isset($this->saved_at_formatted)) {
			$entity['saved_at_formatted'] = $this->saved_at_formatted;
		}
		
		return $entity;
	}
}
