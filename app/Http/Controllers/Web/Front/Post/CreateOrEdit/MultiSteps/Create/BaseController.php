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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create;

use App\Helpers\Services\Referrer;
use App\Http\Controllers\Web\Front\Payment\HasPaymentRedirection;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\BaseController as MultiStepsBaseController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\Traits\ClearTmpInputTrait;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\Traits\SubmitTrait;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\Traits\PricingPageUrlTrait;
use App\Observers\Traits\PictureTrait;
use App\Services\Payment\HasPaymentTrigger;
use App\Services\Payment\Promotion\SingleStepPayment;
use App\Services\PostService;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class BaseController extends MultiStepsBaseController
{
	use PricingPageUrlTrait;
	use PictureTrait, ClearTmpInputTrait;
	use SubmitTrait;
	use HasPaymentTrigger, SingleStepPayment, HasPaymentRedirection;
	
	protected string $baseUrl = '/posts/create';
	protected string $tmpUploadDir = 'temporary';
	protected array $allowedQueries = ['packageId', 'dataMissing'];
	
	/**
	 * @param \App\Services\PostService $postService
	 */
	public function __construct(PostService $postService,)
	{
		parent::__construct($postService);
		
		$this->commonQueries();
		
		$this->baseUrl = url($this->baseUrl);
		
		if (isPostCreationRequest()) {
			$this->shareNavItems();
		}
	}
	
	/**
	 * Get the middleware that should be assigned to the controller.
	 */
	public static function middleware(): array
	{
		$array = [];
		
		// Check if guests can post listings
		if (!doesGuestHaveAbilityToCreateListings()) {
			$array[] = 'auth';
		}
		
		return array_merge(parent::middleware(), $array);
	}
	
	/**
	 * @return void
	 */
	private function commonQueries(): void
	{
		// Set payment settings for promotion packages (Single-Step Form)
		$this->setPaymentSettingsForPromotion();
		
		// Get the listing types
		$postTypes = Referrer::getPostTypes();
		view()->share('postTypes', $postTypes);
		
		// Get the salary types
		$salaryTypes = Referrer::getSalaryTypes();
		view()->share('salaryTypes', $salaryTypes);
		
		// Get the user's latest Company
		if ($this->companies->count() > 0) {
			$selectedCompany = $this->companies->first();
			view()->share('selectedCompany', $selectedCompany);
		}
		
		// Prepend new company (being to create) to the companies list
		$companyInput = (array)session('companyInput');
		$companyId = $companyInput['company']['id'] ?? null;
		if (empty($companyId) || $companyId == 'new') {
			$companyName = $companyInput['company']['name'] ?? null;
			if (!empty($companyName)) {
				$this->companies = $this->companies->prepend($companyInput['company']);
			}
		}
		
		// Share the companies list in views
		view()->share('companies', $this->companies);
		
		if (request()->query('error') == 'paymentCancelled') {
			if (session()->has('postId')) {
				session()->forget('postId');
			}
		}
		
		// Meta Tags
		[$title, $description, $keywords] = getMetaTag('create');
		MetaTag::set('title', $title);
		MetaTag::set('description', strip_tags($description));
		MetaTag::set('keywords', $keywords);
	}
	
	/**
	 * @return array
	 */
	protected function unwantedFields(): array
	{
		return ['_token', 'entity_field', 'valid_field'];
	}
}
