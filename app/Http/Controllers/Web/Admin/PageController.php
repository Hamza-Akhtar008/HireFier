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
use App\Http\Requests\Admin\PageRequest as StoreRequest;
use App\Http\Requests\Admin\PageRequest as UpdateRequest;
use App\Http\Requests\Admin\Request;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Throwable;

class PageController extends PanelController
{
	public function setup()
	{
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel(Page::class);
		$this->xPanel->setRoute(urlGen()->adminUri('pages'));
		$this->xPanel->setEntityNameStrings(trans('admin.page'), trans('admin.pages'));
		$this->xPanel->enableReorder('name', 1);
		$this->xPanel->allowAccess(['reorder']);
		if (!request()->input('order')) {
			$this->xPanel->orderBy('lft');
		}
		
		$this->xPanel->addButtonFromModelFunction('top', 'bulk_activation_button', 'bulkActivationButton', 'end');
		$this->xPanel->addButtonFromModelFunction('top', 'bulk_deactivation_button', 'bulkDeactivationButton', 'end');
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
				$this->xPanel->addClause('where', function ($query) use ($value) {
					$query->where('name', 'LIKE', "%$value%")
						->orWhere('title', 'LIKE', "%$value%");
				});
			}
		);
		// -----------------------
		$this->xPanel->addFilter(
			[
				'name'  => 'status',
				'type'  => 'dropdown',
				'label' => trans('admin.Status'),
			],
			[
				1 => trans('admin.Activated'),
				2 => trans('admin.Unactivated'),
			],
			function ($value) {
				if ($value == 1) {
					$this->xPanel->addClause('where', 'active', '=', 1);
				}
				if ($value == 2) {
					$this->xPanel->addClause('where', fn ($query) => $query->columnIsEmpty('active'));
				}
			}
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
			'name'          => 'name',
			'label'         => trans('admin.Name'),
			'type'          => 'model_function',
			'function_name' => 'getNameHtml',
		]);
		$this->xPanel->addColumn([
			'name'  => 'title',
			'label' => trans('admin.Title'),
		]);
		$this->xPanel->addColumn([
			'name'          => 'active',
			'label'         => trans('admin.Active'),
			'type'          => "model_function",
			'function_name' => 'getActiveHtml',
			'on_display'    => 'checkbox',
		]);
		
		// FIELDS
		$this->xPanel->addField([
			'name'       => 'name',
			'label'      => trans('admin.Name'),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans('admin.Name'),
			],
		]);
		$this->xPanel->addField([
			'name'       => 'slug',
			'label'      => trans('admin.Slug'),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans('admin.Will be automatically generated from your name, if left empty'),
			],
			'hint'       => trans('admin.Will be automatically generated from your name, if left empty'),
			'wrapper'    => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'       => 'external_link',
			'label'      => trans('admin.External Link'),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => "http://",
			],
			'hint'       => trans('admin.Redirect this page to the URL above') . ' ' . trans('admin.Leave this field empty if you do not want redirect this page'),
			'wrapper'    => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'       => 'title',
			'label'      => trans('admin.Title'),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans('admin.Title'),
			],
		]);
		$wysiwygEditor = config('settings.other.wysiwyg_editor');
		$wysiwygEditorViewPath = '/views/admin/panel/fields/' . $wysiwygEditor . '.blade.php';
		$this->xPanel->addField([
			'name'       => 'content',
			'label'      => trans('admin.Content'),
			'type'       => ($wysiwygEditor != 'none' && file_exists(resource_path() . $wysiwygEditorViewPath))
				? $wysiwygEditor
				: 'textarea',
			'attributes' => [
				'placeholder' => trans('admin.Content'),
				'id'          => 'pageContent',
				'rows'        => 20,
			],
		]);
		$this->xPanel->addField([
			'name'  => 'type',
			'label' => trans('admin.type'),
			'type'  => 'enum',
		]);
		$this->xPanel->addField([
			'name'   => 'image_path',
			'label'  => trans('admin.Picture'),
			'type'   => 'image',
			'upload' => true,
			'disk'   => 'public',
		]);
		$this->xPanel->addField([
			'name'    => 'name_color',
			'label'   => trans('admin.Page Name Color'),
			'type'    => 'color_picker',
			'wrapper' => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'    => 'title_color',
			'label'   => trans('admin.Page Title Color'),
			'type'    => 'color_picker',
			'wrapper' => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'  => 'target_blank',
			'label' => trans('admin.Open the link in new window'),
			'type'  => 'checkbox_switch',
		]);
		
		$this->xPanel->addField([
			'name'  => 'seo_tags',
			'type'  => 'custom_html',
			'value' => '<br><h4 style="margin-bottom: 0;">' . trans('admin.seo_tags') . '</h4>',
		]);
		$this->xPanel->addField([
			'name'  => 'seo_start',
			'type'  => 'custom_html',
			'value' => '<hr style="border: 1px dashed #EFEFEF; margin-top: 0; margin-bottom: 2px;">',
		]);
		$this->xPanel->addField([
			'name'  => 'dynamic_variables_hint',
			'type'  => 'custom_html',
			'value' => trans('admin.dynamic_variables_hint'),
		]);
		$this->xPanel->addField([
			'name'       => 'seo_title',
			'label'      => trans('admin.Title'),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans('admin.Title'),
			],
			'hint'       => trans('admin.seo_title_hint'),
		]);
		$this->xPanel->addField([
			'name'       => 'seo_description',
			'label'      => trans('admin.Description'),
			'type'       => 'textarea',
			'attributes' => [
				'placeholder' => trans('admin.Description'),
			],
			'hint'       => trans('admin.seo_description_hint'),
		]);
		$this->xPanel->addField([
			'name'       => 'seo_keywords',
			'label'      => trans('admin.Keywords'),
			'type'       => 'textarea',
			'attributes' => [
				'placeholder' => trans('admin.Keywords'),
			],
			'hint'       => trans('admin.comma_separated_hint') . ' ' . trans('admin.seo_keywords_hint'),
		]);
		
		$this->xPanel->addField([
			'name'  => 'seo_end',
			'type'  => 'custom_html',
			'value' => '<hr style="border: 1px dashed #EFEFEF;">',
		]);
		
		$this->xPanel->addField([
			'name'  => 'excluded_from_footer',
			'label' => trans('admin.Exclude from footer'),
			'type'  => 'checkbox_switch',
		]);
		$this->xPanel->addField([
			'name'  => 'active',
			'label' => trans('admin.Active'),
			'type'  => 'checkbox_switch',
		]);
	}
	
	public function store(StoreRequest $request): RedirectResponse
	{
		try {
			$request = $this->uploadFile($request);
		} catch (Throwable $e) {
		}
		
		return parent::storeCrud($request);
	}
	
	/**
	 * @param \App\Http\Requests\Admin\Request $request
	 * @return \App\Http\Requests\Admin\Request
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private function uploadFile(Request $request): Request
	{
		$params = [
			[
				'attribute' => 'image_path',
				'destPath'  => 'app/page',
				'width'     => (int)config('larapen.media.resize.namedOptions.bg-header.width', 2000),
				'height'    => (int)config('larapen.media.resize.namedOptions.bg-header.height', 1000),
				'quality'   => 100,
			],
		];
		
		foreach ($params as $param) {
			// Get uploaded image file (should return an UploadedFile object)
			$file = $request->file($param['attribute'], $request->input($param['attribute']));
			
			// Upload the image & get its local path
			$imagePath = Upload::image($file, $param['destPath'], $param);
			
			// Set the local path in the input
			$request->merge([$param['attribute'] => $imagePath]);
		}
		
		return $request;
	}
	
	public function update(UpdateRequest $request): RedirectResponse
	{
		try {
			$request = $this->uploadFile($request);
		} catch (Throwable $e) {
		}
		
		return parent::updateCrud($request);
	}
}
