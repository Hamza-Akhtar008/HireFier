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

class ResumeResource extends BaseResource
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
			'id'           => $this->id,
			'country_code' => $this->country_code ?? null,
			'name'         => $this->name ?? null,
		];
		
		$authUser = auth(getAuthGuard())->user();
		
		if (!empty($authUser)) {
			$userId = $this->user_id ?? null;
			$userId = (in_array('user', $this->embed))
				? ($this->user->id ?? $userId)
				: $userId;
			
			$isAuthUserData = ($userId == $authUser->getAuthIdentifier());
			
			if ($isAuthUserData) {
				$columns = $this->getFillable();
				foreach ($columns as $column) {
					$entity[$column] = $this->{$column} ?? null;
				}
			}
		}
		
		$entity['country_flag_url'] = $this->country_flag_url ?? null;
		
		if (in_array('user', $this->embed)) {
			$entity['user'] = new UserResource($this->whenLoaded('user'), $this->params);
		}
		
		return $entity;
	}
}
