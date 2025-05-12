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

use App\Http\Requests\Front\ResumeRequest;
use App\Services\ResumeService;
use Illuminate\Http\JsonResponse;

/**
 * @group Resumes
 */
class ResumeController extends BaseController
{
	protected ResumeService $resumeService;
	
	/**
	 * @param \App\Services\ResumeService $resumeService
	 */
	public function __construct(ResumeService $resumeService)
	{
		parent::__construct();
		
		$this->resumeService = $resumeService;
	}
	
	/**
	 * List resumes
	 *
	 * @queryParam q string Get the resume list related to the entered keyword. Example: null
	 * @queryParam belongLoggedUser boolean Force users to be logged to get data that belongs to him. Resume file and other column can be retrieved - Possible value: 0 or 1. Example: 0
	 * @queryParam sort string The sorting parameter (Order by DESC with the given column. Use "-" as prefix to order by ASC). Possible values: created_at, name. Example: created_at
	 * @queryParam perPage int Items per page. Can be defined globally from the admin settings. Cannot be exceeded 100. Example: 2
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index(): JsonResponse
	{
		$params = [
			'perPage'          => request()->integer('perPage'),
			'embed'            => request()->input('embed'),
			'belongLoggedUser' => (request()->integer('belongLoggedUser') == 1),
			'forApplyingJob'   => (request()->integer('forApplyingJob') == 1),
			'keyword'          => request()->input('q', request()->input('keyword')),
		];
		
		return $this->resumeService->getEntries($params);
	}
	
	/**
	 * Get resume
	 *
	 * @authenticated
	 * @header Authorization Bearer {YOUR_AUTH_TOKEN}
	 *
	 * @queryParam belongLoggedUser boolean Force users to be logged to get data that belongs to him - Possible value: 0 or 1. Example: 0
	 * @queryParam embed string The Comma-separated list of the company relationships for Eager Loading - Possible values: user. Example: user
	 *
	 * @urlParam id int required The resume's ID. Example: 269
	 *
	 * @param $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($id): JsonResponse
	{
		$params = [
			'embed'            => request()->input('embed'),
			'belongLoggedUser' => (request()->input('belongLoggedUser') == 1),
		];
		
		return $this->resumeService->getEntry($id, $params);
	}
	
	/**
	 * Store resume
	 *
	 * @authenticated
	 * @header Authorization Bearer {YOUR_AUTH_TOKEN}
	 *
	 * @bodyParam resume[].country_code string required The code of the user's country. Example: US
	 * @bodyParam resume[].name string The resume's name. Example: Software Engineer
	 * @bodyParam resume[].filename file required The resume's attached file.
	 *
	 * @param \App\Http\Requests\Front\ResumeRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(ResumeRequest $request): JsonResponse
	{
		return $this->resumeService->store($request);
	}
	
	/**
	 * Update resume
	 *
	 * @authenticated
	 * @header Authorization Bearer {YOUR_AUTH_TOKEN}
	 *
	 * @bodyParam resume[].name string The resume's name. Example: Software Engineer
	 * @bodyParam resume[].filename file required The resume's attached file.
	 *
	 * @urlParam id int required The resume's ID. Example: 111111
	 *
	 * @param $id
	 * @param \App\Http\Requests\Front\ResumeRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($id, ResumeRequest $request): JsonResponse
	{
		return $this->resumeService->update($id, $request);
	}
	
	/**
	 * Delete resume(s)
	 *
	 * @authenticated
	 * @header Authorization Bearer {YOUR_AUTH_TOKEN}
	 *
	 * @urlParam ids string required The ID or comma-separated IDs list of resume(s). Example: 111111,222222,333333
	 *
	 * @param string $ids
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy(string $ids): JsonResponse
	{
		return $this->resumeService->destroy($ids);
	}
}
