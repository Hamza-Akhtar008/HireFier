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

use App\Services\PostTypeService;
use Illuminate\Http\JsonResponse;

/**
 * @group Posts
 */
class PostTypeController extends BaseController
{
	protected PostTypeService $postTypeService;
	
	/**
	 * @param \App\Services\PostTypeService $postTypeService
	 */
	public function __construct(PostTypeService $postTypeService)
	{
		parent::__construct();
		
		$this->postTypeService = $postTypeService;
	}
	
	/**
	 * List post types
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index(): JsonResponse
	{
		return $this->postTypeService->getEntries();
	}
	
	/**
	 * Get post type
	 *
	 * @urlParam id int required The post type's ID. Example: 1
	 *
	 * @param $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($id): JsonResponse
	{
		return $this->postTypeService->getEntry($id);
	}
}
