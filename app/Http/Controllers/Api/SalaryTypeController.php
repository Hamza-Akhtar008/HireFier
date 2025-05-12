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

namespace App\Http\Controllers\Api;

use App\Services\SalaryTypeService;
use Illuminate\Http\JsonResponse;

/**
 * @group Posts
 */
class SalaryTypeController extends BaseController
{
	protected SalaryTypeService $salaryTypeService;
	
	/**
	 * @param \App\Services\SalaryTypeService $salaryTypeService
	 */
	public function __construct(SalaryTypeService $salaryTypeService)
	{
		parent::__construct();
		
		$this->salaryTypeService = $salaryTypeService;
	}
	
	/**
	 * List salary types
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index(): JsonResponse
	{
		return $this->salaryTypeService->getEntries();
	}
	
	/**
	 * Get salary type
	 *
	 * @urlParam id int required The salary type's ID. Example: 1
	 *
	 * @param $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($id): JsonResponse
	{
		return $this->salaryTypeService->getEntry($id);
	}
}
