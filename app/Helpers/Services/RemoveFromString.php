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

namespace App\Helpers\Services;

class RemoveFromString
{
	/**
	 * Remove Direct Contact Info from string
	 *
	 * @param string|null $string
	 * @param bool $beforeStorage
	 * @param bool $altText
	 * @return string|null
	 */
	public static function contactInfo(?string $string, bool $beforeStorage = false, bool $altText = false): ?string
	{
		if ($beforeStorage) {
			if (config('settings.listing_form.remove_url_before')) {
				$string = self::links($string, $altText);
			}
			if (config('settings.listing_form.remove_email_before')) {
				$string = self::emails($string, $altText);
			}
			if (config('settings.listing_form.remove_phone_before')) {
				$string = self::phoneNumbers($string, $altText);
			}
		} else {
			if (config('settings.listing_form.remove_url_after')) {
				$string = self::links($string, $altText);
			}
			if (config('settings.listing_form.remove_email_after')) {
				$string = self::emails($string, $altText);
			}
			if (config('settings.listing_form.remove_phone_after')) {
				$string = self::phoneNumbers($string, $altText);
			}
		}
		
		return $string;
	}
	
	/**
	 * Remove Links & URL from string
	 *
	 * @param string|null $string
	 * @param bool $altText
	 * @param bool $removeLinksText
	 * @return string|null
	 */
	public static function links(?string $string, bool $altText = false, bool $removeLinksText = false): ?string
	{
		$replace = ($altText) ? ' [***] ' : ' ';
		
		if (!$removeLinksText) {
			$string = preg_replace('/<a.*?>(.*?)<\/a>/ui', '\1', $string);
		} else {
			$string = preg_replace('/<a.*?>.*?<\/a>/ui', $replace, $string);
		}
		
		$pattern = '/\b((https?|ftp|file):\/\/|www\.)[-a-z\d+&@#\/%?=~_|$!:,.;]*[a-z\d+&@#\/%=~_|$]/ui';
		$string = preg_replace($pattern, $replace, $string);
		
		if (isDemoDomain()) {
			// Delete everything that looks like domain name
			// (in experimentation)
			$string = self::emails($string, $altText); // Need to be run first
			$pattern = '/[a-z\d]+\.[a-z\/]{2,6}/i';
			$string = preg_replace($pattern, $replace, $string);
		}
		
		return self::normalizeExcessBlankSpaces($string);
	}
	
	/**
	 * Remove Email Addresses from string
	 *
	 * @param string|null $string
	 * @param bool $altText
	 * @return string|null
	 */
	public static function emails(?string $string, bool $altText = false): ?string
	{
		$replace = ($altText) ? ' [***] ' : ' ';
		
		$patterns = [
			'[a-z\d\-._%+]+@[a-z\d\-.]+\.[a-z]{2,4}\b',
			'[a-z\d\-_]+(\.[a-z\d\-_]+)*@[a-z\d\-]+(\.[a-z\d\-]+)*(\.[a-z]{2,3})',
			'([a-z\d\-._]+)@((\[\d{1,3}\.\d{1,3}\.\d{1,3}\.)|(([a-z\d\-]+\.)+))([a-z]{2,4}|\d{1,3})(\]?)',
		];
		foreach ($patterns as $pattern) {
			$pattern = '/' . $pattern . '/i';
			$string = preg_replace($pattern, $replace, $string);
		}
		
		return self::normalizeExcessBlankSpaces($string);
	}
	
	/**
	 * Remove Phone Numbers from string
	 *
	 * @param string|null $string
	 * @param bool $altText
	 * @return string|null
	 */
	public static function phoneNumbers(?string $string, bool $altText = false): ?string
	{
		$replace = ($altText) ? ' [***] ' : ' ';
		
		$pattern = '/([()\\s]?[+\\s]?\d+[\-.()\\s]?\d+[\-.()\\s]?){4,}/ui';
		$string = preg_replace($pattern, $replace, $string);
		
		return self::normalizeExcessBlankSpaces($string);
	}
	
	/**
	 * @param $string
	 * @return string|null
	 */
	private static function normalizeExcessBlankSpaces($string): ?string
	{
		$string = preg_replace('/ +/', ' ', $string);
		
		return is_string($string) ? $string : null;
	}
}
