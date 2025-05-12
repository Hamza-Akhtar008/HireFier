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

use App\Http\Requests\Front\ResumeRequest;
use App\Http\Resources\EntityCollection;
use App\Http\Resources\ResumeResource;
use App\Models\Resume;
use App\Services\Resume\SaveResume;
use Illuminate\Http\JsonResponse;

class ResumeService extends BaseService
{
	use SaveResume;
	
	/**
	 * List resumes
	 *
	 * @param array $params
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getEntries(array $params = []): JsonResponse
	{
		$perPage = getNumberOfItemsPerPage('resumes', $params['perPage'] ?? null, $this->perPage);
		$embed = getCommaSeparatedStrAsArray($params['embed'] ?? []);
		$isBelongLoggedUser = getIntAsBoolean($params['belongLoggedUser'] ?? 0);
		$isForApplyingJob = getIntAsBoolean($params['forApplyingJob'] ?? 0);
		$keyword = $params['keyword'] ?? null;
		$sort = $params['sort'] ?? [];
		
		$resumes = Resume::query();
		
		// Apply search filter
		if (!empty($keyword)) {
			$keywords = rawurldecode($keyword);
			$resumes->where('name', 'LIKE', '%' . $keywords . '%');
		}
		
		if ($isBelongLoggedUser) {
			$userId = auth(getAuthGuard())->user()?->getAuthIdentifier() ?? '-1';
			$resumes->where('user_id', $userId);
			
			if ($isForApplyingJob) {
				$limit = config('larapen.core.selectResumeInto', 5);
				$resumes->take($limit * 5);
			}
		}
		
		// Sorting
		$resumes = $this->applySorting($resumes, ['created_at', 'name'], $sort);
		
		$resumes = $resumes->paginate($perPage);
		
		// If the request is made from the app's Web environment,
		// use the Web URL as the pagination's base URL
		$resumes = setPaginationBaseUrl($resumes);
		
		$collection = new EntityCollection(ResumeResource::class, $resumes, $params);
		
		$message = ($resumes->count() <= 0) ? t('no_resumes_found') : null;
		
		return apiResponse()->withCollection($collection, $message);
	}
	
	/**
	 * Get resume
	 *
	 * @param $id
	 * @param array $params
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getEntry($id, array $params = []): JsonResponse
	{
		$embed = getCommaSeparatedStrAsArray($params['embed'] ?? []);
		$isBelongLoggedUser = getIntAsBoolean($params['belongLoggedUser'] ?? 0);
		
		$resume = Resume::query()->where('id', $id);
		
		if ($isBelongLoggedUser) {
			$userId = auth(getAuthGuard())->user()?->getAuthIdentifier() ?? '-1';
			$resume->where('user_id', $userId);
		}
		
		if (in_array('user', $embed)) {
			$resume->with('user');
		}
		
		$resume = $resume->first();
		
		if (empty($resume)) {
			return apiResponse()->notFound(t('resume_not_found'));
		}
		
		$resource = new ResumeResource($resume, $params);
		
		return apiResponse()->withResource($resource);
	}
	
	/**
	 * Store resume
	 *
	 * @param \App\Http\Requests\Front\ResumeRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(ResumeRequest $request): JsonResponse
	{
		$authUser = auth(getAuthGuard())->user();
		
		if (empty($authUser)) {
			return apiResponse()->notFound(t('user_not_found'));
		}
		
		$resume = $this->storeResume($authUser->getAuthIdentifier(), $request);
		
		$data = [
			'success' => true,
			'message' => t('Your resume has created successfully'),
			'result'  => (new ResumeResource($resume))->toArray($request),
		];
		
		return apiResponse()->json($data);
	}
	
	/**
	 * Update resume
	 *
	 * @param $id
	 * @param \App\Http\Requests\Front\ResumeRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($id, ResumeRequest $request): JsonResponse
	{
		$authUser = auth(getAuthGuard())->user();
		
		if (empty($authUser)) {
			return apiResponse()->notFound(t('user_not_found'));
		}
		
		$resume = Resume::where('user_id', $authUser->getAuthIdentifier())->where('id', $id)->first();
		
		if (empty($resume)) {
			return apiResponse()->notFound(t('resume_not_found'));
		}
		
		$resume = $this->updateResume($authUser->getAuthIdentifier(), $request, $resume);
		
		$data = [
			'success' => true,
			'message' => t('Your resume has updated successfully'),
			'result'  => (new ResumeResource($resume))->toArray($request),
		];
		
		return apiResponse()->json($data);
	}
	
	/**
	 * Delete resume(s)
	 *
	 * @param string $ids
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy(string $ids): JsonResponse
	{
		$authUser = auth(getAuthGuard())->user();
		
		if (empty($authUser)) {
			return apiResponse()->notFound(t('user_not_found'));
		}
		
		$data = [
			'success' => false,
			'message' => t('no_deletion_is_done'),
			'result'  => null,
		];
		
		// Get Entries ID (IDs separated by comma accepted)
		$ids = explode(',', $ids);
		
		// Delete
		$res = false;
		foreach ($ids as $resumeId) {
			$resume = Resume::query()
				->where('user_id', $authUser->getAuthIdentifier())
				->where('id', $resumeId)
				->first();
			
			if (!empty($resume)) {
				$res = $resume->delete();
			}
		}
		
		// Confirmation
		if ($res) {
			$data['success'] = true;
			
			$count = count($ids);
			if ($count > 1) {
				$data['message'] = t('x entities has been deleted successfully', ['entities' => t('resumes'), 'count' => $count]);
			} else {
				$data['message'] = t('1 entity has been deleted successfully', ['entity' => t('resume')]);
			}
		}
		
		return apiResponse()->json($data);
	}
}
