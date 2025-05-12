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

namespace App\Helpers\Services;

use App\Services\CompanyService;
use App\Services\GenderService;
use App\Services\PostTypeService;
use App\Services\ReportTypeService;
use App\Services\SalaryTypeService;
use App\Services\UserTypeService;

class Referrer
{
	/**
	 * @return array
	 */
	public static function getGenders(): array
	{
		$data = getServiceData((new GenderService())->getEntries());
		$genders = data_get($data, 'result');
		
		return is_array($genders) ? $genders : [];
	}
	
	/**
	 * @return array
	 */
	public static function getUserTypes(): array
	{
		// Get user types
		$data = getServiceData((new UserTypeService())->getEntries());
		$postTypes = data_get($data, 'result');
		
		return is_array($postTypes) ? $postTypes : [];
	}
	
	/**
	 * @return array
	 */
	public static function getPostTypes(): array
	{
		// Get post types
		$queryParams = ['sort' => '-name'];
		$data = getServiceData((new PostTypeService())->getEntries($queryParams));
		
		$apiResult = data_get($data, 'result');
		$postTypes = data_get($apiResult, 'data');
		
		return is_array($postTypes) ? $postTypes : [];
	}
	
	/**
	 * @return array
	 */
	public static function getReportTypes(): array
	{
		// Get report types
		$queryParams = ['sort' => '-lft'];
		$data = getServiceData((new ReportTypeService())->getEntries($queryParams));
		
		$apiResult = data_get($data, 'result');
		$postTypes = data_get($apiResult, 'data');
		
		return is_array($postTypes) ? $postTypes : [];
	}
	
	/**
	 * @return array
	 */
	public static function getSalaryTypes(): array
	{
		// Get salary types
		$queryParams = ['sort' => '-lft'];
		$data = getServiceData((new SalaryTypeService())->getEntries($queryParams));
		
		$apiResult = data_get($data, 'result');
		$salaryTypes = data_get($apiResult, 'data');
		
		return is_array($salaryTypes) ? $salaryTypes : [];
	}
	
	/**
	 * @return array
	 */
	public static function getLoggedUserCompanies(): array
	{
		// Get user's companies
		$queryParams = [
			'perPage'          => 100,
			'embed'            => 'user',
			'belongLoggedUser' => true, // Logged user required
			'sort'             => 'id',
		];
		$data = getServiceData((new CompanyService())->getEntries($queryParams));
		
		$apiResult = data_get($data, 'result');
		$companies = data_get($apiResult, 'data');
		
		return is_array($companies) ? $companies : [];
	}
}
