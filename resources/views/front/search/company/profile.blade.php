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
	$company ??= [];
@endphp

@section('search')
	@parent
	@include('front.search.inc.form')
	@include('front.search.inc.breadcrumbs')
	@include('front.layouts.inc.advertising.top')
@endsection

@section('content')
	@include('front.common.spacer')
	<div class="main-container">
		<div class="container">
			
			<div class="section-content">
				<div class="inner-box">
					<div class="row">
						@php
							$colDetails = 'col-12';
							$colContact = null;
							if (
								(!empty(data_get($company, 'address')))
								|| (!empty(data_get($company, 'phone')))
								|| (!empty(data_get($company, 'mobile')))
								|| (!empty(data_get($company, 'fax')))
							) {
								$colDetails = 'col-lg-8 col-md-6 col-sm-12';
								$colContact = 'col-lg-4 col-md-6 col-sm-12';
							}
						@endphp
						<div class="{{ $colDetails }}">
							<div class="seller-info seller-profile">
								<div class="seller-profile-img">
									<a><img src="{{ data_get($company, 'logo_url.medium') }}" class="img-fluid img-thumbnail" alt="img"></a>
								</div>
								<h3 class="no-margin no-padding link-color uppercase">
									@if (auth()->check())
										@if (auth()->user()->id == data_get($company, 'user_id'))
											<a href="{{ url(urlGen()->getAccountBasePath() . '/companies/' . data_get($company, 'id') . '/edit') }}" class="btn btn-default">
												<i class="fa-regular fa-pen-to-square"></i> {{ t('Edit') }}
											</a>
										@endif
									@endif
									{{ data_get($company, 'name') }}
								</h3>
								
								<div class="text-muted">
									{!! data_get($company, 'description') !!}
								</div>
								
								<div class="seller-social-list">
									<ul class="share-this-post">
										@if (!empty(data_get($company, 'linkedin')))
											<li><a href="{{ data_get($company, 'linkedin') }}" target="_blank"><i class="fa-brands fa-linkedin"></i></a></li>
										@endif
										@if (!empty(data_get($company, 'facebook')))
											<li><a class="facebook" href="{{ data_get($company, 'facebook') }}" target="_blank"><i class="fa-brands fa-square-facebook"></i></a></li>
										@endif
										@if (!empty(data_get($company, 'twitter')))
											<li><a href="{{ data_get($company, 'twitter') }}" target="_blank"><i class="fa-brands fa-square-x-twitter"></i></a></li>
										@endif
										@if (!empty(data_get($company, 'pinterest')))
											<li><a class="pinterest" href="{{ data_get($company, 'pinterest') }}" target="_blank"><i class="fa-brands fa-square-pinterest"></i></a></li>
										@endif
									</ul>
								</div>
							</div>
						</div>
						
						@if (!empty($colContact))
							<div class="{{ $colContact }}">
								<div class="seller-contact-info mt5">
									<h3 class="no-margin"> {{ t('Contact Information') }} </h3>
									<dl class="dl-horizontal">
										@if (!empty(data_get($company, 'address')))
											<dt>{{ t('Address') }}:</dt>
											<dd class="contact-sensitive">{!! data_get($company, 'address') !!}</dd>
										@endif
										
										@if (!empty(data_get($company, 'phone')))
											<dt>{{ t('phone') }}:</dt>
											<dd class="contact-sensitive">{{ data_get($company, 'phone') }}</dd>
										@endif
										
										@if (!empty(data_get($company, 'mobile')))
											<dt>{{ t('Mobile Phone') }}:</dt>
											<dd class="contact-sensitive">{{ data_get($company, 'mobile') }}</dd>
										@endif
										
										@if (!empty(data_get($company, 'fax')))
											<dt>{{ t('Fax') }}:</dt>
											<dd class="contact-sensitive">{{ data_get($company, 'fax') }}</dd>
										@endif
										
										@if (!empty(data_get($company, 'website')))
											<dt>{{ t('Website') }}:</dt>
											<dd class="contact-sensitive">
												<a href="{!! data_get($company, 'website') !!}" target="_blank">
													{!! data_get($company, 'website') !!}
												</a>
											</dd>
										@endif
									</dl>
								</div>
							</div>
						@endif
					</div>
				</div>
				
				<div class="section-block mt-3">
					<div class="category-list">
						<div class="tab-box clearfix">
							
							{{-- Nav tabs --}}
							<div class="col-lg-12 box-title no-border">
								<div class="inner">
									<h2 class="mx-3">
										<small>{{ data_get($count, '0') }} {{ t('Jobs Found') }}</small>
									</h2>
								</div>
							</div>
							
							{{-- Mobile Filter bar --}}
							<div class="mobile-filter-bar col-lg-12"></div>
							<div class="menu-overly-mask"></div>
							
							{{-- Tab Filter --}}
							<div class="tab-filter hide-xs"></div>
						
						</div>
						
						<div class="listing-filter hidden-xs">
							<div class="float-start col-sm-10 col-12">
								<div class="breadcrumb-list text-center-xs">
									{!! (isset($htmlTitle)) ? $htmlTitle : '' !!}
								</div>
							</div>
							<div class="float-end col-sm-2 col-12 text-end text-center-xs listing-view-action">
								@if (!empty(request()->all()))
									<a class="clear-all-button text-muted" href="{!! urlGen()->searchWithoutQuery() !!}">{{ t('Clear all') }}</a>
								@endif
							</div>
							<div style="clear:both;"></div>
						</div>
						<!--/.listing-filter-->
						
						<div class="posts-wrapper jobs-list">
							@include('front.search.inc.posts.template.list')
						</div>
						<!--/.posts-wrapper-->
						
						<div class="tab-box save-search-bar text-center">
							@if (request()->filled('q') && request()->query('q') != '' && $count->get('all') > 0)
								<a id="saveSearch"
								   data-name="{!! request()->fullUrlWithoutQuery(['_token', 'location']) !!}"
								   data-count="{{ data_get($count, '0') }}"
								>
									<i class="icon-star-empty"></i> {{ t('Save Search') }}
								</a>
							@else
								<a href="#"> &nbsp; </a>
							@endif
						</div>
					</div>
		
					<div class="pagination-bar text-center">
						@include('vendor.pagination.api.bootstrap-4')
					</div>
				</div>
				
				<div style="clear:both;"></div>
				
				{{-- Advertising --}}
				@include('front.layouts.inc.advertising.bottom')
			</div>
		
		</div>
	</div>
@endsection
