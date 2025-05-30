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
use App\Http\Requests\Admin\PostRequest as StoreRequest;
use App\Http\Requests\Admin\PostRequest as UpdateRequest;
use App\Jobs\GenerateLogoThumbnails;
use App\Models\Category;
use App\Models\Company;
use App\Models\Post;
use App\Models\PostType;
use App\Models\SalaryType;
use Illuminate\Http\RedirectResponse;

class PostController extends PanelController
{
	public function setup()
	{
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel(Post::class);
		$this->xPanel->with([
			'user:id,name',
			'city:id,country_code,name',
			'country:code,name',
			// IMPORTANT:
			// Payable can have multiple on-hold, pending, expired, canceled or refunded payments
			// Payable can have only one valid payment, so we have to add that as filter for the Eager Loading.
			// This allows displaying the right payment status (So only when payment is valid).
			'payment' => fn ($query) => $query->valid(),
			'payment.package:id,type,name,short_name',
		]);
		$this->xPanel->withoutAppends();
		$this->xPanel->setRoute(urlGen()->adminUri('posts'));
		$this->xPanel->setEntityNameStrings(trans('admin.ad'), trans('admin.ads'));
		$this->xPanel->denyAccess(['create']);
		if (!request()->input('order')) {
			if (config('settings.listing_form.listings_review_activation') == '1') {
				$this->xPanel->query->orderByUnreviewedFirst();
			}
			$this->xPanel->orderByDesc('created_at');
		}
		// Hard Filters
		if (request()->filled('active')) {
			if (request()->input('active') == 1) {
				$this->xPanel->addClause('where', fn ($query) => $query->verified());
			}
			if (request()->input('active') == 2) {
				$this->xPanel->addClause('where', fn ($query) => $query->unverified());
			}
		}
		
		$this->xPanel->addButtonFromModelFunction('top', 'bulk_approval_button', 'bulkApprovalBtn', 'end');
		$this->xPanel->addButtonFromModelFunction('top', 'bulk_disapproval_button', 'bulkDisapprovalBtn', 'end');
		$this->xPanel->addButtonFromModelFunction('top', 'bulk_deletion_button', 'bulkDeletionButton', 'end');
		
		// Filters
		// -----------------------
		$this->xPanel->disableSearchBar();
		// -----------------------
		$this->xPanel->addFilter(
			[
				'name'  => 'id',
				'type'  => 'text',
				'label' => 'ID/' . t('reference'),
			],
			false,
			function ($value) {
				$value = hashId($value, true) ?? $value;
				$this->xPanel->addClause('where', 'id', '=', $value);
			}
		);
		// -----------------------
		$this->xPanel->addFilter(
			[
				'name'  => 'from_to',
				'type'  => 'date_range',
				'label' => trans('admin.Date range'),
			],
			false,
			function ($value) {
				$dates = json_decode($value);
				if (strlen($dates->from) <= 10) {
					$dates->from = $dates->from . ' 00:00:00';
				}
				if (strlen($dates->to) <= 10) {
					$dates->to = $dates->to . ' 23:59:59';
				}
				$this->xPanel->addClause('where', 'created_at', '>=', $dates->from);
				$this->xPanel->addClause('where', 'created_at', '<=', $dates->to);
			}
		);
		// -----------------------
		$this->xPanel->addFilter(
			[
				'name'  => 'title',
				'type'  => 'text',
				'label' => trans('admin.Title'),
			],
			false,
			function ($value) {
				$this->xPanel->addClause('where', 'title', 'LIKE', "%$value%");
			}
		);
		// -----------------------
		$this->xPanel->addFilter(
			[
				'name'  => 'email',
				'type'  => 'text',
				'label' => trans('auth.email'),
			],
			false,
			function ($value) {
				$this->xPanel->addClause('where', function ($query) use ($value) {
					$query->where('email', 'LIKE', "%$value%");
				});
			}
		);
		// -----------------------
		$this->xPanel->addFilter(
			[
				'name'  => 'country',
				'type'  => 'select2',
				'label' => trans('admin.Country'),
			],
			getCountries(),
			function ($value) {
				$this->xPanel->addClause('where', 'country_code', '=', $value);
			}
		);
		// -----------------------
		$this->xPanel->addFilter(
			[
				'name'  => 'city',
				'type'  => 'text',
				'label' => trans('admin.city'),
			],
			false,
			function ($value) {
				$this->xPanel->query = $this->xPanel->query->whereHas('city', function ($query) use ($value) {
					$query->where('name', 'LIKE', "%$value%");
				});
			}
		);
		// -----------------------
		$this->xPanel->addFilter(
			[
				'name'  => 'has_promotion',
				'type'  => 'dropdown',
				'label' => trans('admin.has_promotion'),
			],
			[
				'pending' => t('pending'),
				'onHold'  => t('onHold'),
				'valid'   => t('valid'),
				'expired' => t('expired'),
				// 'canceled' => t('canceled'),
				// 'refunded' => t('refunded'),
			],
			function ($value) {
				if ($value == 'pending') {
					$this->xPanel->addClause('whereHas', 'payment', function ($query) {
						$query->where(function ($query) {
							$query->valid()->orWhere(fn ($query) => $query->onHold());
						})->columnIsEmpty('active');
					});
				}
				if ($value == 'onHold') {
					$this->xPanel->addClause('whereHas', 'payment', function ($query) {
						$query->onHold()->active();
					});
				}
				if ($value == 'valid') {
					$this->xPanel->addClause('whereHas', 'payment', function ($query) {
						$query->valid()->active();
					});
				}
				if ($value == 'expired') {
					$this->xPanel->addClause('whereHas', 'payment', function ($query) {
						$query->where(function ($query) {
							$query->notValid()->where(fn ($query) => $query->columnIsEmpty('active'));
						})->orWhere(fn ($query) => $query->notValid());
					});
				}
				if ($value == 'canceled') {
					$this->xPanel->addClause('whereHas', 'payment', fn ($query) => $query->canceled());
				}
				if ($value == 'refunded') {
					$this->xPanel->addClause('whereHas', 'payment', fn ($query) => $query->refunded());
				}
			}
		);
		// -----------------------
		if (config('plugins.offlinepayment.installed')) {
			$this->xPanel->addFilter(
				[
					'name'  => 'has_valid_promotion',
					'type'  => 'dropdown',
					'label' => trans('admin.has_valid_promotion'),
				],
				[
					'real' => trans('admin.with_real_payment'),
					'fake' => trans('admin.with_fake_payment'),
				],
				function ($value) {
					if ($value == 'real') {
						$this->xPanel->addClause('whereHas', 'payment', function ($query) {
							$query->valid()->active()->notManuallyCreated();
						});
					}
					if ($value == 'fake') {
						$this->xPanel->addClause('whereHas', 'payment', function ($query) {
							$query->valid()->active()->manuallyCreated();
						});
					}
				}
			);
		}
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
					$this->xPanel->addClause('where', fn ($query) => $query->verified());
				}
				if ($value == 2) {
					$this->xPanel->addClause('where', fn ($query) => $query->unverified());
				}
			}
		);
		
		$isPhoneVerificationEnabled = (config('settings.sms.phone_verification') == 1);
		
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
			'name'  => 'created_at',
			'label' => trans('admin.Date'),
			'type'  => 'datetime',
		]);
		$this->xPanel->addColumn([
			'name'          => 'title',
			'label'         => trans('admin.Title'),
			'type'          => 'model_function',
			'function_name' => 'getTitleHtml',
		]);
		$this->xPanel->addColumn([
			'name'          => 'logo_path', // Put unused field column
			'label'         => trans('admin.Logo'),
			'type'          => 'model_function',
			'function_name' => 'getLogoHtml',
		]);
		$this->xPanel->addColumn([
			'name'          => 'company_name',
			'label'         => trans('admin.company_name'),
			'type'          => 'model_function',
			'function_name' => 'getCompanyNameHtml',
		]);
		$this->xPanel->addColumn([
			'name'          => 'city_id',
			'label'         => trans('admin.city'),
			'type'          => 'model_function',
			'function_name' => 'getCityHtml',
		]);
		if (config('plugins.offlinepayment.installed')) {
			$this->xPanel->addColumn([
				'name'          => 'featured',
				'label'         => mb_ucfirst(trans('offlinepayment::messages.featured')),
				'type'          => 'model_function',
				'function_name' => 'getFeaturedHtml',
			]);
		}
		$this->xPanel->addColumn([
			'name'          => 'email_verified_at',
			'label'         => trans('admin.Verified Email'),
			'type'          => 'model_function',
			'function_name' => 'getVerifiedEmailHtml',
		]);
		if ($isPhoneVerificationEnabled) {
			$this->xPanel->addColumn([
				'name'          => 'phone_verified_at',
				'label'         => trans('admin.Verified Phone'),
				'type'          => 'model_function',
				'function_name' => 'getVerifiedPhoneHtml',
			]);
		}
		if (config('settings.listing_form.listings_review_activation') == '1') {
			$this->xPanel->addColumn([
				'name'          => 'reviewed_at',
				'label'         => trans('admin.Reviewed'),
				'type'          => "model_function",
				'function_name' => 'getReviewedHtml',
			]);
		}
		
		$entity = $this->xPanel->getModel()->find(request()->segment(3));
		
		// FIELDS
		$this->xPanel->addField([
			'label'       => trans('admin.Category'),
			'name'        => 'category_id',
			'type'        => 'select2_from_array',
			'options'     => Category::selectBoxTree(0),
			'allows_null' => false,
		]);
		$this->xPanel->addField([
			'name'       => 'company_name',
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
			'name'       => 'company_description',
			'label'      => trans('admin.Company Description'),
			'type'       => 'textarea',
			'attributes' => [
				'placeholder' => trans('admin.Company Description'),
				'rows'        => 10,
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
		$wysiwygEditor = config('settings.listing_form.wysiwyg_editor');
		$wysiwygEditorViewPath = '/views/admin/panel/fields/' . $wysiwygEditor . '.blade.php';
		$this->xPanel->addField([
			'name'       => 'description',
			'label'      => trans('admin.Description'),
			'type'       => ($wysiwygEditor != 'none' && file_exists(resource_path() . $wysiwygEditorViewPath))
				? $wysiwygEditor
				: 'textarea',
			'attributes' => [
				'placeholder' => trans('admin.Description'),
				'id'          => 'description',
				'rows'        => 10,
			],
		]);
		$this->xPanel->addField([
			'name'       => 'salary_min',
			'label'      => trans('admin.salary_min'),
			'type'       => 'number',
			'attributes' => [
				'min'         => 0,
				'step'        => getInputNumberStep((int)config('currency.decimal_places', 2)),
				'placeholder' => trans('admin.salary_min'),
			],
			'wrapper'    => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'       => 'salary_max',
			'label'      => trans('admin.salary_max'),
			'type'       => 'number',
			'attributes' => [
				'min'         => 0,
				'step'        => getInputNumberStep((int)config('currency.decimal_places', 2)),
				'placeholder' => trans('admin.salary_max'),
			],
			'wrapper'    => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'label'       => trans('admin.Salary Type'),
			'name'        => 'salary_type_id',
			'type'        => 'select2_from_array',
			'options'     => $this->salaryType(),
			'allows_null' => false,
			'wrapper'     => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'    => 'negotiable',
			'label'   => trans('admin.Negotiable Salary'),
			'type'    => 'checkbox_switch',
			'wrapper' => [
				'class' => 'col-md-6',
			],
		]);
		
		$this->xPanel->addField([
			'name'       => 'contact_name',
			'label'      => trans('admin.User Name'),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans('admin.User Name'),
			],
			'wrapper'    => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'        => 'auth_field',
			'label'       => trans('auth.notifications_channel'),
			'type'        => 'select2_from_array',
			'options'     => getAuthFields(),
			'allows_null' => true,
			'default'     => getAuthField($entity),
			'hint'        => t('notifications_channel_hint'),
			'wrapper'     => [
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
			'prefix'     => '<i class="fa-regular fa-envelope"></i>',
			'wrapper'    => [
				'class' => 'col-md-6',
			],
		]);
		$phoneCountry = (!empty($entity) && isset($entity->phone_country)) ? strtolower($entity->phone_country) : 'us';
		$this->xPanel->addField([
			'name'          => 'phone',
			'label'         => trans('admin.User Phone'),
			'type'          => 'intl_tel_input',
			'phone_country' => $phoneCountry,
			'wrapper'       => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'    => 'phone_hidden',
			'label'   => trans('admin.Hide contact phone'),
			'type'    => 'checkbox_switch',
			'wrapper' => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'label'       => trans('admin.Post Type'),
			'name'        => 'post_type_id',
			'type'        => 'select2_from_array',
			'options'     => $this->postType(),
			'allows_null' => false,
			'wrapper'     => [
				'class' => 'col-md-6',
			],
		]);
		$tags = (!empty($entity) && isset($entity->tags)) ? (array)$entity->tags : [];
		$this->xPanel->addField([
			'name'            => 'tags',
			'label'           => trans('admin.Tags'),
			'type'            => 'select2_tagging_from_array',
			'options'         => $tags,
			'allows_multiple' => true,
			'hint'            => t('tags_hint', [
				'limit' => (int)config('settings.listing_form.tags_limit', 15),
				'min'   => (int)config('settings.listing_form.tags_min_length', 2),
				'max'   => (int)config('settings.listing_form.tags_max_length', 30),
			]),
			'wrapper'         => [
				'class' => 'col-md-6',
			],
			'newline'         => true,
		]);
		$this->xPanel->addField([
			'name'    => 'email_verified_at',
			'label'   => trans('admin.Verified Email'),
			'type'    => 'checkbox_switch',
			'wrapper' => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'    => 'phone_verified_at',
			'label'   => trans('admin.Verified Phone'),
			'type'    => 'checkbox_switch',
			'wrapper' => [
				'class' => 'col-md-6',
			],
		]);
		if (config('settings.listing_form.listings_review_activation') == '1') {
			$this->xPanel->addField([
				'name'    => 'reviewed_at',
				'label'   => trans('admin.Reviewed'),
				'type'    => 'checkbox_switch',
				'wrapper' => [
					'class' => 'col-md-6',
				],
			]);
		}
		$this->xPanel->addField([
			'name'    => 'archived_at',
			'label'   => trans('admin.Archived'),
			'type'    => 'checkbox_switch',
			'wrapper' => [
				'class' => 'col-md-6',
			],
			'newline' => !empty($entity),
		]);
		
		if (!empty($entity)) {
			$this->xPanel->addField([
				'name'  => 'ip_separator',
				'type'  => 'custom_html',
				'value' => '<hr style="opacity: 0.15">',
			], 'update');
			
			$emptyIp = 'N/A';
			
			$label = '<span class="fw-bold">' . trans('admin.create_from_ip') . ':</span>';
			if (!empty($entity->create_from_ip)) {
				$ipUrl = config('larapen.core.ipLinkBase') . $entity->create_from_ip;
				$ipLink = '<a href="' . $ipUrl . '" target="_blank">' . $entity->create_from_ip . '</a>';
			} else {
				$ipLink = $emptyIp;
			}
			$this->xPanel->addField([
				'name'    => 'create_from_ip',
				'type'    => 'custom_html',
				'value'   => '<h5>' . $label . ' ' . $ipLink . '</h5>',
				'wrapper' => [
					'class' => 'col-md-6',
				],
			], 'update');
			
			$label = '<span class="fw-bold">' . trans('admin.latest_update_ip') . ':</span>';
			if (!empty($entity->latest_update_ip)) {
				$ipUrl = config('larapen.core.ipLinkBase') . $entity->latest_update_ip;
				$ipLink = '<a href="' . $ipUrl . '" target="_blank">' . $entity->latest_update_ip . '</a>';
			} else {
				$ipLink = $emptyIp;
			}
			$this->xPanel->addField([
				'name'    => 'latest_update_ip',
				'type'    => 'custom_html',
				'value'   => '<h5>' . $label . ' ' . $ipLink . '</h5>',
				'wrapper' => [
					'class' => 'col-md-6',
				],
				'newline' => true,
			], 'update');
			
			if (!empty($entity->email) || !empty($entity->phone)) {
				$this->xPanel->addField([
					'name'  => 'ban_separator',
					'type'  => 'custom_html',
					'value' => '<hr style="opacity: 0.15">',
				], 'update');
				
				$btnUrl = urlGen()->adminUrl('blacklists/add') . '?';
				$btnQs = !empty($entity->email) ? 'email=' . $entity->email : '';
				$btnQs = !empty($btnQs) ? $btnQs . '&' : $btnQs;
				$btnQs = !empty($entity->phone) ? $btnQs . 'phone=' . $entity->phone : $btnQs;
				$btnUrl = $btnUrl . $btnQs;
				
				$btnText = trans('admin.ban_the_user');
				$btnHint = $btnText;
				if (!empty($entity->email) && !empty($entity->phone)) {
					$btnHint = trans('admin.ban_the_user_email_and_phone', ['email' => $entity->email, 'phone' => $entity->phone]);
				} else {
					if (!empty($entity->email)) {
						$btnHint = trans('admin.ban_the_user_email', ['email' => $entity->email]);
					}
					if (!empty($entity->phone)) {
						$btnHint = trans('admin.ban_the_user_phone', ['phone' => $entity->phone]);
					}
				}
				$tooltip = ' data-bs-toggle="tooltip" title="' . $btnHint . '"';
				
				$btnLink = '<a href="' . $btnUrl . '" class="btn btn-danger confirm-simple-action"' . $tooltip . '>' . $btnText . '</a>';
				$this->xPanel->addField([
					'name'    => 'ban_button',
					'type'    => 'custom_html',
					'value'   => $btnLink,
					'wrapper' => [
						'style' => 'text-align:center;',
					],
				], 'update');
			}
		}
	}
	
	public function salaryType(): array
	{
		$entries = SalaryType::query()->get();
		
		return collect($entries)
			->mapWithKeys(fn ($item) => [$item['id'] => $item['name']])
			->toArray();
	}
	
	public function postType(): array
	{
		$entries = PostType::query()->get();
		
		return collect($entries)
			->mapWithKeys(fn ($item) => [$item['id'] => $item['name']])
			->toArray();
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
		$postId = request()->segment(3);
		if (!empty($postId) && is_numeric($postId)) {
			$post = Post::with('company')->find($postId);
			if (!empty($post->company)) {
				$company = $post->company;
			}
		}
		
		// create
		if (empty($company)) {
			$companyId = $request->input('company_id');
			if (!empty($companyId) && is_numeric($companyId)) {
				$company = Company::find($companyId);
			}
		}
		
		if (!empty($company)) {
			if ($request->filled('company_name')) {
				$company->name = $request->input('company_name');
			}
			
			if ($request->filled('company_description')) {
				$company->description = $request->input('company_description');
			}
			
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
				
				$company->logo_path = $logoPath;
			}
			
			if ($company->isDirty()) {
				$company->save();
			}
			
			// Generate the company's logo thumbnails
			GenerateLogoThumbnails::dispatchSync($company);
		}
		
		return $request;
	}
}
