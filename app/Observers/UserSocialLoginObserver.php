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

namespace App\Observers;

use App\Models\User;
use App\Models\UserSocialLogin;

class UserSocialLoginObserver
{
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param UserSocialLogin $socialLogin
	 * @return void
	 */
	public function deleting(UserSocialLogin $socialLogin)
	{
		/** @var User $user */
		$user = $socialLogin->user ?? null;
		if (!empty($user)) {
			if (empty($user->password)) {
				$user->delete();
			}
		}
	}
}
