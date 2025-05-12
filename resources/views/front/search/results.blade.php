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

@php
	
	$apiResult ??= [];
	$apiExtra ??= [];
	$count = (array)data_get($apiExtra, 'count');
	$posts = (array)data_get($apiResult, 'data');
	$totalPosts = (int)data_get($apiResult, 'meta.total', 0);
	$tags = (array)data_get($apiExtra, 'tags');
	
	$postTypes ??= [];
	$orderByOptions ??= [];
	$displayModes ??= [];
	
	$authUser = auth()->check() ? auth()->user() : null;
	$authUserTypeId = (!empty($authUser) && !empty($authUser->user_type_id)) ? $authUser->user_type_id : 0;
	$isJobSeekerUser = ($authUserTypeId == 2);
@endphp

@section('search')
	@parent
	@include('front.search.inc.form')
@endsection

@section('content')
	<div class="main-container">
		
		@if (session()->has('flash_notification'))
			@include('front.common.spacer')
			@php
				$paddingTopExists = true;
			@endphp
			<div class="container">
				<div class="row">
					<div class="col-12">
						@include('flash::message')
					</div>
				</div>
			</div>
		@endif
		
		@include('front.search.inc.breadcrumbs')
		
		@if (config('settings.listings_list.show_cats_in_top'))
			@if (isset($cats) && !empty($cats))
				<div class="container mb-2 hide-xs">
					<div class="row p-0 m-0">
						<div class="col-12 p-0 m-0 border border-bottom-0 bg-light"></div>
					</div>
				</div>
			@endif
			@include('front.search.inc.categories')
		@endif
		
		@if (isset($topAdvertising) && !empty($topAdvertising))
			@include('front.layouts.inc.advertising.top', ['paddingTopExists' => true])
			@php
				$paddingTopExists = false;
			@endphp
		@else
			@php
				if (isset($paddingTopExists) && $paddingTopExists) {
					$paddingTopExists = false;
				}
			@endphp
		@endif
		
		
		<div class="container">
			
			@if (session()->has('flash_notification'))
				<div class="row">
					<div class="col-12">
						@include('flash::message')
					</div>
				</div>
			@endif
			
			<div class="row">
				
				{{-- Sidebar --}}
				@include('front.search.inc.sidebar')
				
				{{-- Content --}}
				<div class="col-md-9 page-content col-thin-left mb-4">
					<div class="category-list">
						<div class="tab-box">

							{{-- Nav tabs --}}
							<div class="col-xl-12 box-title no-border">
								<div class="inner">
									<h2 class="px-2">
										<small>{{ data_get($count, '0') }} {{ t('Jobs Found') }}</small>
									</h2>
								</div>
							</div>

							{{-- Mobile Filter bar --}}
							<div class="col-xl-12 mobile-filter-bar">
								<ul class="list-unstyled list-inline no-margin no-padding">
									<li class="filter-toggle">
										<a class="">
											<i class="fa-solid fa-list"></i> {{ t('Filters') }}
										</a>
									</li>
									<li>
										{{-- OrderBy Mobile --}}
										<div class="dropdown">
											<a data-bs-toggle="dropdown" class="dropdown-toggle">{{ t('Sort by') }}</a>
											<ul class="dropdown-menu">
												@if (!empty($orderByOptions))
													@foreach($orderByOptions as $option)
														@if (data_get($option, 'condition'))
															@php
																$optionUrl = request()->fullUrlWithQuery((array)data_get($option, 'query'));
															@endphp
															<li>
																<a href="{!! $optionUrl !!}" rel="nofollow">
																	{{ data_get($option, 'label') }}
																</a>
															</li>
														@endif
													@endforeach
												@endif
											</ul>
										</div>
									</li>
								</ul>
							</div>
							<div class="menu-overly-mask"></div>
							{{-- Mobile Filter bar End--}}
							
							
							<div class="tab-filter pb-2">
								{{-- OrderBy Desktop --}}
								<select id="orderBy" class="niceselecter select-sort-by small" data-style="btn-select" data-width="auto">
									@if (!empty($orderByOptions))
										@foreach($orderByOptions as $option)
											@if (data_get($option, 'condition'))
												@php
													$optionUrl = request()->fullUrlWithQuery((array)data_get($option, 'query'));
												@endphp
												<option @selected(data_get($option, 'isSelected')) value="{!! $optionUrl !!}">
													{{ data_get($option, 'label') }}
												</option>
											@endif
										@endforeach
									@endif
								</select>
							</div>

						</div>

						<div class="listing-filter hidden-xs">
							<div class="float-start col-md-9 col-sm-8 col-12">
								<h1 class="h6 pb-0 breadcrumb-list text-center-xs">
									{!! (isset($htmlTitle)) ? $htmlTitle : '' !!}
								</h1>
							</div>
							<div class="float-end col-md-3 col-sm-4 col-12 text-end text-center-xs listing-view-action">
								@if (!empty(request()->all()))
									<a class="clear-all-button text-muted" href="{!! urlGen()->searchWithoutQuery() !!}">
										{{ t('Clear all') }}
									</a>
								@endif
							</div>
							<div style="clear:both;"></div>
						</div>

						<div class="posts-wrapper jobs-list">
							@include('front.search.inc.posts.template.list')
						</div>
						
						@php
							$keyword = request()->query('q');
							$searchCanBeSaved = (!empty($keyword) && data_get($count, '0') > 0 && $isJobSeekerUser);
						@endphp
						@if ($searchCanBeSaved)
							<div class="tab-box save-search-bar text-center">
								<a id="saveSearch"
								   data-search-url="{!! request()->fullUrlWithoutQuery(['_token', 'location']) !!}"
								   data-results-count="{{ data_get($count, '0') }}"
								>
									<i class="fa-regular fa-bell"></i> {{ t('Save Search') }}
								</a>
							</div>
						@endif
					</div>
		
					<nav class="mt-3 mb-0 pagination-sm" aria-label="">
						@include('vendor.pagination.api.bootstrap-4')
					</nav>
					
				</div>
			</div>
		</div>
		
		{{-- Advertising --}}
		@include('front.layouts.inc.advertising.bottom')
		
		{{-- Promo Post Button --}}
		@if (!auth()->check())
			<div class="container mb-3">
				<div class="card border-light text-dark bg-light mb-3">
					<div class="card-body text-center">
						<h2>{{ t('Looking for a job') }}</h2>
						<h5>{{ t('Upload your Resume and easily apply to jobs from any device') }}</h5>
						@php
							$candidateRegistrationUrl = urlQuery(urlGen()->signUp())
								->setParameters(['type' => 2])
								->toString();
						@endphp
						<a href="{{ $candidateRegistrationUrl }}" class="btn btn-border btn-border btn-listing">
							<i class="fa-solid fa-paperclip"></i> {{ t('Add Your Resume') }}
						</a>
					</div>
				</div>
			</div>
		@endif
		
		{{-- Category Description --}}
		@if (isset($cat) && !empty(data_get($cat, 'description')))
			@if (!(bool)data_get($cat, 'hide_description'))
				<div class="container mb-3">
					<div class="card border-light text-dark bg-light mb-3">
						<div class="card-body">
							{!! data_get($cat, 'description') !!}
						</div>
					</div>
				</div>
			@endif
		@endif
		
		{{-- Show Posts Tags --}}
		@if (config('settings.listings_list.show_listings_tags'))
			@if (!empty($tags))
				<div class="container">
					<div class="card mb-3">
						<div class="card-body">
							<h2 class="card-title"><i class="fa-solid fa-tags"></i> {{ t('Tags') }}:</h2>
							@foreach($tags as $iTag)
								<span class="d-inline-block border border-inverse bg-light rounded-1 py-1 px-2 my-1 me-1">
									<a href="{{ urlGen()->tag($iTag) }}">
										{{ $iTag }}
									</a>
								</span>
							@endforeach
						</div>
					</div>
				</div>
			@endif
		@endif
	</div>
