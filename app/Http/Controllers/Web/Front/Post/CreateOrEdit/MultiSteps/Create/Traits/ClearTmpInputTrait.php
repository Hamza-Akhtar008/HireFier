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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\Traits;

use Throwable;

trait ClearTmpInputTrait
{
	/**
	 * Clear Temporary Inputs & Files
	 */
	public function clearTemporaryInput(): void
	{
		if (session()->has('companyInput')) {
			$companyInput = (array)session('companyInput');
			$filePath = $companyInput['company']['logo_path'] ?? null;
			if (!empty($filePath)) {
				if (hasTemporaryPath($filePath)) {
					try {
						$this->removePictureWithItsThumbs($filePath);
					} catch (Throwable $e) {
						$message = $e->getMessage();
						flash($message)->error();
					}
				}
			}
			session()->forget('companyInput');
		}
		
		if (session()->has('postInput')) {
			session()->forget('postInput');
		}
		
		if (session()->has('paymentInput')) {
			session()->forget('paymentInput');
		}
		
		if (session()->has('uid')) {
			session()->forget('uid');
		}
	}
}
