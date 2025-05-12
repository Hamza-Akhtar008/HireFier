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

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\Common\Files\Upload;
use App\Http\Controllers\Web\Admin\Panel\PanelController;
use App\Http\Requests\Admin\CompanyRequest as StoreRequest;
use App\Http\Requests\Admin\CompanyRequest as UpdateRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;

class CompanyController extends PanelController
{
	public function setup()
	{
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel(Company::class);
		$this->xPanel->setRoute(urlGen()->adminUri('companies'));
		$this->xPanel->setEntityNameStrings(trans('admin.company'), trans('admin.companies'));
		$this->xPanel->denyAccess(['create']);
		if (!request()->input('order')) {
			$this->xPanel->orderByDesc('id');
		}
		
		$this->xPanel->addButtonFromModelFunction('top', 'bulk_deletion_button', 'bulkDeletionButton', 'end');
		
		// Filters
		// -----------------------
		$this->xPanel->disableSearchBar();
		// -----------------------
		$this->xPanel->addFilter(
			[
				'name'  => 'name',
				'type'  => 'text',
				'label' => mb_ucfirst(trans('admin.Name')),
			],
			false,
			function ($value) {
				$this->xPanel->addClause('where', 'name', 'LIKE', "%$value%");
				$this->xPanel->addClause('orWhere', 'description', 'LIKE', "%$value%");
			}
		);
		// -----------------------
		$this->xPanel->addFilter(
			[
				'name'  => 'country',
				'type'  => 'select2',
				'label' => mb_ucfirst(trans('admin.Country')),
			],
			getCountries(),
			fn ($value) => $this->xPanel->addClause('where', 'country_code', '=', $value)
		);
		
		/*
		|--------------------------------------------------------------------------
		| COLUMNS AND FIELDS
		|--------------------------------------------------------------------------
		*/
		// COLUMNS
		$this->xPanel->addColumn([
			'name'      => 'id',
			'label'     => '',
			'type'      => 'checkbox',
			'orderable' => false,
		]);
		$this->xPanel->addColumn([
			'name'          => 'logo_path', // Put unused field column
			'label'         => trans('admin.Logo'),
			'type'          => 'model_function',
			'function_name' => 'getLogoHtml',
		]);
		$this->xPanel->addColumn([
			'name'          => 'name',
			'label'         => trans('admin.Name'),
			'type'          => 'model_function',
			'function_name' => 'getNameHtml',
		]);
		$this->xPanel->addColumn([
			'name'  => 'description',
			'label' => trans('admin.Description'),
		]);
		$this->xPanel->addColumn([
			'name'          => 'country_code',
			'label'         => trans('admin.Country'),
			'type'          => 'model_function',
			'function_name' => 'getCountryHtml',
		]);
		
		// FIELDS
		$this->xPanel->addField([
			'name'       => 'name',
			'label'      => trans('admin.company_name'),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans('admin.company_name'),
			],
		]);
		$this->xPanel->addField([
			'name'   => 'logo_path',
			'label'  => trans('admin.Logo'),
			'type'   => 'image',
			'upload' => true,
			'disk'   => 'public',
			'hint'   => t('file_types', ['file_types' => getAllowedFileFormatsHint('image')]),
		]);
		$this->xPanel->addField([
			'name'       => 'description',
			'label'      => trans('admin.Company Description'),
			'type'       => 'textarea',
			'attributes' => [
				'placeholder' => trans('admin.Company Description'),
				'rows'        => 10,
			],
		]);
		$this->xPanel->addField([
			'name'       => "address",
			'label'      => trans('admin.Address'),
			'type'       => "text",
			'attributes' => [
				'placeholder' => trans('admin.Address'),
			],
		]);
		$this->xPanel->addField([
			'name'       => 'phone',
			'label'      => trans('admin.Phone'),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans('admin.Phone'),
			],
			'wrapper'    => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'       => 'fax',
			'label'      => trans('admin.Fax'),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans('admin.Fax'),
			],
			'wrapper'    => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'       => 'email',
			'label'      => trans('admin.User Email'),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans('admin.User Email'),
			],
			'wrapper'    => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'       => 'website',
			'label'      => trans('admin.Company Website'),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans('admin.Company Website'),
			],
			'wrapper'    => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'       => "facebook",
			'label'      => 'Facebook',
			'type'       => "text",
			'attributes' => [
				'placeholder' => 'Facebook',
			],
			'wrapper'    => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'       => "twitter",
			'label'      => 'Twitter',
			'type'       => "text",
			'attributes' => [
				'placeholder' => 'Twitter',
			],
			'wrapper'    => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'       => "linkedin",
			'label'      => 'Linkedin',
			'type'       => "text",
			'attributes' => [
				'placeholder' => 'Linkedin',
			],
			'wrapper'    => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'       => "pinterest",
			'label'      => 'Pinterest',
			'type'       => "text",
			'attributes' => [
				'placeholder' => 'Pinterest',
			],
			'wrapper'    => [
				'class' => 'col-md-6',
			],
		]);
	}
	
	public function store(StoreRequest $request): RedirectResponse
	{
		return parent::storeCrud($request);
	}
	
	public function update(UpdateRequest $request): RedirectResponse
	{
		$request = $this->uploadLogo($request);
		
		return parent::updateCrud($request);
	}
	
	private function uploadLogo($request)
	{
		$company = null;
		
		// update
		$companyId = request()->segment(3);
		if (!empty($companyId) && is_numeric($companyId)) {
			$company = Company::find($companyId);
		}
		
		// create
		if (empty($company)) {
			$companyId = request()->input('company_id');
			if (!empty($companyId) && is_numeric($companyId)) {
				$company = Company::find($companyId);
			}
		}
		
		if (!empty($company)) {
			$attribute = 'logo_path';
			$file = $request->hasFile($attribute) ? $request->file($attribute) : $request->input($attribute);
			
			if (!empty($file)) {
				$param = [
					'destPath' => 'files/' . strtolower($company->country_code) . '/' . $company->id,
					'width'    => (int)config('larapen.media.resize.namedOptions.company-logo.width', 800),
					'height'   => (int)config('larapen.media.resize.namedOptions.company-logo.height', 800),
					'ratio'    => config('larapen.media.resize.namedOptions.company-logo.ratio', '1'),
					'upsize'   => config('larapen.media.resize.namedOptions.company-logo.upsize', '1'),
				];
				$logoPath = Upload::image($file, $param['destPath'], $param);
				$request->request->set($attribute, $logoPath);
			}
		}
		
		return $request;
	}
}