@endsection

@section('modal_location')
	@parent
	@include('front.layouts.inc.modal.location')
@endsection

@section('after_scripts')
	<script>
		onDocumentReady((event) => {
			const postTypeEls = document.querySelectorAll('#postType a');
			if (postTypeEls.length > 0) {
				postTypeEls.forEach((element) => {
					element.addEventListener('click', (event) => {
						event.preventDefault();
						
						const goToUrl = event.target.getAttribute('href');
						if (goToUrl) {
							redirect(goToUrl);
						}
					});
				});
			}
			
			{{-- orderBy: HTML Select --}}
			const orderByEl = document.getElementById('orderBy');
			if (orderByEl) {
				orderByEl.addEventListener('change', (event) => {
					event.preventDefault();
					
					const goToUrl = event.target.value;
					if (goToUrl) {
						redirect(goToUrl);
					}
				});
			}
			
			{{-- orderBy: jQuery Nice Select --}}
			onDomElementsAdded('.select-sort-by li.option', (elements) => {
				if (elements.length <= 0) {
					return false;
				}
				
				elements.forEach((element) => {
					element.addEventListener('click', (event) => {
						event.preventDefault();
						
						const goToUrl = event.target.dataset.value;
						if (goToUrl) {
							redirect(goToUrl);
						}
					});
				});
			});
		});
	</script>
@endsection
