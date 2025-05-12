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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps;

use App\Helpers\Services\Referrer;
use App\Http\Controllers\Web\Front\FrontController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Traits\WizardTrait;
use App\Services\Payment\HasPaymentReferrers;
use App\Services\PostService;
use Illuminate\Support\Collection;

class BaseController extends FrontController
{
	use WizardTrait;
	use HasPaymentReferrers;
	
	protected PostService $postService;
	
	protected array $rawNavItems = [];
	protected array $navItems = [];
	protected int $stepsSegment = 3;
	protected array $allowedQueries = [];
	protected Collection $companies;
	
	/**
	 * @param \App\Services\PostService $postService
	 */
	public function __construct(PostService $postService)
	{
		parent::__construct();
		
		$this->postService = $postService;
		
		$this->commonQueries();
	}
	
	/**
	 * Common Queries
	 *
	 * @return void
	 */
	private function commonQueries(): void
	{
		// Set the payment global settings
		$this->getPaymentReferrersData();
		
		// Get the user's companies
		$companies = collect();
		if (auth()->check()) {
			// Get all the user's companies
			$companiesList = Referrer::getLoggedUserCompanies();
			
			// Get companies with only needed columns
			$companies = collect($companiesList)
				->keyBy('id')
				->map(function ($item) {
					$keepKeys = ['id', 'name', 'logo_path', 'logo_url', 'description', 'country_code'];
					$company = [];
					foreach ($keepKeys as $key) {
						if (array_key_exists($key, $item)) {
							$company[$key] = $item[$key];
						}
					}
					
					return $company;
				});
		}
		
		// Share the companies list globally
		$this->companies = $companies;
	}
}
