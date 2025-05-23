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

class StatsSection
{
	public static function getValues($value)
	{
		if (empty($value)) {
			
			$value['icon_count_posts'] = 'fa-solid fa-briefcase';
			$value['icon_count_users'] = 'bi bi-people';
			$value['icon_count_locations'] = 'bi bi-geo-alt';
			$value['counter_up_delay'] = 10;
			$value['counter_up_time'] = 2000;
			
		} else {
			
			if (!isset($value['icon_count_posts'])) {
				$value['icon_count_posts'] = 'fa-solid fa-briefcase';
			}
			if (!isset($value['icon_count_users'])) {
				$value['icon_count_users'] = 'bi bi-people';
			}
			if (!isset($value['icon_count_locations'])) {
				$value['icon_count_locations'] = 'bi bi-geo-alt';
			}
			if (!isset($value['counter_up_delay'])) {
				$value['counter_up_delay'] = 10;
			}
			if (!isset($value['counter_up_time'])) {
				$value['counter_up_time'] = 2000;
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
		$defaultFontIconSet = config('larapen.core.defaultFontIconSet', 'bootstrap');
		$fields = [
			[
				'name'  => 'count_posts',
				'type'  => 'custom_html',
				'value' => trans('admin.count_posts_info'),
			],
			[
				'name'    => 'icon_count_posts',
				'label'   => trans('admin.Icon'),
				'type'    => 'icon_picker',
				'iconset' => config('larapen.core.fontIconSet.' . $defaultFontIconSet . '.key'),
				'version' => config('larapen.core.fontIconSet.' . $defaultFontIconSet . '.version'),
				'wrapper' => [
					'class' => 'col-md-2',
				],
			],
			[
				'name'       => 'custom_counts_posts',
				'label'      => trans('admin.custom_counter_up_label'),
				'type'       => 'number',
				'attributes' => [
					'min'  => 0,
					'step' => 1,
				],
				'hint'       => trans('admin.custom_counter_up_hint'),
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'prefix_count_posts',
				'label'   => trans('admin.prefix_counter_up_label'),
				'type'    => 'text',
				'hint'    => trans('admin.counter_up_prefix_suffix_hint'),
				'wrapper' => [
					'class' => 'col-md-2',
				],
			],
			[
				'name'    => 'suffix_count_posts',
				'label'   => trans('admin.suffix_counter_up_label'),
				'type'    => 'text',
				'hint'    => trans('admin.counter_up_prefix_suffix_hint'),
				'wrapper' => [
					'class' => 'col-md-2',
				],
			],
			
			
			[
				'name'  => 'count_users',
				'type'  => 'custom_html',
				'value' => trans('admin.count_users_info'),
			],
			[
				'name'    => 'icon_count_users',
				'label'   => trans('admin.Icon'),
				'type'    => 'icon_picker',
				'iconset' => config('larapen.core.fontIconSet.' . $defaultFontIconSet . '.key'),
				'version' => config('larapen.core.fontIconSet.' . $defaultFontIconSet . '.version'),
				'wrapper' => [
					'class' => 'col-md-2',
				],
			],
			[
				'name'       => 'custom_counts_users',
				'label'      => trans('admin.custom_counter_up_label'),
				'type'       => 'number',
				'attributes' => [
					'min'  => 0,
					'step' => 1,
				],
				'hint'       => trans('admin.custom_counter_up_hint'),
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'prefix_count_users',
				'label'   => trans('admin.prefix_counter_up_label'),
				'type'    => 'text',
				'hint'    => trans('admin.counter_up_prefix_suffix_hint'),
				'wrapper' => [
					'class' => 'col-md-2',
				],
			],
			[
				'name'    => 'suffix_count_users',
				'label'   => trans('admin.suffix_counter_up_label'),
				'type'    => 'text',
				'hint'    => trans('admin.counter_up_prefix_suffix_hint'),
				'wrapper' => [
					'class' => 'col-md-2',
				],
			],
			
			
			[
				'name'  => 'count_locations',
				'type'  => 'custom_html',
				'value' => trans('admin.count_locations_info'),
			],
			[
				'name'    => 'icon_count_locations',
				'label'   => trans('admin.Icon'),
				'type'    => 'icon_picker',
				'iconset' => config('larapen.core.fontIconSet.' . $defaultFontIconSet . '.key'),
				'version' => config('larapen.core.fontIconSet.' . $defaultFontIconSet . '.version'),
				'wrapper' => [
					'class' => 'col-md-2',
				],
			],
			[
				'name'       => 'custom_counts_locations',
				'label'      => trans('admin.custom_counter_up_label'),
				'type'       => 'number',
				'attributes' => [
					'min'  => 0,
					'step' => 1,
				],
				'hint'       => trans('admin.custom_counter_up_hint'),
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'prefix_count_locations',
				'label'   => trans('admin.prefix_counter_up_label'),
				'type'    => 'text',
				'hint'    => trans('admin.counter_up_prefix_suffix_hint'),
				'wrapper' => [
					'class' => 'col-md-2',
				],
			],
			[
				'name'    => 'suffix_count_locations',
				'label'   => trans('admin.suffix_counter_up_label'),
				'type'    => 'text',
				'hint'    => trans('admin.counter_up_prefix_suffix_hint'),
				'wrapper' => [
					'class' => 'col-md-2',
				],
			],
			
			[
				'name'  => 'counter_up_options',
				'type'  => 'custom_html',
				'value' => trans('admin.counter_up_options_info'),
			],
			[
				'name'       => 'counter_up_delay',
				'label'      => trans('admin.counter_up_delay_label'),
				'type'       => 'number',
				'attributes' => [
					'min'  => 0,
					'max'  => 50000,
					'step' => 1,
				],
				'hint'       => trans('admin.counter_up_delay_hint'),
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'       => 'counter_up_time',
				'label'      => trans('admin.counter_up_time_label'),
				'type'       => 'number',
				'attributes' => [
					'min'  => 0,
					'max'  => 50000,
					'step' => 1,
				],
				'hint'       => trans('admin.counter_up_time_hint'),
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'  => 'disable_counter_up',
				'label' => trans('admin.disable_counter_up_label'),
				'type'  => 'checkbox_switch',
				'hint'  => trans('admin.disable_counter_up_hint'),
			],
			
			[
				'name'  => 'separator_last',
				'type'  => 'custom_html',
				'value' => '<hr>',
			],
			[
				'name'    => 'hide_on_mobile',
				'label'   => trans('admin.hide_on_mobile_label'),
				'type'    => 'checkbox_switch',
				'hint'    => trans('admin.hide_on_mobile_hint'),
				'wrapper' => [
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
				'name'  => 'active',
				'label' => trans('admin.Active'),
				'type'  => 'checkbox_switch',
			],
		];
		
		return $fields;
	}
}
