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

namespace App\Models\Builders;

use App\Models\Builders\Classes\TranslationsBuilder;

trait HasTranslationsBuilder
{
	/**
	 * Get a new query builder instance for the connection for translatable models
	 * This overwrites the custom global builder in the 'HasGlobalBuilder.php' file
	 *
	 * @param $query
	 * @return \App\Models\Builders\Classes\TranslationsBuilder
	 */
	public function newEloquentBuilder($query)
	{
		return new TranslationsBuilder($query);
	}
}
