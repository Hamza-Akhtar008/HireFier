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

namespace App\Models\Traits;

trait SettingTrait
{
	// ===| ADMIN PANEL METHODS |===
	
	public function getNameHtml(): string
	{
		$currentUrl = preg_replace('#/(search)$#', '', url()->current());
		$url = $currentUrl . '/' . $this->getKey() . '/edit';
		
		return '<a href="' . $url . '">' . $this->name . '</a>';
	}
	
	public function configureButton($xPanel = false): string
	{
		$url = urlGen()->adminUrl('settings/' . $this->id . '/edit');
		
		$msg = trans('admin.configure_entity', ['entity' => $this->name]);
		$tooltip = ' data-bs-toggle="tooltip" title="' . $msg . '"';
		
		$out = '<a class="btn btn-xs btn-primary" href="' . $url . '"' . $tooltip . '>';
		$out .= '<i class="fa-solid fa-gear"></i> ';
		$out .= mb_ucfirst(trans('admin.Configure'));
		$out .= '</a>';
		
		return $out;
	}
	
	// ===| OTHER METHODS |===
	
	public static function optionsThatNeedToBeHidden(): array
	{
		return [
			'purchase_code',
			'email',
			'phone_number',
			'smtp_username',
			'smtp_password',
			'mailgun_secret',
			'mailgun_username',
			'mailgun_password',
			'postmark_token',
			'postmark_username',
			'postmark_password',
			'ses_key',
			'ses_secret',
			'ses_username',
			'ses_password',
			'mandrill_secret',
			'mandrill_username',
			'mandrill_password',
			'sparkpost_secret',
			'sparkpost_username',
			'sparkpost_password',
			'sendmail_username',
			'sendmail_password',
			'mailersend_api_key',
			'vonage_key',
			'vonage_secret',
			'vonage_application_id',
			'vonage_from',
			'twilio_username',
			'twilio_password',
			'twilio_account_sid',
			'twilio_auth_token',
			'twilio_from',
			'twilio_alpha_sender',
			'twilio_sms_service_sid',
			'twilio_debug_to',
			'ipinfo_token',
			'dbip_api_key',
			'ipbase_api_key',
			'ip2location_api_key',
			'ipgeolocation_api_key',
			'iplocation_api_key',
			'ipstack_api_key', // old
			'ipstack_access_key',
			'maxmind_api_account_id',
			'maxmind_api_license_key',
			'maxmind_database_license_key',
			'recaptcha_v2_site_key',
			'recaptcha_v2_secret_key',
			'recaptcha_v3_site_key',
			'recaptcha_v3_secret_key',
			'recaptcha_site_key',
			'recaptcha_secret_key',
			'stripe_secret',
			'paypal_username',
			'paypal_password',
			'paypal_signature',
			'facebook_client_id',
			'facebook_client_secret',
			'linkedin_client_id',
			'linkedin_client_secret',
			'twitter_client_id',
			'twitter_client_secret',
			'google_client_id',
			'google_client_secret',
			'google_maps_key',
			'facebook_app_id',
		];
	}
}
