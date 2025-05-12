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

namespace App\Helpers\Services\Search\Traits;

use App\Helpers\Services\Search\Traits\Filters\AuthorFilter;
use App\Helpers\Services\Search\Traits\Filters\CategoryFilter;
use App\Helpers\Services\Search\Traits\Filters\CompanyFilter;
use App\Helpers\Services\Search\Traits\Filters\DateFilter;
use App\Helpers\Services\Search\Traits\Filters\DynamicFieldsFilter;
use App\Helpers\Services\Search\Traits\Filters\KeywordFilter;
use App\Helpers\Services\Search\Traits\Filters\LocationFilter;
use App\Helpers\Services\Search\Traits\Filters\PostTypeFilter;
use App\Helpers\Services\Search\Traits\Filters\SalaryFilter;
use App\Helpers\Services\Search\Traits\Filters\TagFilter;

trait Filters
{
	use AuthorFilter, CategoryFilter, KeywordFilter, LocationFilter, TagFilter,
		DateFilter, PostTypeFilter, SalaryFilter, DynamicFieldsFilter, CompanyFilter;
	
	protected function applyFilters(): void
	{
		if (!(isset($this->posts))) {
			return;
		}
		
		// Default Filters
		$this->posts->inCountry()->verified()->unarchived();
		if (config('settings.listing_form.listings_review_activation')) {
			$this->posts->reviewed();
		}
		
		// Author
		$this->applyAuthorFilter();
		
		// Category
		$this->applyCategoryFilter();
		
		// Keyword
		$this->applyKeywordFilter();
		
		// Location
		$this->applyLocationFilter();
		
		// Tag
		$this->applyTagFilter();
		
		// Date
		$this->applyDateFilter();
		
		// Post Type
		$this->applyPostTypeFilter();
		
		// Salary
		$this->applySalaryFilter();
		
		// Dynamic Fields
		$this->applyDynamicFieldsFilters();
		
		// Company
		$this->applyCompanyFilter();
	}
}
