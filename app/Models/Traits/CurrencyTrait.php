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

trait CurrencyTrait
{
	// ===| ADMIN PANEL METHODS |===
	
	public function getNameHtml(): string
	{
		$currentUrl = preg_replace('#/(search)$#', '', url()->current());
		$url = $currentUrl . '/' . $this->getKey() . '/edit';
		
		return '<a href="' . $url . '">' . $this->name . '</a>';
	}
	
	public function getSymbolHtml(): string
	{
		return html_entity_decode($this->symbol);
	}
	
	public function getPositionHtml(): string
	{
		$toggleIcon = ($this->in_left == 1)
			? 'fa-solid fa-toggle-on'
			: 'fa-solid fa-toggle-off';
		
		return '<i class="admin-single-icon ' . $toggleIcon . '" aria-hidden="true"></i>';
	}
	
	// ===| OTHER METHODS |===
}
