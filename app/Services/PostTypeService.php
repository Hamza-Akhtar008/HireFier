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
use App\Http\Resources\PostTypeResource;
use App\Models\PostType;
use Illuminate\Http\JsonResponse;

class PostTypeService extends BaseService
{
	/**
	 * List post types
	 *
	 * @param array $params
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getEntries(array $params = []): JsonResponse
	{
		$sort = $params['sort'] ?? [];
		
		$postTypes = PostType::query();
		
		// Sorting
		$postTypes = $this->applySorting($postTypes, ['name', 'lft'], $sort);
		
		$postTypes = $postTypes->get();
		
		$resourceCollection = new EntityCollection(PostTypeResource::class, $postTypes, $params);
		
		$message = ($postTypes->count() <= 0) ? t('no_post_types_found') : null;
		
		return apiResponse()->withCollection($resourceCollection, $message);
	}
	
	/**
	 * Get post type
	 *
	 * @param $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getEntry($id): JsonResponse
	{
		$postType = PostType::query()->where('id', $id)->first();
		
		abort_if(empty($postType), 404, t('post_type_not_found'));
		
		$resource = new PostTypeResource($postType);
		
		return apiResponse()->withResource($resource);
	}
}
