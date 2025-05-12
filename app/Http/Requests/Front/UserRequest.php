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
use App\Http\Requests\Traits\HasCaptchaInput;
use App\Http\Requests\Traits\HasEmailInput;
use App\Http\Requests\Traits\HasPasswordInput;
use App\Http\Requests\Traits\HasPhoneInput;
use App\Rules\BetweenRule;
use App\Rules\BlacklistTitleRule;
use App\Rules\BlacklistWordRule;
use App\Rules\UsernameIsAllowedRule;
use App\Rules\UsernameIsValidRule;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;

class UserRequest extends Request
{
	use HasEmailInput, HasPhoneInput, HasPasswordInput, HasCaptchaInput;
	
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		if (in_array($this->method(), ['POST', 'CREATE'])) {
			return true;
		} else {
			$guard = getAuthGuard();
			
			return auth($guard)->check();
		}
	}
	
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
		
		// name
		if ($this->filled('name')) {
			$input['name'] = singleLineStringCleaner($this->input('name'));
			$input['name'] = preventStringContainingOnlyNumericChars($input['name']);
		}
		
		// auth_field
		$input['auth_field'] = getAuthField();
		
		// phone
		$input = $this->preparePhoneForValidation($this, $input);
		
		// Set/Capture IP address
		if (doesRequestIsFromWebClient()) {
			// create_from_ip
			if (in_array($this->method(), ['POST', 'CREATE'])) {
				$input['create_from_ip'] = request()->ip();
			}
			
			// latest_update_ip
			if (in_array($this->method(), ['PUT', 'PATCH', 'UPDATE'])) {
				$input['latest_update_ip'] = request()->ip();
			}
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
		$rules = [];
		
		$authFields = array_keys(getAuthFields());
		
		// CREATE
		if (in_array($this->method(), ['POST', 'CREATE'])) {
			$rules = $this->storeRules($authFields);
		}
		
		// UPDATE
		if (in_array($this->method(), ['PUT', 'PATCH', 'UPDATE'])) {
			$rules = $this->updateRules($authFields);
		}
		
		// photo_path
		if ($this->hasFile('photo_path')) {
			$serverAllowedImageFormats = collect(getServerAllowedImageFormats())->join(',');
			$rules['photo_path'] = [
				'file',
				'mimes:' . $serverAllowedImageFormats,
				'min:' . (int)config('settings.upload.min_image_size', 0),
				'max:' . (int)config('settings.upload.max_image_size', 1000),
			];
		}
		
		return $rules;
	}
	
	/**
	 * @param array $authFields
	 * @return array
	 */
	private function storeRules(array $authFields): array
	{
		$rules = [
			'name'          => ['required', new BetweenRule(2, 200)],
			'user_type_id'  => ['required', 'not_in:0'],
			'country_code'  => ['sometimes', 'required', 'not_in:0'],
			'auth_field'    => ['required', Rule::in($authFields)],
			'phone'         => ['max:30'],
			'phone_country' => ['required_with:phone'],
			'password'      => ['required', 'confirmed'],
			'accept_terms'  => ['accepted'],
		];
		
		$phoneIsEnabledAsAuthField = (config('settings.sms.enable_phone_as_auth_field') == '1');
		$phoneNumberIsRequired = ($phoneIsEnabledAsAuthField && $this->input('auth_field') == 'phone');
		
		// email
		$emailIsRequired = (!$phoneNumberIsRequired);
		if ($emailIsRequired) {
			$rules['email'][] = 'required';
		}
		$rules = $this->emailRules($rules);
		if ($this->filled('email')) {
			if (isDemoEnv()) {
				$rules['email'][] = Rule::notIn(getDemoEmailAddresses());
			}
			$rules['email'][] = 'unique:users,email';
		}
		
		// phone
		if ($phoneNumberIsRequired) {
			$rules['phone'][] = 'required';
		}
		$rules = $this->phoneRules($rules);
		if ($this->filled('phone')) {
			$rules['phone'][] = 'unique:users,phone';
		}
		
		// username
		$usernameIsEnabled = !config('larapen.core.disable.username');
		if ($usernameIsEnabled) {
			if ($this->filled('username')) {
				$rules['username'] = [
					'between:3,50',
					'unique:users,username',
					new UsernameIsValidRule(),
					new UsernameIsAllowedRule(),
				];
			}
		}
		
		// password
		$rules = $this->passwordRules($rules);
		
		// COMPANY: Check if 'company' is required
		if (config('larapen.core.register.showCompanyFields')) {
			if ($this->input('user_type_id') == 1) {
				$rules['company.name'] = ['required', new BetweenRule(2, 200), new BlacklistTitleRule()];
				$rules['company.description'] = ['required', new BetweenRule(5, 1000), new BlacklistWordRule()];
				
				// company.logo_path
				if ($this->file('company.logo_path')) {
					$serverAllowedImageFormats = collect(getServerAllowedImageFormats())->join(',');
					$rules['company.logo_path'] = [
						'file',
						'mimes:' . $serverAllowedImageFormats,
						'max:' . (int)config('settings.upload.max_image_size', 1000),
					];
				}
			}
		}
		
		// CANDIDATE: Check if 'resume' is required
		if (config('larapen.core.register.showResumeFields')) {
			if ($this->input('user_type_id') == 2) {
				$allowedFileFormats = collect(getAllowedFileFormats())->join(',');
				$rules['resume.file_path'] = [
					'required',
					'mimes:' . $allowedFileFormats,
					'max:' . (int)config('settings.upload.max_file_size', 1000),
				];
			}
		}
		
		return $this->captchaRules($rules);
	}
	
	/**
	 * @param array $authFields
	 * @return array
	 */
	private function updateRules(array $authFields): array
	{
		$guard = getAuthGuard();
		$authUser = auth($guard)->user();
		
		$rules = [];
		if (empty($authUser->user_type_id) || $authUser->user_type_id == 0) {
			$rules['user_type_id'] = ['required', 'not_in:0'];
		} else {
			$rules['name'] = ['required', 'max:100'];
			$rules['auth_field'] = ['required', Rule::in($authFields)];
			$rules['phone'] = ['max:30'];
			$rules['phone_country'] = ['required_with:phone'];
			$rules['username'] = [new UsernameIsValidRule()];
			
			// Check if these fields have changed
			$emailChanged = ($this->filled('email') && $this->input('email') != $authUser->email);
			$phoneChanged = ($this->filled('phone') && $this->input('phone') != $authUser->phone);
			$usernameChanged = ($this->filled('username') && $this->input('username') != $authUser->username);
			
			$phoneIsEnabledAsAuthField = (config('settings.sms.enable_phone_as_auth_field') == '1');
			$phoneNumberIsRequired = ($phoneIsEnabledAsAuthField && $this->input('auth_field') == 'phone');
			
			// email
			$emailIsRequired = (!$phoneNumberIsRequired);
			if ($emailIsRequired) {
				$rules['email'][] = 'required';
			}
			$rules = $this->emailRules($rules);
			if ($emailChanged) {
				$rules['email'][] = 'unique:users,email';
			}
			
			// phone
			if ($phoneNumberIsRequired) {
				$rules['phone'][] = 'required';
			}
			$rules = $this->phoneRules($rules);
			if ($phoneChanged) {
				$rules['phone'][] = 'unique:users,phone';
			}
			
			// username
			if ($this->filled('username')) {
				$rules['username'][] = 'between:3,50';
			}
			if ($usernameChanged) {
				$rules['username'][] = 'required';
				$rules['username'][] = new UsernameIsAllowedRule();
				$rules['username'][] = 'unique:users,username';
			}
			
			// password
			$rules = $this->passwordRules($rules);
			if ($this->filled('password')) {
				$rules['password'][] = 'confirmed';
			}
			
			if ($this->filled('user_accept_terms') && $this->input('user_accept_terms') != 1) {
				$rules['accept_terms'] = ['accepted'];
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
		
		if ($this->hasFile('company.logo_path')) {
			$attributes['company.logo_path'] = strtolower(t('logo'));
		}
		if ($this->hasFile('resume.file_path')) {
			$attributes['resume.file_path'] = strtolower(t('resume_file'));
		}
		if ($this->hasFile('photo_path')) {
			$attributes['photo_path'] = strtolower(t('Photo'));
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
		
		// company.logo_path
		if ($this->file('company.logo_path')) {
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
		
		if ($this->hasFile('photo_path')) {
			// uploaded
			$maxSize = (int)config('settings.upload.max_image_size', 1000); // In KB
			$maxSize = $maxSize * 1024;                                     // Convert KB to Bytes
			$msg = t('large_file_uploaded_error', [
				'field'   => strtolower(t('Photo')),
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
						'field'   => strtolower(t('Photo')),
						'maxSize' => Number::fileSize($serverMaxSize),
					]);
				}
			}
			
			$messages['photo_path.uploaded'] = $msg;
		}
		
		return $messages;
	}
}
