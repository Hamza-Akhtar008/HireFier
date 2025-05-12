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

use App\Models\Post;
use App\Models\PostType;
use Exception;

class PostTypeObserver
{
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param PostType $postType
	 * @return void
	 */
	public function deleting($postType)
	{
		// Delete all the postType's posts
		$posts = Post::where('post_type_id', $postType->id);
		if ($posts->count() > 0) {
			foreach ($posts->cursor() as $post) {
				$post->delete();
			}
		}
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param PostType $postType
	 * @return void
	 */
	public function saved(PostType $postType)
	{
		// Removing Entries from the Cache
		$this->clearCache($postType);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param PostType $postType
	 * @return void
	 */
	public function deleted(PostType $postType)
	{
		// Removing Entries from the Cache
		$this->clearCache($postType);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $postType
	 * @return void
	 */
	private function clearCache($postType): void
	{
		try {
			cache()->flush();
		} catch (Exception $e) {
		}
	}
}
