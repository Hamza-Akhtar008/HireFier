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

namespace App\Models\Section\Home;

class CompaniesSection
{
	public static function getValues($value)
	{
		if (empty($value)) {
			
			$value['max_items'] = '12';
			
		} else {
			
			if (!isset($value['max_items'])) {
				$value['max_items'] = '12';
			}
			
		}
		
		return $value;
	}
	
	public static function setValues($value, $setting)
	{
		return $value;
	}
	
	public static function getFields($diskName): array
	{
		$fields = [
			[
				'name'    => 'max_items',
				'label'   => trans('admin.Max Items'),
				'type'    => 'number',
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'        => 'order_by',
				'label'       => trans('admin.Order By'),
				'type'        => 'select2_from_array',
				'options'     => [
					'date'   => 'Date',
					'random' => 'Random',
				],
				'allows_null' => false,
				'wrapper'     => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'       => 'cache_expiration',
				'label'      => trans('admin.Cache Expiration Time for this section'),
				'type'       => 'number',
				'attributes' => [
					'placeholder' => '0',
				],
				'hint'       => trans('admin.section_cache_expiration_hint'),
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'  => 'separator_last',
				'type'  => 'custom_html',
				'value' => '<hr>',
			],
			[
				'name'  => 'hide_on_mobile',
				'label' => trans('admin.hide_on_mobile_label'),
				'type'  => 'checkbox_switch',
				'hint'  => trans('admin.hide_on_mobile_hint'),
			],
			[
				'name'  => 'active',
				'label' => trans('admin.Active'),
				'type'  => 'checkbox_switch',
			],
		];
		
		return $fields;
	}
}
