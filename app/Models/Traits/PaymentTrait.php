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

use App\Models\Payment;

trait PaymentTrait
{
	// ===| ADMIN PANEL METHODS |===
	
	public function getPayableTitleHtml(): string
	{
		$out = '';
		
		$blankImageUrl = url('images/blank.gif');
		$blankImage = '<img src="' . $blankImageUrl . '" style="width: 16px; height: 16px;" alt="">';
		
		if (empty($this->payable_id) || empty($this->payable_type)) {
			return $blankImage;
		}
		
		$isPromoting = (str_ends_with($this->payable_type, 'Post'));
		$isSubscripting = (str_ends_with($this->payable_type, 'User'));
		
		if (!$isPromoting && !$isSubscripting) {
			return $blankImage;
		}
		
		$payable = $this->payable ?? null;
		$country = $payable->country ?? null;
		$countryCode = $country->code ?? $payable->country_code ?? null;
		
		if (empty($payable) || (empty($countryCode))) {
			$out .= $blankImage;
			$out .= ' ';
			$out .= '#' . $this->payable_id;
			
			return $out;
		}
		
		$countryName = $country->name ?? $countryCode;
		$countryFlagUrl = $country->flag_url ?? $payable->country_flag_url ?? null;
		
		// Post's Country
		if (!empty($countryFlagUrl)) {
			$out .= '<a href="' . dmUrl($countryCode, '/', true, true) . '" target="_blank">';
			$out .= '<img src="' . $countryFlagUrl . '" data-bs-toggle="tooltip" title="' . $countryName . '">';
			$out .= '</a>';
		} else {
			$out .= $blankImage;
		}
		$out .= ' ';
		
		// Payable (Post|User) ID
		$out .= '#' . $this->payable_id;
		
		// Payable Link
		$entityPath = $isPromoting ? 'posts' : 'users';
		$editEntityUrl = urlGen()->adminUrl($entityPath . '/' . $this->payable_id . '/edit');
		$deleteEntityUrl = urlGen()->adminUrl($entityPath . '/' . $this->payable_id);
		
		if ($isPromoting) {
			// $payableUrl = url(urlGen()->post($this->payable));
			$payableUrl = dmUrl($countryCode, urlGen()->postPath($this->payable));
			$out .= ' - ';
			$out .= '<a href="' . $payableUrl . '" target="_blank">' . $this->payable->title . '</a>';
		}
		
		if ($isSubscripting) {
			$out .= ' - ';
			$out .= '<a href="' . $editEntityUrl . '" target="_blank">' . $this->payable->name . '</a>';
		}
		
		if ($isPromoting && config('settings.listing_form.listings_review_activation')) {
			$outLeft = '<div class="float-start">' . $out . '</div>';
			$outRight = '<div class="float-end"></div>';
			
			if ($this->active != 1) {
				// Check if this payable (Post only) has at least valid & active payment
				$countValidActivePayments = Payment::query()
					->where('payable_type', $this->payable_type)
					->where('payable_id', $this->payable_id)
					->valid()->active()->count();
				
				if ($countValidActivePayments <= 0) {
					$msg = trans('admin.payment_listing_delete_btn_tooltip');
					$tooltip = ' data-bs-toggle="tooltip" title="' . $msg . '"';
					
					$outRight = '<div class="float-end">';
					$outRight .= '<a href="' . $deleteEntityUrl . '" class="btn btn-xs btn-danger" data-button-type="delete"' . $tooltip . '>';
					$outRight .= '<i class="fa-regular fa-trash-can"></i> ';
					$outRight .= trans('admin.Delete');
					$outRight .= '</a>';
					$outRight .= '</div>';
				}
			}
			
			$out = $outLeft . $outRight;
		}
		
		return $out;
	}
	
	public function getPackageNameHtml(): string
	{
		// Package Name
		$outLeft = $this->package_id;
		if (!empty($this->package)) {
			$periods = $this->starting_info . '<br>' . $this->expiry_info;
			$tooltip = ' data-bs-toggle="tooltip" data-bs-html="true" title="' . $periods . '"';
			$packageUrl = urlGen()->adminUrl('packages/' . $this->package_id . '/edit');
			
			$outLeft = '<a href="' . $packageUrl . '"' . $tooltip . '>';
			$outLeft .= $this->package->name;
			$outLeft .= '</a>';
			$outLeft .= ' (' . $this->package->price . ' ' . $this->package->currency_code . ')';
		}
		
		// Payment Status
		$tooltip = ' data-bs-toggle="tooltip" title="' . $this->expiry_info . '"';
		$cssClass = 'bg-' . $this->css_class_variant;
		
		$outRight = '<span class="badge ' . $cssClass . '"' . $tooltip . '>';
		$outRight .= $this->status_info;
		$outRight .= '</span>';
		
		// output
		$outLeft = '<div class="float-start">' . $outLeft . '</div>';
		$outRight = '<div class="float-end">' . $outRight . '</div>';
		
		return $outLeft . $outRight;
	}
	
	public function getPaymentMethodNameHtml(): string
	{
		$out = '--';
		
		if (!empty($this->paymentMethod)) {
			$paymentMethodUrl = urlGen()->adminUrl('payment_methods/' . $this->payment_method_id . '/edit');
			
			$out = '<a href="' . $paymentMethodUrl . '">';
			if ($this->paymentMethod->name == 'offlinepayment') {
				$out .= trans('offlinepayment::messages.offline_payment');
			} else {
				$out .= $this->paymentMethod->display_name;
			}
			$out .= '</a>';
		}
		
		return $out;
	}
	
	public function getAmountHtml()
	{
		$out = $this->amount;
		
		if (!empty($this->currency_code)) {
			$out .= ' ' . $this->currency_code;
		} else {
			if (!empty($this->package)) {
				$out .= ' ' . $this->package->currency_code;
			}
		}
		
		return $out;
	}
	
	// ===| OTHER METHODS |===
}
