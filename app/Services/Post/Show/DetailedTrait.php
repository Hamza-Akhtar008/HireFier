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

namespace App\Services\Post\Show;

use App\Events\PostWasVisited;
use App\Http\Resources\PostResource;
use App\Jobs\GenerateLogoThumbnails;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Scopes\ReviewedScope;
use App\Models\Scopes\VerifiedScope;
use Illuminate\Http\JsonResponse;

trait DetailedTrait
{
	/**
	 * @param $id
	 * @param array $params
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function showDetailedPost($id, array $params = []): JsonResponse
	{
		// Lazy Loading Array
		$lazyLoadingArray = [
			'category',
			'category.parent',
			'postType',
			'city',
			'city.subAdmin1',
			'pictures',
			'payment',
			'payment.package',
			'savedByLoggedUser',
		];
		
		$authUser = auth(getAuthGuard())->user();
		if (!empty($authUser)) {
			// Get post's details even if it's not activated, not reviewed or archived
			$cacheId = 'post.withoutGlobalScopes.with.lazyLoading.' . $id . '.' . config('app.locale');
			$post = cache()->remember($cacheId, $this->cacheExpiration, function () use ($id, $lazyLoadingArray) {
				return Post::query()
					->withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
					->withCountryFix()
					->where('id', $id)
					->with($lazyLoadingArray)
					->first();
			});
			
			// If the logged user is not an admin user...
			if (!$authUser->can(Permission::getStaffPermissions())) {
				// Then don't get post that is not from the user
				if (!empty($post) && $post->user_id != $authUser->getAuthIdentifier()) {
					$cacheId = 'post.with.lazyLoading.' . $id . '.' . config('app.locale');
					$post = cache()->remember($cacheId, $this->cacheExpiration, function () use ($id, $lazyLoadingArray) {
						return Post::withCountryFix()
							->unarchived()
							->where('id', $id)
							->with($lazyLoadingArray)
							->first();
					});
				}
			}
		} else {
			$cacheId = 'post.with.lazyLoading.' . $id . '.' . config('app.locale');
			$post = cache()->remember($cacheId, $this->cacheExpiration, function () use ($id, $lazyLoadingArray) {
				return Post::withCountryFix()
					->unarchived()
					->where('id', $id)
					->with($lazyLoadingArray)
					->first();
			});
		}
		// Preview Post after activation
		if (request()->filled('preview') && request()->input('preview') == 1) {
			// Get the post's details even if it's not activated and reviewed
			$post = Post::query()
				->withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
				->withCountryFix()
				->where('id', $id)
				->with($lazyLoadingArray)
				->first();
		}
		
		// Post isn't found
		if (empty($post) || empty($post->category) || empty($post->city)) {
			abort(404, t('post_not_found'));
		}
		
		// Increment the listing's visit counter
		PostWasVisited::dispatch($post);
		
		// Generate the listing's logo thumbnails
		GenerateLogoThumbnails::dispatch($post);
		
		$data = [
			'success' => true,
			'result'  => new PostResource($post, $params),
		];
		
		return apiResponse()->json($data);
	}
}
