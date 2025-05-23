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
use App\Models\Package;
use Illuminate\Database\Eloquent\Collection;

class PackageRequest extends Request
{
	public static Collection $packages;
	public static Collection $paymentMethods;
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$rules = [];
		
		// Check if 'package_id' & 'payment_method_id' are required
		if (
			isset(self::$packages, self::$paymentMethods)
			&& self::$packages->count() > 0
			&& self::$paymentMethods->count() > 0
		) {
			// Require 'package_id' if Packages are available
			$rules['package_id'] = ['required'];
			
			// Require 'payment_method_id' if the Package 'price' > 0
			if ($this->filled('package_id')) {
				$package = Package::find($this->input('package_id'));
				if (!empty($package) && $package->price > 0) {
					$rules['payment_method_id'] = ['required', 'not_in:0'];
				}
			}
		}
		
		return $rules;
	}
}
