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

use App\Http\Requests\Front\CompanyRequest;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;

/**
 * @group Companies
 */
class CompanyController extends BaseController
{
	protected CompanyService $companyService;
	
	/**
	 * @param \App\Services\CompanyService $companyService
	 */
	public function __construct(CompanyService $companyService)
	{
		parent::__construct();
		
		$this->companyService = $companyService;
	}
	
	/**
	 * List companies
	 *
	 * @queryParam hasPosts boolean Do entries have Post(s)? - Possible value: 0 or 1. Example: 0
	 * @queryParam countPosts boolean Count posts number for each entry? - Possible value: 0 or 1. Example: 0
	 * @queryParam belongLoggedUser boolean Force users to be logged to get data that belongs to him - Possible value: 0 or 1. Example: 0
	 * @queryParam q string Get the company list related to the entered keyword. Example: null
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
			'hasPosts'         => (request()->integer('hasPosts') == 1),
			'countPosts'       => (request()->integer('countPosts') == 1),
			'belongLoggedUser' => (request()->integer('belongLoggedUser') == 1),
			'keyword'          => request()->input('q', request()->input('keyword')),
		];
		
		return $this->companyService->getEntries($params);
	}
	
	/**
	 * Get company
	 *
	 * @queryParam belongLoggedUser boolean Check if entry is belonged the logged user - Possible value: 0 or 1. Example: 0
	 * @queryParam embed string The Comma-separated list of the company relationships for Eager Loading - Possible values: user,city,subAdmin1,subAdmin2. Example: user
	 *
	 * @urlParam id int required The company's ID. Example: 44
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
		
		return $this->companyService->getEntry($id, $params);
	}
	
	/**
	 * Store company
	 *
	 * @authenticated
	 * @header Authorization Bearer {YOUR_AUTH_TOKEN}
	 *
	 * @bodyParam company[].country_code string required The code of the company's country. Example: US
	 * @bodyParam company[].name string required The company's name. Example: Foo Inc
	 * @bodyParam company[].logo_path file The company's logo.
	 * @bodyParam company[].description string required The company's description. Example: Nostrum quia est aut quas. Consequuntur ut quis odit voluptatem laborum quia.
	 * @bodyParam company[].city_id int The company city's ID.
	 * @bodyParam company[].address string The company's address. Example: 5 rue de l'Echelle
	 * @bodyParam company[].phone string The company's phone number. Example: +17656766467
	 * @bodyParam company[].fax string The company's fax number. Example: +80159266712
	 * @bodyParam company[].email string The company's email address. Example: contact@domain.tld
	 * @bodyParam company[].website string The company's website URL. Example: https://domain.tld
	 * @bodyParam company[].facebook string The company's Facebook URL.
	 * @bodyParam company[].twitter string The company's Twitter URL.
	 * @bodyParam company[].linkedin string The company's LinkedIn URL.
	 * @bodyParam company[].pinterest string The company's Pinterest URL.
	 *
	 * @param \App\Http\Requests\Front\CompanyRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(CompanyRequest $request): JsonResponse
	{
		return $this->companyService->store($request);
	}
	
	/**
	 * Update company
	 *
	 * @authenticated
	 * @header Authorization Bearer {YOUR_AUTH_TOKEN}
	 *
	 * @bodyParam company[].country_code string required The code of the company's country. Example: US
	 * @bodyParam company[].name string required The company's name. Example: Foo Inc
	 * @bodyParam company[].logo_path file The company's logo.
	 * @bodyParam company[].description string required The company's description. Example: Nostrum quia est aut quas. Consequuntur ut quis odit voluptatem laborum quia.
	 * @bodyParam company[].city_id int The company city's ID.
	 * @bodyParam company[].address string The company's address. Example: 5 rue de l'Echelle
	 * @bodyParam company[].phone string The company's phone number. Example: +17656766467
	 * @bodyParam company[].fax string The company's fax number. Example: +80159266712
	 * @bodyParam company[].email string The company's email address. Example: contact@domain.tld
	 * @bodyParam company[].website string The company's website URL. Example: https://domain.tld
	 * @bodyParam company[].facebook string The company's Facebook URL.
	 * @bodyParam company[].twitter string The company's Twitter URL.
	 * @bodyParam company[].linkedin string The company's LinkedIn URL.
	 * @bodyParam company[].pinterest string The company's Pinterest URL.
	 *
	 * @urlParam id int required The company's ID. Example: 111111
	 *
	 * @param $id
	 * @param \App\Http\Requests\Front\CompanyRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($id, CompanyRequest $request): JsonResponse
	{
		return $this->companyService->update($id, $request);
	}
	
	/**
	 * Delete company(ies)
	 *
	 * @authenticated
	 * @header Authorization Bearer {YOUR_AUTH_TOKEN}
	 *
	 * @urlParam ids string required The ID or comma-separated IDs list of company(ies). Example: 111111,222222,333333
	 *
	 * @param string $ids
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy(string $ids): JsonResponse
	{
		return $this->companyService->destroy($ids);
	}
}
