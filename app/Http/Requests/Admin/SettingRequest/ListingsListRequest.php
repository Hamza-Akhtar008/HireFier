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

namespace App\Http\Requests\Admin\SettingRequest;

/*
 * Use request() instead of $this since this form request can be called from another
 */

class ListingsListRequest extends BaseRequest
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		// $request = request();
		
		return [
			'min_salary'         => ['required', 'integer', 'min:0', 'lte:max_salary'],
			'max_salary'         => ['required', 'integer', 'max:1000000', 'gte:min_salary'],
			'salary_slider_step' => ['required', 'integer', 'min:1', 'max:10000'],
		];
	}
	
	/**
	 * @return array
	 */
	public function messages(): array
	{
		$messages = [];
		
		return $this->mergeMessages($messages);
	}
	
	/**
	 * @return array
	 */
	public function attributes(): array
	{
		$attributes = [
			'min_salary'         => trans('admin.min_salary_label'),
			'max_salary'         => trans('admin.max_salary_label'),
			'salary_slider_step' => trans('admin.salary_slider_step_label'),
		];
		
		return $this->mergeAttributes($attributes);
	}
}
