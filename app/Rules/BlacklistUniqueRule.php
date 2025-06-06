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

namespace App\Rules;

use App\Models\Blacklist;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BlacklistUniqueRule implements ValidationRule
{
	public string $type = 'word';
	
	public function __construct($type)
	{
		$this->type = $type;
	}
	
	/**
	 * Run the validation rule.
	 */
	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
		if (!$this->passes($attribute, $value)) {
			$fail(trans('validation.blacklist_unique', ['type' => $this->type]));
		}
	}
	
	/**
	 * Determine if the validation rule passes.
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function passes(string $attribute, mixed $value): bool
	{
		$value = getAsString($value);
		$value = trim(mb_strtolower($value));
		
		$words = Blacklist::where('type', $this->type)->where($attribute, $value)->get();
		if ($words->count() > 0) {
			foreach ($words as $word) {
				$word->entry = trim(mb_strtolower($word->entry));
				/*
				 * Since MySQL (non-strict mode) allows entries with accented characters,
				 * we need to check that using PHP to exclude entries with accented characters.
				 */
				if ($word->entry == $value) {
					return false;
				}
			}
		}
		
		return true;
	}
}
