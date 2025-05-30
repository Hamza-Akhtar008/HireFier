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
	$resumes = (array)data_get($apiResult, 'data');
	$totalResumes = (int)data_get($apiResult, 'meta.total', 0);
@endphp
@section('content')
	@include('front.common.spacer')
	<div class="main-container">
		<div class="container">
			<div class="row">
				
				@if (session()->has('flash_notification'))
					<div class="col-12">
						<div class="row">
							<div class="col-12">
								@include('flash::message')
							</div>
						</div>
					</div>
				@endif
				
				<div class="col-md-3 page-sidebar">
					@include('front.account.partials.sidebar')
				</div>
				
				<div class="col-md-9 page-content">
					<div class="inner-box">
						<h2 class="title-2"><i class="fa-regular fa-building"></i> {{ t('my_resumes') }} </h2>
						<div class="mb30">
							<a href="{{ url(urlGen()->getAccountBasePath() . '/resumes/create') }}" class="btn btn-default">
								<i class="fa-solid fa-plus"></i> {{ t('Add a new resume') }}
							</a>
						</div>
						<br>
						
						<div class="table-responsive">
							<form name="listForm" method="POST" action="{{ url(urlGen()->getAccountBasePath() . '/resumes/delete') }}">
								{!! csrf_field() !!}
								<div class="table-action">
									<div class="btn-group hidden-sm" role="group">
										<button type="button" class="btn btn-sm btn-default pb-0">
											<input type="checkbox" id="checkAll" class="from-check-all">
										</button>
										<button type="button" class="btn btn-sm btn-default from-check-all">
											{{ t('Select') }}: {{ t('All') }}
										</button>
									</div>
									
									<button type="submit" class="btn btn-sm btn-default confirm-simple-action">
										<i class="fa-regular fa-trash-can"></i> {{ t('Delete') }}
									</button>
									
									<div class="table-search float-end col-sm-7">
										<div class="row">
											<label class="col-sm-5 form-label text-end">{{ t('search') }} <br>
												<a title="clear filter" class="clear-filter" href="#clear">[{{ t('clear') }}]</a>
											</label>
											<div class="col-sm-7 searchpan px-3">
												<input type="text" class="form-control" id="filter">
											</div>
										</div>
									</div>
								</div>
								
								<table id="addManageTable"
								       class="table table-striped table-bordered add-manage-table table demo"
									   data-filter="#filter"
									   data-filter-text-only="true"
								>
									<thead>
									<tr>
										<th data-type="numeric" data-sort-initial="true"></th>
										<th> {{ t('File') }}</th>
										<th data-sort-ignore="true"> {{ t('Name') }} </th>
										<th> {{ t('Option') }}</th>
									</tr>
									</thead>
									<tbody>
									@if (!empty($resumes) && $totalResumes > 0)
										@foreach($resumes as $key => $resume)
											<tr>
												<td style="width:2%" class="add-img-selector">
													<div class="checkbox">
														<label><input type="checkbox" name="entries[]" value="{{ data_get($resume, 'id') }}"></label>
													</div>
												</td>
												<td style="width:14%" class="add-img-td">
													<a class="btn btn-default" href="{{ privateFileUrl(data_get($resume, 'file_path')) }}" target="_blank">
														<i class="fa-solid fa-paperclip"></i> {{ t('Download') }}
													</a>
												</td>
												<td style="width:58%" class="items-details-td">
													<div>
														<p>
															{{ str(data_get($resume, 'name'))->limit(40) }}
														</p>
													</div>
												</td>
												<td style="width:10%" class="action-td">
													<div>
														@if (data_get($resume, 'user_id') == $authUser->id)
															<p>
																<a class="btn btn-primary btn-sm"
																   href="{{ url(urlGen()->getAccountBasePath() . '/resumes/' . data_get($resume, 'id') . '/edit') }}"
																>
																	<i class="fa-regular fa-pen-to-square"></i> {{ t('Edit') }}
																</a>
															</p>
															<p>
																<a class="btn btn-danger btn-sm confirm-simple-action"
																   href="{{ url(urlGen()->getAccountBasePath() . '/resumes/'.data_get($resume, 'id').'/delete') }}"
																>
																	<i class="fa-regular fa-trash-can"></i> {{ t('Delete') }}
																</a>
															</p>
														@endif
													</div>
												</td>
											</tr>
										@endforeach
									@endif
									</tbody>
								</table>
							</form>
						</div>
						
						<div class="pagination-bar text-center">
							@include('vendor.pagination.api.bootstrap-4')
						</div>
					
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('after_styles')
	<style>
		.action-td p {
			margin-bottom: 5px;
		}
	</style>
@endsection

@section('after_scripts')
	<script src="{{ url('assets/js/footable.js?v=2-0-1') }}" type="text/javascript"></script>
	<script src="{{ url('assets/js/footable.filter.js?v=2-0-1') }}" type="text/javascript"></script>
	<script type="text/javascript">
		onDocumentReady((event) => {
			$('#addManageTable').footable().bind('footable_filtering', function (e) {
				var selected = $('.filter-status').find(':selected').text();
				if (selected && selected.length > 0) {
					e.filter += (e.filter && e.filter.length > 0) ? ' ' + selected : selected;
					e.clear = !e.filter;
				}
			});
			
			/* Clear Filter OnClick */
			const clearFilterEl = document.querySelector(".clear-filter");
			clearFilterEl.addEventListener("click", (event) => {
				event.preventDefault();
				
				const filterStatusEl = document.querySelector(".filter-status");
				if (filterStatusEl) {
					filterStatusEl.value = '';
				}
				
				$('table.demo').trigger('footable_clear_filter');
			});
			
			/* Check All OnClick */
			const checkAllEl = document.querySelector(".from-check-all");
			if (checkAllEl) {
				checkAllEl.addEventListener("click", (event) => checkAll(event.target));
			}
		});
	</script>
	{{-- include custom script for ads table [select all checkbox] --}}
	<script>
		function checkAll(checkAllEl) {
			if (checkAllEl.type !== "checkbox") {
				checkAllEl = document.getElementById("checkAll");
				checkAllEl.checked = !checkAllEl.checked;
			}
			
			const checkboxInputs = document.getElementsByTagName("input");
			if (checkboxInputs) {
				for (let i = 0; i < checkboxInputs.length; i++) {
					if (checkboxInputs[i].type === "checkbox") {
						checkboxInputs[i].checked = checkAllEl.checked;
					}
				}
			}
		}
	</script>
@endsection
