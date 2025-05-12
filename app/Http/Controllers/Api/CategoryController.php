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

use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

/**
 * @group Categories
 */
class CategoryController extends BaseController
{
	protected CategoryService $categoryService;
	
	/**
	 * @param \App\Services\CategoryService $categoryService
	 */
	public function __construct(CategoryService $categoryService)
	{
		parent::__construct();
		
		$this->categoryService = $categoryService;
	}
	
	/**
	 * List categories
	 *
	 * @queryParam parentId int The ID of the parent category of the sub categories to retrieve. Example: 0
	 * @queryParam nestedIncluded int If parent ID is not provided, are nested entries will be included? - Possible values: 0,1. Example: 0
	 * @queryParam embed string The Comma-separated list of the category relationships for Eager Loading - Possible values: parent,children. Example: null
	 * @queryParam sort string The sorting parameter (Order by DESC with the given column. Use "-" as prefix to order by ASC). Possible values: lft. Example: -lft
	 * @queryParam perPage int Items per page. Can be defined globally from the admin settings. Cannot be exceeded 100. Example: 2
	 * @queryParam page int Items page number. From 1 to ("total items" divided by "items per page value - perPage"). Example: 1
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index(): JsonResponse
	{
		$params = [
			'cacheExpiration' => request()->integer('cacheExpiration'),
			'perPage'         => request()->integer('perPage'),
			'page'            => request()->integer('page'),
			'embed'           => request()->input('embed'),
			'nestedIncluded'  => (request()->input('nestedIncluded') == 1),
		];
		
		$parentId = request()->integer('parentId');
		
		return $this->categoryService->getEntries($parentId, $params);
	}
	
	/**
	 * Get category
	 *
	 * Get category by its unique slug or ID.
	 *
	 * @queryParam parentCatSlug string The slug of the parent category to retrieve used when category's slug provided instead of ID. Example: engineering
	 *
	 * @urlParam slugOrId string required The slug or ID of the category. Example: 1
	 *
	 * @param int|string $slugOrId
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show(int|string $slugOrId): JsonResponse
	{
		$parentSlug = is_numeric($slugOrId) ? null : request()->input('parentCatSlug');
		
		return $this->categoryService->getEntry($slugOrId, $parentSlug);
	}
}
