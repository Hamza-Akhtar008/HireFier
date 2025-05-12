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

namespace App\Http\Requests\Front;

use App\Helpers\Services\RemoveFromString;
use App\Http\Requests\Request;
use App\Http\Requests\Traits\HasEmailInput;
use App\Rules\BetweenRule;
use App\Rules\BlacklistTitleRule;
use App\Rules\BlacklistWordRule;
use Illuminate\Support\Number;

class CompanyRequest extends Request
{
	use HasEmailInput;
	
	/**
	 * Prepare the data for validation.
	 *
	 * @return void
	 */
	protected function prepareForValidation(): void
	{
		// Don't apply this to the Admin Panel
		if (isAdminPanel()) {
			return;
		}
		
		$input = $this->all();
		
		// company.name
		if ($this->filled('company.name')) {
			$input['company']['name'] = $this->input('company.name');
			$input['company']['name'] = singleLineStringCleaner($input['company']['name']);
			$input['company']['name'] = preventStringContainingOnlyNumericChars($input['company']['name']);
			$input['company']['name'] = RemoveFromString::contactInfo($input['company']['name'], true);
		}
		
		// company.description
		if ($this->filled('company.description')) {
			$input['company']['description'] = $this->input('company.description');
			$input['company']['description'] = multiLinesStringCleaner($input['company']['description']);
			$input['company']['description'] = preventStringContainingOnlyNumericChars($input['company']['description']);
			$input['company']['description'] = RemoveFromString::contactInfo($input['company']['description'], true);
		}
		
		// company.website
		if ($this->filled('company.website')) {
			$input['company']['website'] = addHttp($this->input('company.website'));
		}
		
		// company.city_id
		if ($this->has('company.city_id')) {
			$cityId = $this->input('company.city_id');
			$input['company']['city_id'] = (int)$cityId;
		}
		
		request()->merge($input); // Required!
		$this->merge($input);
	}
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		// Validation Rules
		$rules = [
			'company.name'        => ['nullable'],
			'company.description' => ['nullable'],
			'company.website'     => ['nullable', 'url'],
			'company.facebook'    => ['nullable', 'url'],
			'company.twitter'     => ['nullable', 'url'],
			'company.linkedin'    => ['nullable', 'url'],
			'company.pinterest'   => ['nullable', 'url'],
		];
		
		$companyId = $this->input('company_id');
		if (empty($companyId) || $companyId == 'new') {
			$rules['company.name'] = ['required', new BetweenRule(2, 200), new BlacklistTitleRule()];
			$rules['company.description'] = ['required', new BetweenRule(5, 12000), new BlacklistWordRule()];
			
			// Get the logo uploaded file
			$logoFile = $this->file('company.logo_path', $this->files->get('company.logo_path'));
			
			// Check 'company.logo_path' is required
			if (!empty($logoFile)) {
				$serverAllowedImageFormats = collect(getServerAllowedImageFormats())->join(',');
				$rules['company.logo_path'] = [
					'required',
					'file',
					'mimes:' . $serverAllowedImageFormats,
					'max:' . (int)config('settings.upload.max_image_size', 1000),
				];
			}
		} else {
			$rules['company_id'] = ['required', 'not_in:0', 'exists:companies,id'];
		}
		
		// Check email address
		return $this->emailRules($rules, 'company.email');
	}
	
	/**
	 * Get custom attributes for validator errors.
	 *
	 * @return array
	 */
	public function attributes(): array
	{
		$attributes = [];
		
		// Get the logo uploaded file
		$logoFile = $this->file('company.logo_path', $this->files->get('company.logo_path'));
		
		if (!empty($logoFile)) {
			$attributes['company.logo_path'] = t('logo');
		}
		
		return $attributes;
	}
	
	/**
	 * @return array
	 */
	public function messages(): array
	{
		$messages = [];
		
		// Get the logo uploaded file
		$logoFile = $this->file('company.logo_path', $this->files->get('company.logo_path'));
		
		// company.logo_path
		if (!empty($logoFile)) {
			// uploaded
			$maxSize = (int)config('settings.upload.max_image_size', 1000); // In KB
			$maxSize = $maxSize * 1024; // Convert KB to Bytes
			$msg = t('large_file_uploaded_error', [
				'field'   => t('logo'),
				'maxSize' => Number::fileSize($maxSize),
			]);
			
			$uploadMaxFilesizeStr = @ini_get('upload_max_filesize');
			$postMaxSizeStr = @ini_get('post_max_size');
			if (!empty($uploadMaxFilesizeStr) && !empty($postMaxSizeStr)) {
				$uploadMaxFilesize = forceToInt($uploadMaxFilesizeStr);
				$postMaxSize = forceToInt($postMaxSizeStr);
				
				$serverMaxSize = min($uploadMaxFilesize, $postMaxSize);
				$serverMaxSize = $serverMaxSize * 1024 * 1024; // Convert MB to KB to Bytes
				if ($serverMaxSize < $maxSize) {
					$msg = t('large_file_uploaded_error_system', [
						'field'   => t('logo'),
						'maxSize' => Number::fileSize($serverMaxSize),
					]);
				}
			}
			
			$messages['company.logo_path.uploaded'] = $msg;
		}
		
		return $messages;
	}
}
