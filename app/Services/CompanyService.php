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

// Increase the server resources
$iniConfigFile = __DIR__ . '/../../../Helpers/Common/Functions/ini.php';
if (file_exists($iniConfigFile)) {
	$configForUpload = true;
	include_once $iniConfigFile;
}

use App\Http\Requests\Front\CompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\EntityCollection;
use App\Jobs\GenerateLogoCollectionThumbnails;
use App\Jobs\GenerateLogoThumbnails;
use App\Models\Company;
use App\Services\Company\SaveCompany;
use Illuminate\Http\JsonResponse;
use Throwable;

class CompanyService extends BaseService
{
	use SaveCompany;
	
	/**
	 * List companies
	 *
	 * @param array $params
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getEntries(array $params = []): JsonResponse
	{
		$perPage = getNumberOfItemsPerPage('companies', $params['perPage'] ?? null, $this->perPage);
		$embed = getCommaSeparatedStrAsArray($params['embed'] ?? []);
		$doEntriesHavePosts = getIntAsBoolean($params['hasPosts'] ?? 0);
		$isWithCountPosts = getIntAsBoolean($params['countPosts'] ?? 0);
		$isBelongLoggedUser = getIntAsBoolean($params['belongLoggedUser'] ?? 0);
		$keyword = $params['keyword'] ?? null;
		$sort = $params['sort'] ?? [];
		
		$isListingsReviewEnabled = (config('settings.listing_form.listings_review_activation') == '1');
		
		// Non Cached Query
		$companies = Company::query()->with(['user', 'user.permissions', 'user.roles']);
		
		if ($doEntriesHavePosts) {
			$companies->whereHas('posts', function ($query) {
				$query->inCountry()->verified()->unarchived();
				if (config('settings.listing_form.listings_review_activation')) {
					$query->reviewed();
				}
			});
		}
		
		if ($isWithCountPosts) {
			$companies->withCount([
				'posts' => function ($query) use ($isListingsReviewEnabled) {
					$query->inCountry()->verified()->unarchived();
					if ($isListingsReviewEnabled) {
						$query->reviewed();
					}
				},
			]);
		}
		
		// Apply search filter
		if (!empty($keyword)) {
			$keywords = rawurldecode($keyword);
			$companies->where(function ($query) use ($keywords) {
				$query->where('name', 'LIKE', '%' . $keywords . '%')
					->whereOr('description', 'LIKE', '%' . $keywords . '%');
			});
		}
		
		if ($isBelongLoggedUser) {
			$userId = auth(getAuthGuard())->user()?->getAuthIdentifier() ?? '-1';
			$companies->where('user_id', $userId);
		}
		
		// Sorting
		$companies = $this->applySorting($companies, ['created_at', 'name'], $sort);
		
		$companies = $companies->paginate($perPage);
		
		// Generate companies logo thumbnails
		GenerateLogoCollectionThumbnails::dispatch($companies);
		
		// If the request is made from the app's Web environment,
		// use the Web URL as the pagination's base URL
		$companies = setPaginationBaseUrl($companies);
		
		$collection = new EntityCollection(CompanyResource::class, $companies, $params);
		
		$message = ($companies->count() <= 0) ? t('no_companies_found') : null;
		
		return apiResponse()->withCollection($collection, $message);
	}
	
	/**
	 * Get company
	 *
	 * @param $id
	 * @param array $params
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getEntry($id, array $params = []): JsonResponse
	{
		$embed = getCommaSeparatedStrAsArray($params['embed'] ?? []);
		$isBelongLoggedUser = getIntAsBoolean($params['belongLoggedUser'] ?? 0);
		
		$company = Company::query();
		
		if (in_array('user', $embed)) {
			$company->with('user');
		}
		if (in_array('city', $embed)) {
			$company->with('city');
			if (in_array('subAdmin1', $embed)) {
				$company->with('city.subAdmin1');
			}
			if (in_array('subAdmin2', $embed)) {
				$company->with('city.subAdmin2');
			}
		}
		
		if ($isBelongLoggedUser) {
			$userId = auth(getAuthGuard())->user()?->getAuthIdentifier() ?? '-1';
			$company->where('user_id', $userId);
		}
		
		$company = $company->where('id', $id)->first();
		
		if (empty($company)) {
			return apiResponse()->notFound(t('company_not_found'));
		}
		
		// Generate the company's logo thumbnails
		GenerateLogoThumbnails::dispatch($company);
		
		$resource = new CompanyResource($company, $params);
		
		return apiResponse()->withResource($resource);
	}
	
	/**
	 * Store company
	 *
	 * @param \App\Http\Requests\Front\CompanyRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(CompanyRequest $request): JsonResponse
	{
		$authUser = auth(getAuthGuard())->user();
		
		if (empty($authUser)) {
			return apiResponse()->notFound(t('user_not_found'));
		}
		
		// Create Company
		try {
			$company = $this->storeCompany($authUser->getAuthIdentifier(), $request);
		} catch (Throwable $e) {
			return apiResponse()->error($e->getMessage());
		}
		
		$data = [
			'success' => true,
			'message' => t('Your company has created successfully'),
			'result'  => (new CompanyResource($company))->toArray($request),
		];
		
		return apiResponse()->json($data);
	}
	
	/**
	 * Update company
	 *
	 * @param $id
	 * @param \App\Http\Requests\Front\CompanyRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($id, CompanyRequest $request): JsonResponse
	{
		$authUser = auth(getAuthGuard())->user();
		
		if (empty($authUser)) {
			return apiResponse()->notFound(t('user_not_found'));
		}
		
		$company = Company::where('user_id', $authUser->getAuthIdentifier())->where('id', $id)->first();
		
		if (empty($company)) {
			return apiResponse()->notFound(t('company_not_found'));
		}
		
		// Update the Company
		try {
			$company = $this->updateCompany($authUser->getAuthIdentifier(), $request, $company);
		} catch (Throwable $e) {
			return apiResponse()->error($e->getMessage());
		}
		
		$data = [
			'success' => true,
			'message' => t('Your company details has updated successfully'),
			'result'  => (new CompanyResource($company))->toArray($request),
		];
		
		return apiResponse()->json($data);
	}
	
	/**
	 * Delete company(ies)
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
		foreach ($ids as $companyId) {
			$company = Company::query()
				->where('user_id', $authUser->getAuthIdentifier())
				->where('id', $companyId)
				->first();
			
			if (!empty($company)) {
				$res = $company->delete();
			}
		}
		
		// Confirmation
		if ($res) {
			$data['success'] = true;
			
			$count = count($ids);
			if ($count > 1) {
				$data['message'] = t('x entities has been deleted successfully', ['entities' => t('companies'), 'count' => $count]);
			} else {
				$data['message'] = t('1 entity has been deleted successfully', ['entity' => t('company')]);
			}
		}
		
		return apiResponse()->json($data);
	}
}
