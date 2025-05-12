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

namespace App\Http\Controllers\Web\Front\Browsing\Category;

use App\Http\Controllers\Web\Front\FrontController;
use App\Http\Controllers\Web\Front\Traits\Sluggable\CategoryBySlug;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends FrontController
{
	use CategoryBySlug;
	
	protected string $catDisplayType = 'c_border_list';
	
	/**
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getCategoriesHtml(Request $request): JsonResponse
	{
		$perPage = getNumberOfItemsPerPage('categories');
		$page = $request->integer('page', 1);
		$languageCode = $request->input('languageCode', config('app.locale'));
		$selectedId = $request->input('selectedId');
		$parentId = $request->input('parentId');
		$parentId = !empty($parentId) ? (int)$parentId : null; // Change 0 to null
		
		// Update global vars
		$this->catDisplayType = config('settings.listing_form.cat_display_type', 'c_border_list');
		
		// Get category by ID
		$category = $this->getCategoryById($parentId, $languageCode);
		
		// Get categories
		$queryParams = [
			'perPage'        => $perPage,
			'parentId'       => $parentId,
			'nestedIncluded' => false,
			'embed'          => 'children,parent',
			'sort'           => '-lft',
		];
		if (!empty($page)) {
			$queryParams['page'] = $page;
		}
		$data = getServiceData((new CategoryService())->getEntries($parentId, $queryParams));
		
		$apiMessage = data_get($data, 'message');
		$apiResult = data_get($data, 'result');
		
		// Get categories list and format it
		$categories = data_get($apiResult, 'data', []);
		
		// Format the categories:
		// If $parentId is null, get list of categories
		// If $parentId is not null, get the selected category's list of subcategories
		$categories = collect($categories);
		if ($categories->count() > 0) {
			$categories = $categories->keyBy('id');
		}
		
		$hasChildren = (
			empty($parentId)
			|| (!empty($category) && !empty($category['children']))
		);
		
		$data = [
			'apiResult'      => $apiResult,
			'apiMessage'     => $apiMessage,
			'catDisplayType' => $this->catDisplayType,
			'categories'     => $categories, // Adjacent Categories (Children)
			'category'       => $category,
			'hasChildren'    => $hasChildren,
			'selectedId'     => $selectedId,
		];
		
		// Get categories list buffer
		$html = getViewContent('front.post.createOrEdit.inc.category.select', $data);
		
		// Send JSON Response
		$result = [
			'html'        => $html,
			'category'    => $category,
			'hasChildren' => $hasChildren,
			'parent'      => $category['parent'] ?? null,
		];
		
		return ajaxResponse()->json($result);
	}
}
