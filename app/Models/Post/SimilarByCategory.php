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

namespace App\Models\Post;

use App\Jobs\GenerateLogoCollectionThumbnails;
use App\Models\Category;
use App\Models\Post;

trait SimilarByCategory
{
	/**
	 * Get similar Posts (Posts in the same Category)
	 *
	 * @param int|null $limit
	 * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
	 */
	public function getSimilarByCategory(?int $limit = 20)
	{
		$posts = Post::query();
		
		$postsTable = (new Post())->getTable();
		
		$select = [
			$postsTable . '.id',
			$postsTable . '.country_code',
			'category_id',
			'post_type_id',
			'company_id',
			'company_name',
			'logo_path',
			'title',
			$postsTable . '.description',
			'salary_min',
			'salary_max',
			'salary_type_id',
			'city_id',
			'featured',
			'email_verified_at',
			'phone_verified_at',
			'reviewed_at',
			$postsTable . '.created_at',
			$postsTable . '.archived_at',
		];
		if (isFromApi() && !doesRequestIsFromWebClient()) {
			$select[] = $postsTable . '.description';
			$select[] = 'user_id';
			$select[] = 'contact_name';
			$select[] = $postsTable . '.auth_field';
			$select[] = $postsTable . '.phone';
			$select[] = $postsTable . '.email';
		}
		
		if (!empty($select)) {
			foreach ($select as $column) {
				$posts->addSelect($column);
			}
		}
		
		// Get the sub-categories of the current ad parent's category
		$similarCatIds = [];
		if (!empty($this->category)) {
			if ($this->category->id == $this->category->parent_id) {
				$similarCatIds[] = $this->category->id;
			} else {
				if (!empty($this->category->parent_id)) {
					$similarCatIds = Category::childrenOf($this->category->parent_id)->get()
						->keyBy('id')
						->keys()
						->toArray();
					$similarCatIds[] = (int)$this->category->parent_id;
				} else {
					$similarCatIds[] = (int)$this->category->id;
				}
			}
		}
		
		// Default Filters
		$posts->inCountry()->verified()->unarchived();
		if (config('settings.listing_form.listings_review_activation')) {
			$posts->reviewed();
		}
		
		// Get ads from same category
		if (!empty($similarCatIds)) {
			if (count($similarCatIds) == 1) {
				if (isset($similarCatIds[0]) && !empty(isset($similarCatIds[0]))) {
					$posts->where('category_id', (int)$similarCatIds[0]);
				}
			} else {
				$posts->whereIn('category_id', $similarCatIds);
			}
		}
		
		// Relations
		$posts->has('postType');
		if (!config('settings.listings_list.hide_post_type')) {
			$posts->with('postType');
		}
		$posts->has('category');
		if (!config('settings.listings_list.hide_category')) {
			$posts->with('category', fn ($query) => $query->with('parent'));
		}
		$posts->has('salaryType');
		if (!config('settings.listings_list.hide_salary')) {
			$posts->with('salaryType');
		}
		$posts->has('city');
		if (!config('settings.listings_list.hide_location')) {
			$posts->with('city');
		}
		$posts->with('savedByLoggedUser');
		$posts->with('payment', fn($query) => $query->with('package'));
		$posts->with('user');
		$posts->with('user.permissions');
		
		if (isset($this->id)) {
			$posts->where($postsTable . '.id', '!=', $this->id);
		}
		
		// Set ORDER BY
		// $posts->orderByDesc('created_at');
		$seed = rand(1, 9999);
		$posts->inRandomOrder($seed);
		
		// $posts = $posts->take((int)$limit)->get();
		$posts = $posts->paginate((int)$limit);
		
		// Generate listings logo thumbnails
		GenerateLogoCollectionThumbnails::dispatch($posts);
		
		return $posts;
	}
}
