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

use App\Http\Requests\Request;
use Illuminate\Support\Number;

class PhotoRequest extends Request
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$rules = [];
		
		// Require 'pictures' if exists
		if ($this->file('pictures')) {
			$serverAllowedImageFormats = collect(getServerAllowedImageFormats())->join(',');
			
			$files = $this->file('pictures');
			foreach ($files as $key => $file) {
				if (empty($file)) continue;
				
				$rules['pictures.' . $key] = [
					'file',
					'mimes:' . $serverAllowedImageFormats,
					'min:' . (int)config('settings.upload.min_image_size', 0),
					'max:' . (int)config('settings.upload.max_image_size', 1000),
				];
			}
		}
		
		// Apply this rules only for the 'Multi Steps Form' Web based requests
		if (!isFromApi()) {
			// Check if this request comes from Listing creation form
			// i.e. Not from Listing updating form, where 'postInput' & 'picturesInput' sessions are not available
			if (session()->has('postInput')) {
				// If no picture is uploaded & If picture is mandatory,
				// Don't allow user to go to the next page.
				$picturesInput = (array)session('picturesInput');
				if (empty($picturesInput)) {
					if (config('settings.listing_form.picture_mandatory')) {
						$rules['pictures'] = ['required'];
					}
				}
			}
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
		
		if ($this->file('pictures')) {
			$files = $this->file('pictures');
			foreach ($files as $key => $file) {
				$attributes['pictures.' . $key] = t('picture_x', ['name' => ($key + 1)]);
			}
		}
		
		return $attributes;
	}
	
	/**
	 * Get custom messages for validator errors.
	 *
	 * @return array
	 */
	public function messages(): array
	{
		$messages = [];
		
		if ($this->file('pictures')) {
			$files = $this->file('pictures');
			foreach ($files as $key => $file) {
				// uploaded
				$maxSize = (int)config('settings.upload.max_image_size', 1000); // In KB
				$maxSize = $maxSize * 1024; // Convert KB to Bytes
				$msg = t('large_file_uploaded_error', [
					'field'   => t('picture_x', ['name' => ($key + 1)]),
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
							'field'   => t('picture_x', ['name' => ($key + 1)]),
							'maxSize' => Number::fileSize($serverMaxSize),
						]);
					}
				}
				
				$messages['pictures.' . $key . '.uploaded'] = $msg;
			}
		}
		
		if (config('settings.listing_form.picture_mandatory')) {
			$messages['pictures.required'] = t('pictures_mandatory_text');
		}
		
		return $messages;
	}
}
