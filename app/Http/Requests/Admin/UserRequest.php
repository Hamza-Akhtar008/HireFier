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

use App\Http\Requests\Traits\HasEmailInput;
use App\Http\Requests\Traits\HasPasswordInput;
use App\Http\Requests\Traits\HasPhoneInput;
use App\Models\User;
use App\Rules\UsernameIsAllowedRule;
use App\Rules\UsernameIsValidRule;
use Illuminate\Validation\Rule;

class UserRequest extends Request
{
	use HasEmailInput, HasPhoneInput, HasPasswordInput;
	
	/**
	 * Prepare the data for validation.
	 *
	 * @return void
	 */
	protected function prepareForValidation(): void
	{
		$input = $this->all();
		
		// auth_field
		$input['auth_field'] = getAuthField();
		
		// phone
		$input = $this->preparePhoneForValidation($this, $input);
		
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
		$authFields = array_keys(getAuthFields());
		
		$rules = [];
		
		// CREATE
		if (in_array($this->method(), ['POST', 'CREATE'])) {
			$rules = $this->storeRules($authFields);
		}
		
		// UPDATE
		if (in_array($this->method(), ['PUT', 'PATCH', 'UPDATE'])) {
			$rules = $this->updateRules($authFields);
		}
		
		return $rules;
	}
	
	/**
	 * @param array $authFields
	 * @return array
	 */
	public function storeRules(array $authFields): array
	{
		$rules = [
			'user_type_id'  => ['required', 'not_in:0'],
			'gender_id'     => ['required', 'not_in:0'],
			'name'          => ['required', 'min:2', 'max:100'],
			'country_code'  => ['sometimes', 'required', 'not_in:0'],
			'auth_field'    => ['required', Rule::in($authFields)],
			'phone'         => ['max:30'],
			'phone_country' => ['required_with:phone'],
			'password'      => ['required'],
		];
		
		$phoneIsEnabledAsAuthField = (config('settings.sms.enable_phone_as_auth_field') == '1');
		$phoneNumberIsRequired = ($phoneIsEnabledAsAuthField && $this->input('auth_field') == 'phone');
		$usersTable = config('permission.table_names.users', 'users');
		
		// email
		$emailIsRequired = (!$phoneNumberIsRequired);
		if ($emailIsRequired) {
			$rules['email'][] = 'required';
		}
		$rules = $this->emailRules($rules);
		if ($this->filled('email')) {
			$rules['email'][] = 'unique:' . $usersTable . ',email';
		}
		
		// phone
		if ($phoneNumberIsRequired) {
			$rules['phone'][] = 'required';
		}
		$rules = $this->phoneRules($rules);
		if ($this->filled('phone')) {
			$rules['phone'][] = 'unique:' . $usersTable . ',phone';
		}
		
		// username
		if ($this->filled('username')) {
			$rules['username'] = [
				'between:3,50',
				'unique:' . $usersTable . ',username',
				new UsernameIsValidRule(),
				new UsernameIsAllowedRule(),
			];
		}
		
		return $this->passwordRules($rules);
	}
	
	/**
	 * @param array $authFields
	 * @return array
	 */
	public function updateRules(array $authFields): array
	{
		$rules = [
			'user_type_id'  => ['required', 'not_in:0'],
			'gender_id'     => ['required', 'not_in:0'],
			'name'          => ['required', 'max:100'],
			'country_code'  => ['sometimes', 'required', 'not_in:0'],
			'auth_field'    => ['required', Rule::in($authFields)],
			'phone'         => ['max:30'],
			'phone_country' => ['required_with:phone'],
			'username'      => [new UsernameIsValidRule()],
		];
		
		$emailChanged = false;
		$phoneChanged = false;
		$usernameChanged = false;
		
		$user = null;
		$userId = request()->segment(3);
		if (!empty($userId) && is_numeric($userId)) {
			$user = User::find($userId);
		}
		
		if (isFromAdminPanel() && request()->segment(2) == 'account') {
			$user = auth()->check() ? auth()->user() : null;
		}
		
		// Check if these fields has changed
		if (!empty($user)) {
			$emailChanged = ($this->filled('email') && $this->input('email') != $user->email);
			$phoneChanged = ($this->filled('phone') && $this->input('phone') != $user->phone);
			$usernameChanged = ($this->filled('username') && $this->input('username') != $user->username);
		}
		
		$phoneIsEnabledAsAuthField = (config('settings.sms.enable_phone_as_auth_field') == '1');
		$phoneNumberIsRequired = ($phoneIsEnabledAsAuthField && $this->input('auth_field') == 'phone');
		$usersTable = config('permission.table_names.users', 'users');
		
		// email
		$emailIsRequired = (!$phoneNumberIsRequired);
		if ($emailIsRequired) {
			$rules['email'][] = 'required';
		}
		$rules = $this->emailRules($rules);
		if ($emailChanged) {
			$rules['email'][] = 'unique:' . $usersTable . ',email';
		}
		
		// phone
		if ($phoneNumberIsRequired) {
			$rules['phone'][] = 'required';
		}
		$rules = $this->phoneRules($rules);
		if ($phoneChanged) {
			$rules['phone'][] = 'unique:' . $usersTable . ',phone';
		}
		
		// username
		if ($this->filled('username')) {
			$rules['username'][] = 'between:3,50';
		}
		if ($usernameChanged) {
			$rules['username'][] = 'required';
			$rules['username'][] = new UsernameIsAllowedRule();
			$rules['username'][] = 'unique:' . $usersTable . ',username';
		}
		
		return $this->passwordRules($rules);
	}
}
