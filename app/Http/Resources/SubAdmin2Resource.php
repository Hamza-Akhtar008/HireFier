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

class SubAdmin2Resource extends BaseResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray(Request $request): array
	{
		if (!isset($this->code)) return [];
		
		$entity = [
			'code' => $this->code,
		];
		
		$columns = $this->getFillable();
		foreach ($columns as $column) {
			$entity[$column] = $this->{$column} ?? null;
		}
		
		if (in_array('country', $this->embed)) {
			$entity['country'] = new CountryResource($this->whenLoaded('country'), $this->params);
		}
		if (in_array('subAdmin1', $this->embed)) {
			$entity['subAdmin1'] = new SubAdmin1Resource($this->whenLoaded('subAdmin1'), $this->params);
		}
		
		return $entity;
	}
}
