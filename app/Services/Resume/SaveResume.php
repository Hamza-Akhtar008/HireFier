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

namespace App\Services\Resume;

use App\Helpers\Common\Files\Upload;
use App\Http\Requests\Request;
use App\Models\Resume;

trait SaveResume
{
	/**
	 * Store the user's resume
	 *
	 * @param $userId
	 * @param \App\Http\Requests\Request $request
	 * @return \App\Models\Resume
	 */
	protected function storeResume($userId, Request $request): Resume
	{
		return $this->saveResume($userId, $request);
	}
	
	/**
	 * Update the user's resume
	 *
	 * @param $userId
	 * @param \App\Http\Requests\Request $request
	 * @param \App\Models\Resume $resume
	 * @return \App\Models\Resume
	 */
	protected function updateResume($userId, Request $request, Resume $resume): Resume
	{
		return $this->saveResume($userId, $request, $resume);
	}
	
	/**
	 * Save the user's resume
	 *
	 * @param $userId
	 * @param \App\Http\Requests\Request $request
	 * @param \App\Models\Resume|null $resume
	 * @return \App\Models\Resume
	 */
	private function saveResume($userId, Request $request, Resume|null $resume = null): Resume
	{
		// Get Resume Input
		$resumeInput = $request->input('resume');
		if (empty($resumeInput['user_id'])) {
			$resumeInput['user_id'] = $userId;
		}
		if (empty($resumeInput['country_code'])) {
			$resumeInput['country_code'] = config('country.code');
		}
		$resumeInput['active'] = 1;
		
		// Create
		if (empty($resume)) {
			$resume = new Resume();
		}
		
		// Update
		foreach ($resumeInput as $key => $value) {
			if (in_array($key, $resume->getFillable())) {
				$resume->{$key} = $value;
			}
		}
		$resume->save();
		
		// Save the Resume's File
		if ($request->hasFile('resume.file_path')) {
			$destPath = 'resumes/' . strtolower($resume->country_code) . '/' . $resume->user_id;
			$resume->file_path = Upload::file($request->file('resume.file_path'), $destPath, 'private');
			
			if (empty($resume->name)) {
				$resume->name = last(explode('/', $resume->file_path));
			}
			
			$resume->save();
		}
		
		return $resume;
	}
}
