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

namespace App\Http\Requests\Admin;

use App\Rules\BetweenRule;
use App\Rules\BlacklistTitleRule;
use App\Rules\BlacklistWordRule;

class CompanyRequest extends Request
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		// Validation Rules
		$rules = [
			'name'        => ['required', new BetweenRule(2, 200), new BlacklistTitleRule()],
			'description' => ['required', new BetweenRule(5, 12000), new BlacklistWordRule()],
			'email'       => ['nullable', 'email'],
			'website'     => ['nullable', 'url'],
			'facebook'    => ['nullable', 'url'],
			'twitter'     => ['nullable', 'url'],
			'linkedin'    => ['nullable', 'url'],
			'pinterest'   => ['nullable', 'url'],
		];
		
		// Check 'logo_path' is required
		if ($this->hasFile('logo_path')) {
			$serverAllowedImageFormats = collect(getServerAllowedImageFormats())->join(',');
			$rules['logo_path'] = [
				'required',
				'file',
				'mimes:' . $serverAllowedImageFormats,
				'max:' . (int)config('settings.upload.max_image_size', 1000),
			];
		}
		
		return $rules;
	}
	
	/**
	 * Get custom attributes for validator errors.
	 *
	 * @return array
	 */
	public function attributes(): array
	{
		$attributes = [];
		
		if ($this->file('logo_path')) {
			$attributes['logo_path'] = t('logo');
		}
		
		return $attributes;
	}
}
