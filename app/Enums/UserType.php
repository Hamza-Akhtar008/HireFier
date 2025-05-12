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

namespace App\Enums;

enum UserType: int
{
	use EnumToArray;
	
	case EMPLOYER = 1;
	case JOB_SEEKER = 2;
	
	public function label(): string
	{
		return match ($this) {
			self::EMPLOYER => trans('enum.employer'),
			self::JOB_SEEKER => trans('enum.job_seeker'),
		};
	}
}
