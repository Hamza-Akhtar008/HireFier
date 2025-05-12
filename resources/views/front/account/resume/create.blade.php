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
	$fiTheme = config('larapen.core.fileinput.theme', 'bs5');
@endphp

@section('content')
	@include('front.common.spacer')
	<div class="main-container">
		<div class="container">
			<div class="row">
				<div class="col-md-3 page-sidebar">
					@include('front.account.partials.sidebar')
				</div>
				
				<div class="col-md-9 page-content">
					
					@include('flash::message')
					
					@if (isset($errors) && $errors->any())
						<div class="alert alert-danger">
							<h5><strong>{{ t('validation_errors_title') }}</strong></h5>
							<ul class="list list-check">
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif
					
					<div class="inner-box">
						<h2 class="title-2"><i class="fa-solid fa-paperclip"></i> {{ t('Add a new resume') }} </h2>
						
						<div class="mb30" style="float: right; padding-right: 5px;">
							<a href="{{ url(urlGen()->getAccountBasePath() . '/resumes') }}">{{ t('my_resumes') }}</a>
						</div>
						<div style="clear: both;"></div>
						
						<div class="panel-group" id="accordion">
							
							{{-- RESUME --}}
							<div class="card card-default">
								<div class="card-header">
									<h4 class="card-title">
										<a href="#resumePanel" data-bs-toggle="collapse" data-parent="#accordion"> {{ t('Resume') }} </a>
									</h4>
								</div>
								<div class="panel-collapse collapse show" id="resumePanel">
									<div class="card-body">
										<form name="resume"
										      class="form-horizontal"
										      role="form"
										      method="POST"
										      action="{{ url(urlGen()->getAccountBasePath() . '/resumes') }}"
										      enctype="multipart/form-data"
										>
											{!! csrf_field() !!}
											<input name="panel" type="hidden" value="resumePanel">
											
											@include('front.account.resume._form')
											
											<div class="row mb-3">
												<div class="offset-md-3 col-md-9"></div>
											</div>
											
											{{-- Button --}}
											<div class="row mb-3">
												<div class="offset-md-3 col-md-9">
													<button type="submit" class="btn btn-primary">{{ t('submit') }}</button>
												</div>
											</div>
										</form>
									</div>
								</div>
							</div>
						
						</div>
					
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('after_styles')
	<link href="{{ url('assets/plugins/bootstrap-fileinput/css/fileinput.min.css') }}" rel="stylesheet">
	@if (config('lang.direction') == 'rtl')
		<link href="{{ url('assets/plugins/bootstrap-fileinput/css/fileinput-rtl.min.css') }}" rel="stylesheet">
	@endif
	@if (str_starts_with($fiTheme, 'explorer'))
		<link href="{{ url('assets/plugins/bootstrap-fileinput/themes/' . $fiTheme . '/theme.min.css') }}" rel="stylesheet">
	@endif
	<style>
		.krajee-default.file-preview-frame:hover:not(.file-preview-error) {
			box-shadow: 0 0 5px 0 #666666;
		}
		.file-loading:before {
			content: " {{ t('loading_wd') }}";
		}
	</style>
@endsection

@section('after_scripts')
	<script src="{{ url('assets/plugins/bootstrap-fileinput/js/plugins/sortable.min.js') }}" type="text/javascript"></script>
	<script src="{{ url('assets/plugins/bootstrap-fileinput/js/fileinput.min.js') }}" type="text/javascript"></script>
	<script src="{{ url('assets/plugins/bootstrap-fileinput/themes/' . $fiTheme . '/theme.js') }}" type="text/javascript"></script>
	<script src="{{ url('common/js/fileinput/locales/' . config('app.locale') . '.js') }}" type="text/javascript"></script>
@endsection
