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

namespace App\Observers;

use App\Models\Company;
use App\Models\Post;
use Exception;

class CompanyObserver
{
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param Company $company
	 * @return void
	 */
	public function deleting(Company $company)
	{
		// Get Posts
		$posts = Post::where('company_id', $company->id);
		if ($posts->count() > 0) {
			$posts->chunk(100, function ($posts) {
				foreach ($posts as $post) {
					$post->delete();
				}
			});
		}
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Company $company
	 * @return void
	 */
	public function updated(Company $company)
	{
		// Update all the Company's Posts
		$posts = Post::where('company_id', $company->id);
		if ($posts->count() > 0) {
			$posts->chunk(100, function ($posts) use ($company) {
				foreach ($posts as $post) {
					$post->company_name = $company->name;
					$post->logo_path = $company->logo_path;
					$post->company_description = $company->description;
					$post->save();
				}
			});
		}
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Company $company
	 * @return void
	 */
	public function saved(Company $company)
	{
		// Removing Entries from the Cache
		$this->clearCache($company);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Company $company
	 * @return void
	 */
	public function deleted(Company $company)
	{
		// Removing Entries from the Cache
		$this->clearCache($company);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $company
	 * @return void
	 */
	private function clearCache($company): void
	{
		try {
			cache()->forget($company->country_code . '.home.getCompanies.take.limit.x');
		} catch (Exception $e) {
		}
	}
}
