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

namespace App\Http\Controllers\Web\Front;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Front\Traits\CommonTrait;
use App\Http\Controllers\Web\Front\Traits\EnvFileTrait;
use App\Http\Controllers\Web\Front\Traits\RobotsTxtTrait;
use App\Http\Controllers\Web\Front\Traits\SettingsTrait;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FrontController extends Controller
{
	use SettingsTrait, EnvFileTrait, RobotsTxtTrait, CommonTrait;
	
	public Request $request;
	public array $data = [];
	protected Collection $userMenu;
	
	public function __construct()
	{
		// Set the storage disk
		$this->setStorageDisk();
		
		// Check & Change the App Key (If needed)
		$this->checkAndGenerateAppKey();
		
		// Check & Update the '/.env' file
		$this->checkDotEnvEntries();
		
		// Check & Update the '/public/robots.txt' file
		$this->checkRobotsTxtFile();
		
		// Load Localization Data first
		// Check out the SetCountryLocale Middleware
		$this->applyFrontSettings();
		
		// Get & Share Users Menu
		$this->userMenu = $this->getUserMenu();
		view()->share('userMenu', $this->userMenu);
	}
	
	/**
	 * Get the middleware that should be assigned to the controller.
	 */
	public static function middleware(): array
	{
		$array = [];
		
		// Add the 'Domain Mapping' plugin middleware
		if (config('plugins.domainmapping.installed')) {
			$array[] = 'domain.verification';
		}
		
		return array_merge(parent::middleware(), $array);
	}
	
	/**
	 * @return \Illuminate\Support\Collection
	 */
	private function getUserMenu(): Collection
	{
		if (!auth()->check()) {
			return collect();
		}
		
		$authUser = auth()->user();
		$isMultipleCompaniesPerUserEnabled = isMultipleCompaniesPerUserEnabled();
		
		$menuArray = [];
		
		if (isset($authUser->user_type_id)) {
			// company
			if ($authUser->user_type_id == 1) {
				$menuArray = [
					[
						'name'       => $isMultipleCompaniesPerUserEnabled ? t('my_companies') : t('my_company'),
						'url'        => url(urlGen()->getAccountBasePath() . '/companies'),
						'icon'       => 'fa-regular fa-building',
						'group'      => t('my_listings'),
						'countVar'   => $isMultipleCompaniesPerUserEnabled ? 'companies' : null,
						'inDropdown' => true,
						'isActive'   => (request()->segment(2) == 'companies'),
					],
					[
						'name'       => t('my_listings'),
						'url'        => url(urlGen()->getAccountBasePath() . '/posts/list'),
						'icon'       => 'fa-solid fa-list',
						'group'      => t('my_listings'),
						'countVar'   => 'posts.published',
						'inDropdown' => true,
						'isActive'   => (request()->segment(3) == 'list'),
					],
					[
						'name'       => t('pending_approval'),
						'url'        => url(urlGen()->getAccountBasePath() . '/posts/pending-approval'),
						'icon'       => 'fa-solid fa-hourglass-half',
						'group'      => t('my_listings'),
						'countVar'   => 'posts.pendingApproval',
						'inDropdown' => true,
						'isActive'   => (request()->segment(3) == 'pending-approval'),
					],
					[
						'name'       => t('archived_ads'),
						'url'        => url(urlGen()->getAccountBasePath() . '/posts/archived'),
						'icon'       => 'fa-solid fa-calendar-xmark',
						'group'      => t('my_listings'),
						'countVar'   => 'posts.archived',
						'inDropdown' => false,
						'isActive'   => (request()->segment(3) == 'archived'),
					],
					[
						'name'             => t('messenger'),
						'url'              => url(urlGen()->getAccountBasePath() . '/messages'),
						'icon'             => 'fa-regular fa-envelope',
						'group'            => t('my_listings'),
						'countVar'         => 0,
						'countCustomClass' => ' count-threads-with-new-messages',
						'inDropdown'       => true,
						'isActive'         => (request()->segment(2) == 'messages'),
					],
					[
						'name'       => t('promotion'),
						'url'        => url(urlGen()->getAccountBasePath() . '/transactions/promotion'),
						'icon'       => 'fa-solid fa-coins',
						'group'      => t('Transactions'),
						'countVar'   => 'transactions.promotion',
						'inDropdown' => false,
						'isActive'   => (request()->segment(2) == 'transactions' && request()->segment(3) == 'promotion'),
					],
					[
						'name'       => t('subscription'),
						'url'        => url(urlGen()->getAccountBasePath() . '/transactions/subscription'),
						'icon'       => 'fa-solid fa-coins',
						'group'      => t('Transactions'),
						'countVar'   => 'transactions.subscription',
						'inDropdown' => false,
						'isActive'   => (request()->segment(2) == 'transactions' && request()->segment(3) == 'subscription'),
					],
				];
			}
			
			// job seeker
			if ($authUser->user_type_id == 2) {
				$menuArray = [
					[
						'name'       => t('my_resumes'),
						'url'        => url(urlGen()->getAccountBasePath() . '/resumes'),
						'icon'       => 'fa-solid fa-paperclip',
						'group'      => t('my_listings'),
						'countVar'   => 'resumes',
						'inDropdown' => true,
						'isActive'   => (request()->segment(2) == 'resumes'),
					],
					[
						'name'       => t('Favourite jobs'),
						'url'        => url(urlGen()->getAccountBasePath() . '/saved-posts'),
						'icon'       => 'fa-solid fa-bookmark',
						'group'      => t('my_listings'),
						'countVar'   => 'posts.favourite',
						'inDropdown' => true,
						'isActive'   => (request()->segment(2) == 'saved-posts'),
					],
					[
						'name'       => t('saved_searches'),
						'url'        => url(urlGen()->getAccountBasePath() . '/saved-searches'),
						'icon'       => 'fa-solid fa-bell',
						'group'      => t('my_listings'),
						'countVar'   => 'savedSearch',
						'inDropdown' => false,
						'isActive'   => (request()->segment(2) == 'saved-searches'),
					],
					[
						'name'             => t('messenger'),
						'url'              => url(urlGen()->getAccountBasePath() . '/messages'),
						'icon'             => 'fa-regular fa-envelope',
						'group'            => t('my_listings'),
						'countVar'         => 0,
						'countCustomClass' => ' count-threads-with-new-messages',
						'inDropdown'       => true,
						'isActive'         => (request()->segment(2) == 'messages'),
					],
				];
			}
		}
		
		$myAccount = [
			'name'       => t('my_account'),
			'url'        => urlGen()->accountOverview(),
			'icon'       => 'fa-solid fa-gear',
			'group'      => t('my_account'),
			'countVar'   => null,
			'inDropdown' => true,
			'isActive'   => (
				(request()->segment(1) == urlGen()->getAccountBasePath() && request()->segment(2) == null)
				|| request()->segment(2) == 'overview'
			),
		];
		$menuArray[] = $myAccount;
		
		$profile = [
			'name'       => trans('auth.profile'),
			'url'        => urlGen()->accountProfile(),
			'icon'       => 'bi bi-person-circle',
			'group'      => t('my_account'),
			'countVar'   => null,
			'inDropdown' => true,
			'isActive'   => (request()->segment(2) == 'profile'),
		];
		$menuArray[] = $profile;
		
		$security = [
			'name'       => trans('auth.security'),
			'url'        => urlGen()->accountSecurity(),
			'icon'       => 'bi bi-shield-lock',
			'group'      => t('my_account'),
			'countVar'   => null,
			'inDropdown' => true,
			'isActive'   => (request()->segment(2) == 'security'),
		];
		$menuArray[] = $security;
		
		$preferences = [
			'name'       => trans('auth.preferences'),
			'url'        => urlGen()->accountPreferences(),
			'icon'       => 'bi bi-sliders',
			'group'      => t('my_account'),
			'countVar'   => null,
			'inDropdown' => true,
			'isActive'   => (request()->segment(2) == 'preferences'),
		];
		$menuArray[] = $preferences;
		
		if (socialLogin()->isEnabled()) {
			$connections = [
				'name'       => trans('auth.linked_accounts'),
				'url'        => urlGen()->accountLinkedAccounts(),
				'icon'       => 'bi bi-plugin',
				'group'      => t('my_account'),
				'countVar'   => null,
				'inDropdown' => false,
				'isActive'   => (request()->segment(2) == 'connections'),
			];
			$menuArray[] = $connections;
		}
		
		if (app('impersonate')->isImpersonating()) {
			$logOut = [
				'name'       => t('Leave'),
				'url'        => route('impersonate.leave'),
				'icon'       => 'bi bi-box-arrow-right',
				'group'      => t('my_account'),
				'countVar'   => null,
				'inDropdown' => true,
				'isActive'   => false,
			];
		} else {
			$logOut = [
				'name'       => trans('auth.log_out'),
				'url'        => urlGen()->signOut(),
				'icon'       => 'bi bi-box-arrow-right',
				'group'      => t('my_account'),
				'countVar'   => null,
				'inDropdown' => true,
				'isActive'   => false,
			];
		}
		
		$closeAccount = [];
		if (isAccountClosureEnabled()) {
			$closeAccount = [
				'name'       => t('close_account'),
				'url'        => urlGen()->accountClosing(),
				'icon'       => 'fa-solid fa-circle-xmark',
				'group'      => t('my_account'),
				'countVar'   => null,
				'inDropdown' => false,
				'isActive'   => (request()->segment(2) == 'closing'),
			];
		}
		
		$adminPanel = [];
		if (doesUserHavePermission($authUser, Permission::getStaffPermissions())) {
			$adminPanel = [
				'name'       => t('admin_panel'),
				'url'        => urlGen()->adminUrl('/'),
				'icon'       => 'fa-solid fa-gears',
				'group'      => t('admin_panel'),
				'countVar'   => null,
				'inDropdown' => true,
				'isActive'   => false,
			];
		}
		
		// Merge all arrays
		array_push($menuArray, $logOut, $closeAccount, $adminPanel);
		
		// Set missed information
		return collect($menuArray)
			->reject(fn ($item) => empty($item))
			->map(function ($item) {
				// countCustomClass
				$item['countCustomClass'] = $item['countCustomClass'] ?? '';
				
				$accountBasePath = urlGen()->getAccountBasePath();
				
				// path
				// |(account.*)|ui
				$matches = [];
				preg_match('|(' . $accountBasePath . '.*)|ui', $item['url'], $matches);
				$item['path'] = $matches[1] ?? '-1';
				$item['path'] = str_replace([$accountBasePath, '/'], '', $item['path']);
				
				return $item;
			});
	}
}
