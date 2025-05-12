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

namespace App\Http\Controllers\Web\Front\Account;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class OverviewController extends AccountBaseController
{
	/**
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function index(): View|RedirectResponse
	{
		$authUser = auth()->user();
		
		// User Type missing notification
		if (empty($authUser->user_type_id)) {
			flash(t('select_a_user_type_to_start'))->warning();
			
			return redirect()->to(urlGen()->accountProfile());
		}
		
		$appName = config('settings.app.name', 'Site Name');
		$title = t('my_account') . ' - ' . $appName;
		$description = t('my_account_on', ['appName' => config('settings.app.name')]);
		
		// Meta Tags
		MetaTag::set('title', $title);
		MetaTag::set('description', $description);
		
		return view('front.account.overview');
	}
}
