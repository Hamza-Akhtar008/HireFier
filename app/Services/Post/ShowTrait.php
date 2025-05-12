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

namespace App\Services\Post;

use App\Events\PostWasVisited;
use App\Http\Resources\PostResource;
use App\Jobs\GenerateLogoThumbnails;
use App\Models\Post;
use App\Models\Scopes\ReviewedScope;
use App\Models\Scopes\StrictActiveScope;
use App\Models\Scopes\VerifiedScope;
use App\Services\Post\Show\DetailedTrait;
use Illuminate\Http\JsonResponse;

trait ShowTrait
{
	use DetailedTrait;
	
	/**
	 * @param $id
	 * @param array $params
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function showPost($id, array $params = []): JsonResponse
	{
		$embed = getCommaSeparatedStrAsArray($params['embed'] ?? []);
		$countryCode = $params['countryCode'] ?? null;
		$isUnactivatedIncluded = getIntAsBoolean($params['unactivatedIncluded'] ?? 0);
		$isBelongLoggedUser = getIntAsBoolean($params['belongLoggedUser'] ?? 0);
		
		// Cache control
		$this->updateCachingParameters();
		
		// Cache ID
		$cacheEmbedId = !empty($embed) ? '.embed.' . implode(',', $embed) : '';
		$cacheFiltersId = '.filters' . '.unactivatedIncluded:' . (int)$isUnactivatedIncluded . '.auth:' . (int)$isBelongLoggedUser;
		$cacheId = 'post' . $cacheEmbedId . $cacheFiltersId . '.id:' . $id . '.' . config('app.locale');
		$cacheId = md5($cacheId);
		
		// Cached Query
		$post = cache()->remember($cacheId, $this->cacheExpiration, function () use (
			$countryCode,
			$isUnactivatedIncluded,
			$id,
			$embed,
			$isBelongLoggedUser
		) {
			$post = Post::query();
			
			if ($isUnactivatedIncluded) {
				$post->withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class]);
			}
			
			if (in_array('country', $embed)) {
				$post->with('country');
			}
			if (in_array('user', $embed)) {
				$post->with('user');
			}
			if (in_array('category', $embed)) {
				$post->with('category');
			}
			if (in_array('postType', $embed)) {
				$post->with('postType');
			}
			if (in_array('city', $embed)) {
				$post->with('city');
				if (in_array('subAdmin1', $embed)) {
					$post->with('city.subAdmin1');
				}
				if (in_array('subAdmin2', $embed)) {
					$post->with('city.subAdmin2');
				}
			}
			if (in_array('payment', $embed)) {
				$post->with(['payment' => function ($query) {
					$query->withoutGlobalScope(StrictActiveScope::class);
				}]);
				if (in_array('package', $embed)) {
					$post->with('payment.package');
				}
			}
			if (in_array('possiblePayment', $embed)) {
				$post->with(['possiblePayment']);
				if (in_array('package', $embed)) {
					$post->with('possiblePayment.package');
				}
			}
			if (in_array('savedByLoggedUser', $embed)) {
				$post->with('savedByLoggedUser');
			}
			if (in_array('company', $embed)) {
				$post->with('company');
			}
			
			if (!empty($countryCode)) {
				$post->inCountry($countryCode)->has('country');
			}
			if ($isBelongLoggedUser) {
				$guard = getAuthGuard();
				$userId = (auth($guard)->check()) ? auth($guard)->user()->getAuthIdentifier() : '-1';
				$post->where('user_id', $userId);
			}
			
			return $post->where('id', $id)->first();
		});
		
		// Reset caching parameters
		$this->resetCachingParameters();
		
		abort_if(empty($post), 404, t('post_not_found'));
		
		// Increment the listing's visit counter
		PostWasVisited::dispatch($post);
		
		// Generate the listing's logo thumbnails
		GenerateLogoThumbnails::dispatch($post);
		
		$resource = new PostResource($post, $params);
		
		return apiResponse()->withResource($resource);
	}
}
