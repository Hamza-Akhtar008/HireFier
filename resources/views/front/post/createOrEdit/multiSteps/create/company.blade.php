{{--
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
--}}
@extends('front.layouts.master')

@section('wizard')
	@include('front.post.createOrEdit.multiSteps.inc.wizard')
@endsection

@php
	$companyInput ??= [];
	$selectedCompany ??= [];
	$companies ??= [];
	
	// Get steps URLs & labels
	$previousStepUrl ??= null;
	$previousStepLabel ??= null;
	$formActionUrl ??= request()->fullUrl();
	$nextStepUrl ??= '/';
	$nextStepLabel ??= t('submit');
@endphp

@section('content')
	@include('front.common.spacer')
	<div class="main-container">
		<div class="container">
			<div class="row">
				
				@include('front.post.inc.notification')
				
				<div class="col-md-9 page-content">
					<div class="inner-box category-content" style="overflow: visible;">
						<h2 class="title-2">
							<strong><i class="fa-regular fa-pen-to-square"></i> {{ t('company_information') }}</strong>
						</h2>
						
						<div class="row">
							<div class="col-12">
								
								<form class="form-horizontal"
								      id="payableForm"
								      method="POST"
								      action="{{ $formActionUrl }}"
								      enctype="multipart/form-data"
								>
									{!! csrf_field() !!}
									@honeypot
									<fieldset>
										
										{{-- company_id --}}
										@php
											$companyIdError = (isset($errors) && $errors->has('company_id')) ? ' is-invalid' : '';
											$selectedCompanyId = data_get($selectedCompany, 'id', 0);
											$selectedCompanyId = data_get($companyInput, 'company_id', $selectedCompanyId);
											$selectedCompanyId = data_get($companyInput, 'company.id', $selectedCompanyId);
											$selectedCompanyId = old('company_id', $selectedCompanyId);
										@endphp
										<div class="row mb-3 required">
											<label class="col-md-3 col-form-label{{ $companyIdError }}">
												{{ t('Select a Company') }} <sup>*</sup>
											</label>
											<div class="col-md-8">
												<select id="companyId" name="company_id" class="form-control selecter{{ $companyIdError }}">
													<option value="0" data-logo="" @selected(empty(old('company_id')))>
														[+] {{ t('New Company') }}
													</option>
													@if (!empty($companies))
														@foreach ($companies as $item)
															<option value="{{ data_get($item, 'id') }}"
															        data-logo="{{ data_get($item, 'logo_url.small') }}"
																	@selected($selectedCompanyId == data_get($item, 'id'))
															>
																{{ data_get($item, 'name') }}
															</option>
														@endforeach
													@endif
												</select>
											</div>
										</div>
										
										{{-- logo --}}
										<div id="logoField" class="row mb-3">
											<label class="col-md-3 col-form-label">&nbsp;</label>
											<div class="col-md-8">
												<div class="mb10">
													<div id="logoFieldValue"></div>
												</div>
												<small id="" class="form-text text-muted">
													<a id="companyFormLink"
													   href="{{ url(urlGen()->getAccountBasePath() . '/companies/0/edit') }}"
													   class="btn btn-default"
													>
														<i class="fa-regular fa-pen-to-square"></i> {{ t('Edit the Company') }}
													</a>
												</small>
											</div>
										</div>
										
										@include('front.account.company._form', ['originForm' => 'post'])
										
										@include('front.layouts.inc.tools.captcha', [
											'colLeft'  => 'col-md-3',
											'colRight' => 'col-md-8'
										])
										
										{{-- Button  --}}
										<div class="row mb-3">
											<div class="col-md-12 text-center">
												<button id="nextStepBtn" class="btn btn-primary btn-lg">
													{{ $nextStepLabel }}
												</button>
											</div>
										</div>
									
									</fieldset>
								</form>
							
							
							</div>
						</div>
					</div>
				</div>
				
				<div class="col-md-3 reg-sidebar">
					@include('front.post.createOrEdit.inc.right-sidebar')
				</div>
			
			</div>
		</div>
	</div>
@endsection

@section('after_styles')
@endsection

@section('after_scripts')
@endsection

@include('front.post.createOrEdit.inc.company-form-assets')
