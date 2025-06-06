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
	$threads = (array)data_get($apiResult, 'data');
	$totalThreads = (int)data_get($apiResult, 'meta.total', 0);
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
                    <div class="inner-box">
                        <h2 class="title-2">
                            <i class="fa-solid fa-envelope"></i> {{ t('inbox') }}
                        </h2>
                        
                        @if (session()->has('flash_notification'))
                            <div class="row">
                                <div class="col-12">
                                    @include('flash::message')
                                </div>
                            </div>
                        @endif
                        
                        <div id="successMsg" class="alert alert-success hide" role="alert"></div>
                        <div id="errorMsg" class="alert alert-danger hide" role="alert"></div>
                        
                        <div class="inbox-wrapper">
                            <div class="row">
                                <div class="col-md-3 col-lg-2">
                                    <div class="btn-group hidden-sm"></div>
                                </div>
                                
                                <div class="col-md-9 col-lg-10">
                                    
                                    <div class="btn-group mobile-only-inline">
                                        <a href="#" class="btn btn-primary text-uppercase">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    </div>
                                    <div class="btn-group hidden-sm">
                                        <button type="button" class="btn btn-default pb-0">
                                            <div class="form-check p-0 m-0">
                                                <input type="checkbox" id="form-check-all">
                                            </div>
                                        </button>
                                        
                                        <button type="button" class="btn btn-default dropdown-toggle" data-bs-toggle="dropdown">
                                            <span class="dropdown-menu-sort-selected">{{ t('action') }}</span>
                                        </button>
    
                                        {!! csrf_field() !!}
                                        <ul id="groupedAction" class="dropdown-menu dropdown-menu-sort" role="menu">
                                            <li class="dropdown-item">
                                                <a href="{{ url(urlGen()->getAccountBasePath() . '/messages/actions?type=markAsRead') }}">
                                                    {{  t('Mark as read') }}
                                                </a>
                                            </li>
                                            <li class="dropdown-item">
                                                <a href="{{ url(urlGen()->getAccountBasePath() . '/messages/actions?type=markAsUnread') }}">
                                                    {{ t('Mark as unread') }}
                                                </a>
                                            </li>
                                            <li class="dropdown-item">
                                                <a href="{{ url(urlGen()->getAccountBasePath() . '/messages/actions?type=markAsImportant') }}">
                                                    {{ t('Mark as important') }}
                                                </a>
                                            </li>
                                            <li class="dropdown-item">
                                                <a href="{{ url(urlGen()->getAccountBasePath() . '/messages/actions?type=markAsNotImportant') }}">
                                                    {{ t('Mark as not important') }}
                                                </a>
                                            </li>
                                            <li class="dropdown-item">
                                                <a href="{{ url(urlGen()->getAccountBasePath() . '/messages/delete') }}">
                                                    {{ t('Delete') }}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <button type="button" id="btnRefresh" class="btn btn-default hidden-sm" data-bs-toggle="tooltip" title="{{ t('refresh') }}">
                                        <span class="fa-solid fa-rotate"></span>
                                    </button>
                                    
                                    <div class="btn-group hidden-sm">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-bs-toggle="dropdown">
                                            {{ t('more') }}
                                        </button>
                                        <ul class="dropdown-menu" role="menu">
                                            <li class="dropdown-item">
                                                <a class="markAllAsRead">{{ t('Mark all as read') }}</a>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <div class="message-tool-bar-right float-end" id="linksThreads">
                                        
                                        @include('front.account.messenger.threads.links')
                                        
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="border-0 bg-secondary">
                            
                            <div class="row">
                                @include('front.account.messenger.partials.sidebar')
                                
                                <div class="col-md-9 col-lg-10">
                                    <div class="message-list">
                                        <div id="listThreads">
                                            
                                            @include('front.account.messenger.threads.threads')
                                            
                                        </div>
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
    <style>
        {{-- Center image related to the parent element --}}
        .loading-img {
            position: absolute;
            width: 32px;
            height: 32px;
            left: 50%;
            top: 50%;
            margin-left: -16px;
            margin-right: -16px;
            z-index: 100000;
        }
    </style>
@endsection

@section('after_scripts')
	<script>
        var loadingImage = '{{ url('images/spinners/fading-line.gif') }}';
        var loadingErrorMessage = '{{ t('Threads could not be loaded') }}';
        var actionText = '{{ t('action') }}';
        var actionErrorMessage = '{{ t('This action could not be done') }}';
        var title = {
            'seen': '{{ t('Mark as read') }}',
            'notSeen': '{{ t('Mark as unread') }}',
            'important': '{{ t('Mark as important') }}',
            'notImportant': '{{ t('Mark as not important') }}',
        };
	</script>
    <script src="{{ url('assets/js/app/messenger.js') }}" type="text/javascript"></script>
@endsection
