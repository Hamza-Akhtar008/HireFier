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

namespace App\Http\Controllers\Web\Front\Search;

use App\Http\Controllers\Web\Front\FrontController;
use App\Http\Controllers\Web\Front\Search\Traits\MetaTagTrait;
use App\Http\Controllers\Web\Front\Search\Traits\TitleTrait;
use App\Http\Requests\Front\SendPostByEmailRequest;
use App\Services\ContactService;
use App\Services\PostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BaseController extends FrontController
{
	use MetaTagTrait, TitleTrait;
	
	protected PostService $postService;
	protected ContactService $contactService;
	
	/**
	 * @param \App\Services\PostService $postService
	 * @param \App\Services\ContactService $contactService
	 * @param \Illuminate\Http\Request $request
	 */
	public function __construct(PostService $postService, ContactService $contactService, Request $request)
	{
		parent::__construct();
		
		$this->postService = $postService;
		$this->contactService = $contactService;
		$this->request = $request;
	}
	
	/**
	 * @param array|null $sidebar
	 * @return void
	 */
	protected function bindSidebarVariables(?array $sidebar = []): void
	{
		if (!empty($sidebar)) {
			foreach ($sidebar as $key => $value) {
				view()->share($key, $value);
			}
		}
	}
	
	/**
	 * Set the Open Graph info
	 *
	 * @param $og
	 * @param $title
	 * @param $description
	 * @param array|null $apiExtra
	 * @return void
	 */
	protected function setOgInfo($og, $title, $description, ?array $apiExtra = null): void
	{
		$og->title($title)->description($description)->type('website');
		
		// If listings found, then remove the fallback image
		$doesListingsFound = (is_array($apiExtra) && (int)data_get($apiExtra, 'count.0') > 0);
		if ($doesListingsFound) {
			if ($og->has('image')) {
				$og->forget('image')->forget('image:width')->forget('image:height');
			}
		}
		
		view()->share('og', $og);
	}
	
	/**
	 * Send Post by Email.
	 *
	 * @param \App\Http\Requests\Front\SendPostByEmailRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function sendByEmail(SendPostByEmailRequest $request): RedirectResponse
	{
		$postId = $request->input('post_id');
		
		// Send the post by email
		$data = getServiceData($this->contactService->sendPostByEmail($postId, $request));
		
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		// Notification Message
		if (data_get($data, 'success')) {
			flash($message)->success();
			
			return redirect()->to(url()->previous());
		} else {
			flash($message)->error();
			
			return redirect()->back()->withInput();
		}
	}
}
