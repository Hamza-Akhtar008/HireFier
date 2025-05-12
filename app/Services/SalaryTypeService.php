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

namespace App\Services;

use App\Http\Resources\EntityCollection;
use App\Http\Resources\SalaryTypeResource;
use App\Models\SalaryType;
use Illuminate\Http\JsonResponse;

class SalaryTypeService extends BaseService
{
	/**
	 * List salary types
	 *
	 * @param array $params
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getEntries(array $params = []): JsonResponse
	{
		$sort = $params['sort'] ?? [];
		
		$salaryTypes = SalaryType::query();
		
		// Sorting
		$salaryTypes = $this->applySorting($salaryTypes, ['name', 'lft'], $sort);
		
		$salaryTypes = $salaryTypes->get();
		
		$resourceCollection = new EntityCollection(SalaryTypeResource::class, $salaryTypes, $params);
		
		$message = ($salaryTypes->count() <= 0) ? t('no_salary_types_found') : null;
		
		return apiResponse()->withCollection($resourceCollection, $message);
	}
	
	/**
	 * Get salary type
	 *
	 * @param $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getEntry($id): JsonResponse
	{
		$salaryType = SalaryType::query()->where('id', $id)->first();
		
		abort_if(empty($salaryType), 404, t('salary_type_not_found'));
		
		$resource = new SalaryTypeResource($salaryType);
		
		return apiResponse()->withResource($resource);
	}
}
