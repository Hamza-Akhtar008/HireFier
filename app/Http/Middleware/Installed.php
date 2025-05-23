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

namespace App\Http\Middleware;

use App\Http\Controllers\Web\Setup\Update\Traits\CleanUpTrait;
use App\Http\Middleware\Install\CheckInstallation;
use App\Http\Middleware\Install\CheckPurchaseCode;
use App\Models\Permission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class Installed
{
	use CheckInstallation, CheckPurchaseCode, CleanUpTrait;
	
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @param null $guard
	 * @return \Illuminate\Http\RedirectResponse|mixed
	 * @throws \App\Exceptions\Custom\CustomException
	 * @throws \App\Exceptions\Custom\InvalidPurchaseCode
	 */
	public function handle(Request $request, Closure $next, $guard = null)
	{
		if (isFromApi()) {
			return $this->handleApi($request, $next);
		} else {
			return $this->handleWeb($request, $next, $guard);
		}
	}
	
	/**
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return \Illuminate\Http\JsonResponse|mixed
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private function handleApi(Request $request, Closure $next)
	{
		// Since the Admin panel doesn't call the API,
		// skip requests from there to allow admins to log in to into it.
		if (request()->hasHeader('X-WEB-REQUEST-URL')) {
			if (isFromAdminPanel(request()->header('X-WEB-REQUEST-URL'))) {
				return $next($request);
			}
		}
		
		if ($this->isNotInstalled()) {
			$url = url('install');
			$url = request()->hasHeader('X-WEB-REQUEST-URL')
				? '<a href="' . $url . '">' . $url . '</a>'
				: '"' . $url . '"';
			
			$message = 'The application is not installed. ';
			$message .= 'Please install it by visiting the URL ' . $url . ' from a web browser.';
			
			$data = [
				'success' => false,
				'message' => $message,
				'extra'   => ['error' => ['type' => 'install']],
			];
			
			return apiResponse()->json($data, Response::HTTP_FORBIDDEN);
		}
		
		if (updateIsAvailable()) {
			$url = url('upgrade');
			$url = request()->hasHeader('X-WEB-REQUEST-URL')
				? '<a href="' . $url . '">' . $url . '</a>'
				: '"' . $url . '"';
			
			$message = 'Your application needs to be upgraded. ';
			$message .= 'To achieve this, visit the URL ' . $url . ' in a web browser and follow the steps.';
			
			$data = [
				'success' => false,
				'message' => $message,
				'extra'   => ['error' => ['type' => 'upgrade']],
			];
			
			return apiResponse()->json($data, Response::HTTP_FORBIDDEN);
		}
		
		return $next($request);
	}
	
	/**
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @param null $guard
	 * @return \Illuminate\Http\RedirectResponse|mixed
	 * @throws \App\Exceptions\Custom\CustomException
	 * @throws \App\Exceptions\Custom\InvalidPurchaseCode
	 */
	private function handleWeb(Request $request, Closure $next, $guard = null)
	{
		// Check if an update is available
		if (updateIsAvailable()) {
			if (auth()->check()) {
				$authUser = auth()->guard($guard)->user();
				$aclTableNames = config('permission.table_names');
				if (isset($aclTableNames['permissions'])) {
					if (Schema::hasTable($aclTableNames['permissions'])) {
						if (
							doesUserHavePermission($authUser, Permission::getStaffPermissions())
							&& !isDemoDomain()
						) {
							return redirect()->to(getRawBaseUrl() . '/upgrade');
						}
					}
				}
			} else {
				// Clear all the cache (TMP)
				$this->clearCache();
			}
		}
		
		if ($this->isNotInstalled()) {
			return redirect()->to(getRawBaseUrl() . '/install');
		}
		
		$this->checkPurchaseCode();
		
		return $next($request);
	}
}
