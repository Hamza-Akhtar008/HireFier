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

namespace App\Services\Payment;

use App\Models\Package;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

trait HasPaymentTrigger
{
	use MakePayment;
	
	/**
	 * Check if a payment is requested
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \App\Models\Post|\App\Models\User|null $payable
	 * @return array
	 */
	protected function isPaymentRequested(Request $request, Post|User|null $payable): array
	{
		$result = [
			'success' => true,
			'failure' => false,
			'message' => null,
		];
		
		$areRequiredDataFilled = $request->filled(['package_id', 'payment_method_id']);
		if (empty($payable) || !$areRequiredDataFilled) {
			return $result;
		}
		
		// Check if new payment is accepted
		if (!empty($payable->payment)) {
			// Check package renewal
			if (!$request->filled('accept_package_renewal')) {
				return $result;
			}
		}
		
		// Get the payable full name with namespace
		$payableType = get_class($payable);
		$isPromoting = (str_ends_with($payableType, 'Post'));
		$isSubscripting = (str_ends_with($payableType, 'User'));
		
		if (!$isPromoting && !$isSubscripting) {
			$result['failure'] = true;
			$result['message'] = t('payable_type_not_found');
			
			return $result;
		}
		
		// Check if the selected package exists and is not a basic package
		$package = Package::query()
			->when($isPromoting, fn ($query) => $query->promotion())
			->when($isSubscripting, fn ($query) => $query->subscription())
			->where('id', $request->input('package_id'))
			->first();
		if (empty($package)) {
			$result['failure'] = true;
			$result['message'] = t('package_not_found');
			
			return $result;
		}
		
		$isNotBasicPackage = (is_numeric($package->price) && $package->price > 0);
		
		$result['success'] = $isNotBasicPackage;
		$result['message'] = null;
		
		return $result;
	}
}
