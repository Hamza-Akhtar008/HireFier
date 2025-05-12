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
	$companies = (array)data_get($apiResult, 'data');
	$totalCompanies = (int)data_get($apiResult, 'meta.total', 0);
@endphp

@section('search')
	@parent
	@include('front.search.company.inc.search')
@endsection

@section('content')
	@include('front.common.spacer')
	<div class="main-container">
		<div class="container">
			
			<div class="col-lg-12 content-box">
				<div class="row row-featured row-featured-category row-featured-company">
					<div class="col-lg-12 box-title no-border">
						<div class="inner">
							<h2>
								<span class="title-3">{{ t('companies_list') }}</span>
								<a class="sell-your-item" href="{{ urlGen()->searchWithoutQuery() }}">
									{{ t('Browse Jobs') }}
									<i class="fa-solid fa-list"></i>
								</a>
							</h2>
						</div>
					</div>
					
					@if (!empty($companies) && $totalCompanies > 0)
						@foreach($companies as $key => $iCompany)
							<div class="col-lg-2 col-md-3 col-sm-3 col-4 f-category">
								<a href="{{ urlGen()->company(data_get($iCompany, 'id')) }}">
									<img alt="{{ data_get($iCompany, 'name') }}" class="img-fluid" src="{{ data_get($iCompany, 'logo_url.medium') }}">
									<h6> {{ t('Jobs at') }}
										<span class="company-name">{{ data_get($iCompany, 'name') }}</span>
										<span class="jobs-count text-muted">({{ data_get($iCompany, 'posts_count') ?? 0 }})</span>
									</h6>
								</a>
							</div>
						@endforeach
					@else
						<div class="col-lg-12 col-md-12 col-sm-12 col-12 f-category" style="width: 100%;">
							{{ $apiMessage ?? t('no_result_refine_your_search') }}
						</div>
					@endif
			
				</div>
			</div>
			
			<div style="clear: both"></div>
			
			<div class="pagination-bar text-center">
				@include('vendor.pagination.api.bootstrap-4')
			</div>
			
		</div>
	</div>
@endsection
