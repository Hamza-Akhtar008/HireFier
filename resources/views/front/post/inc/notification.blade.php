@if (isset($errors) && $errors->any())
    <div class="col-12">
        <div class="alert alert-danger">
            <h5><strong>{{ t('validation_errors_title') }}</strong></h5>
            <ul class="list list-check">
                @foreach ($errors->all() as $error)
                    <li>{!! $error !!}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@php
    $withMessage = !session()->has('flash_notification');
	$resendVerificationLink = getResendVerificationLink(withMessage: $withMessage);
@endphp

@if (session()->has('flash_notification'))
    <div class="col-12">
        <div class="row">
            <div class="col-12">
                @include('flash::message')
            </div>
        </div>
    </div>
@endif

@if (!empty($resendVerificationLink))
    <div class="col-12">
        <div class="alert alert-info text-center">
            {!! $resendVerificationLink !!}
        </div>
    </div>
@endif
