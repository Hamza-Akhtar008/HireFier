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

use App\Helpers\Common\Num;
use App\Http\Requests\Traits\HasEmailInput;
use App\Http\Requests\Traits\HasPhoneInput;
use App\Rules\BetweenRule;
use App\Rules\BlacklistTitleRule;
use App\Rules\BlacklistWordRule;
use Illuminate\Validation\Rule;

class PostRequest extends Request
{
	use HasEmailInput, HasPhoneInput;
	
	/**
	 * Prepare the data for validation.
	 *
	 * @return void
	 */
	protected function prepareForValidation(): void
	{
		$input = $this->all();
		
		// salary_min
		if ($this->has('salary_min')) {
			if ($this->filled('salary_min')) {
				$input['salary_min'] = $this->input('salary_min');
				// If field's value contains only numbers and dot,
				// Then decimal separator is set as dot.
				if (preg_match('/^[0-9.]*$/', $input['salary_min'])) {
					$input['salary_min'] = Num::formatForDb($input['salary_min'], '.');
				} else {
					if ($this->filled('currency_decimal_separator')) {
						$input['salary_min'] = Num::formatForDb($input['salary_min'], $this->input('currency_decimal_separator'));
					}
				}
			} else {
				$input['salary_min'] = null;
			}
		}
		
		// salary_max
		if ($this->has('salary_max')) {
			if ($this->filled('salary_max')) {
				$input['salary_max'] = $this->input('salary_max');
				// If field's value contains only numbers and dot,
				// Then decimal separator is set as dot.
				if (preg_match('/^[0-9.]*$/', $input['salary_max'])) {
					$input['salary_max'] = Num::formatForDb($input['salary_max'], '.');
				} else {
					if ($this->filled('currency_decimal_separator')) {
						$input['salary_max'] = Num::formatForDb($input['salary_max'], $this->input('currency_decimal_separator'));
					}
				}
			} else {
				$input['salary_max'] = null;
			}
		}
		
		// currency_code (Not implemented)
		if ($this->filled('currency_code')) {
			$input['currency_code'] = $this->input('currency_code');
		}
		
		// auth_field
		$input['auth_field'] = getAuthField();
		
		// phone
		$input = $this->preparePhoneForValidation($this, $input);
		
		// tags
		if ($this->filled('tags')) {
			$input['tags'] = tagCleaner($this->input('tags'));
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
		
		$rules['category_id'] = ['required', 'not_in:0'];
		$rules['post_type_id'] = ['required', 'not_in:0'];
		$rules['company_name'] = ['required', new BetweenRule(2, 200), new BlacklistTitleRule()];
		$rules['company_description'] = ['required', new BetweenRule(5, 12000), new BlacklistWordRule()];
		$rules['title'] = [
			'required',
			new BetweenRule(
				(int)config('settings.listing_form.title_min_length', 2),
				(int)config('settings.listing_form.title_max_length', 150)
			),
		];
		$rules['description'] = [
			'required',
			new BetweenRule(
				(int)config('settings.listing_form.description_min_length', 5),
				(int)config('settings.listing_form.description_max_length', 12000)
			),
		];
		$rules['contact_name'] = ['required', new BetweenRule(3, 200)];
		$rules['auth_field'] = ['required', Rule::in($authFields)];
		$rules['phone'] = ['max:30'];
		$rules['phone_country'] = ['required_with:phone'];
		
		$phoneIsEnabledAsAuthField = (config('settings.sms.enable_phone_as_auth_field') == '1');
		$phoneNumberIsRequired = ($phoneIsEnabledAsAuthField && $this->input('auth_field') == 'phone');
		
		// email
		$emailIsRequired = (!$phoneNumberIsRequired);
		if ($emailIsRequired) {
			$rules['email'][] = 'required';
		}
		$rules = $this->emailRules($rules);
		
		// phone
		if ($phoneNumberIsRequired) {
			$rules['phone'][] = 'required';
		}
		$rules = $this->phoneRules($rules);
		
		// Tags
		if ($this->filled('tags')) {
			$rules['tags.*'] = ['regex:' . tagRegexPattern()];
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
		
		if ($this->filled('tags')) {
			foreach ($this->input('tags') as $key => $tag) {
				$attributes['tags.' . $key] = t('tag_x', ['key' => ($key + 1)]);
			}
		}
		
		return $attributes;
	}
}
