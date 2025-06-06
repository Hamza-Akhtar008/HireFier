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

namespace App\Models\Setting;

use App\Helpers\Common\Date;

/*
 * settings.backup.option
 */

class BackupSetting
{
	public static function getValues($value, $disk)
	{
		if (empty($value)) {
			
			$value['disable_notifications'] = '1';
			$value['keep_all_backups_for_days'] = '7';
			$value['keep_daily_backups_for_days'] = '16';
			$value['keep_weekly_backups_for_weeks'] = '8';
			$value['keep_monthly_backups_for_months'] = '4';
			$value['keep_yearly_backups_for_years'] = '2';
			$value['maximum_storage_in_megabytes'] = '5000';
			
		} else {
			
			if (!array_key_exists('disable_notifications', $value)) {
				$value['disable_notifications'] = '1';
			}
			if (!array_key_exists('keep_all_backups_for_days', $value)) {
				$value['keep_all_backups_for_days'] = '7';
			}
			if (!array_key_exists('keep_daily_backups_for_days', $value)) {
				$value['keep_daily_backups_for_days'] = '16';
			}
			if (!array_key_exists('keep_weekly_backups_for_weeks', $value)) {
				$value['keep_weekly_backups_for_weeks'] = '8';
			}
			if (!array_key_exists('keep_monthly_backups_for_months', $value)) {
				$value['keep_monthly_backups_for_months'] = '4';
			}
			if (!array_key_exists('keep_yearly_backups_for_years', $value)) {
				$value['keep_yearly_backups_for_years'] = '2';
			}
			if (!array_key_exists('maximum_storage_in_megabytes', $value)) {
				$value['maximum_storage_in_megabytes'] = '5000';
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
				'name'  => 'backups_list',
				'type'  => 'custom_html',
				'value' => trans('admin.backups_list_value'),
			],
			[
				'name'  => 'backup_link_btn_hint',
				'type'  => 'custom_html',
				'value' => trans('admin.backup_link_btn_hint_value'),
			],
			[
				'name'  => 'backup_link_btn',
				'type'  => 'custom_html',
				'value' => trans('admin.backup_link_btn_value'),
			],
			
			[
				'name'  => 'backup_storage_disk',
				'type'  => 'custom_html',
				'value' => trans('admin.backup_storage_disk_value'),
			],
			[
				'name'    => 'storage_disk',
				'label'   => trans('admin.storage_disk_label'),
				'type'    => 'select2_from_array',
				'options' => [
					0 => trans('admin.storage_disk_option_0'),
					1 => trans('admin.storage_disk_option_1'),
					2 => trans('admin.storage_disk_option_2'),
				],
				'hint'    => trans('admin.storage_disk_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'disable_notifications',
				'label'   => trans('admin.backup_disable_notifications_label'),
				'type'    => 'checkbox_switch',
				'hint'    => trans('admin.backup_disable_notifications_hint', ['email' => config('settings.app.email')]),
				'wrapper' => [
					'class' => 'col-md-6 mt-3',
				],
			],
			
			[
				'name'  => 'backup_schedule',
				'type'  => 'custom_html',
				'value' => trans('admin.backup_schedule_value'),
			],
			[
				'name'  => 'help_backup_sep',
				'type'  => 'custom_html',
				'value' => trans('admin.card_body', [
					'text'        => trans('admin.help_backup', [
						'backupLocalStorage' => relativeAppPath(storage_path('backups')),
					]), 'bgColor' => 'bg-light-warning',
				]),
			],
			[
				'name'  => 'backup_sep_2',
				'type'  => 'custom_html',
				'value' => '<hr>',
			],
			[
				'name'  => 'cron_info_sep',
				'type'  => 'custom_html',
				'value' => trans('admin.cron_info_sep_value'),
			],
			[
				'name'    => 'taking_backup',
				'label'   => trans('admin.taking_backup_label'),
				'type'    => 'select2_from_array',
				'options' => self::backupFrequencies(),
				'hint'    => trans('admin.taking_backup_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'taking_backup_at',
				'label'   => trans('admin.taking_backup_at_label'),
				'type'    => 'select2_from_array',
				'options' => self::backupFrequencyAt(),
				'hint'    => trans('admin.taking_backup_at_hint', ['timeZone' => Date::getAppTimeZone()]),
				'wrapper' => [
					'class' => 'col-md-6 taking-backup-field',
				],
			],
			
			[
				'name'  => 'backup_cleanup_sep',
				'type'  => 'custom_html',
				'value' => trans('admin.backup_cleanup_sep_value'),
			],
			[
				'name'  => 'backup_cleanup_rules',
				'type'  => 'custom_html',
				'value' => trans('admin.backup_cleanup_rules_value'),
			],
			[
				'name'    => 'keep_all_backups_for_days',
				'label'   => trans('admin.keep_all_backups_for_days_label'),
				'type'    => 'number',
				'hint'    => trans('admin.keep_all_backups_for_days_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'keep_daily_backups_for_days',
				'label'   => trans('admin.keep_daily_backups_for_days_label'),
				'type'    => 'number',
				'hint'    => trans('admin.keep_daily_backups_for_days_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'keep_weekly_backups_for_weeks',
				'label'   => trans('admin.keep_weekly_backups_for_weeks_label'),
				'type'    => 'number',
				'hint'    => trans('admin.keep_weekly_backups_for_weeks_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'keep_monthly_backups_for_months',
				'label'   => trans('admin.keep_monthly_backups_for_months_label'),
				'type'    => 'number',
				'hint'    => trans('admin.keep_monthly_backups_for_months_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'keep_yearly_backups_for_years',
				'label'   => trans('admin.keep_yearly_backups_for_years_label'),
				'type'    => 'number',
				'hint'    => trans('admin.keep_yearly_backups_for_years_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'maximum_storage_in_megabytes',
				'label'   => trans('admin.maximum_storage_in_megabytes_label'),
				'type'    => 'number',
				'hint'    => trans('admin.maximum_storage_in_megabytes_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
		];
		
		return addOptionsGroupJavaScript(__NAMESPACE__, __CLASS__, $fields);
	}
	
	/**
	 * @return array
	 */
	private static function backupFrequencies(): array
	{
		return [
			'none'    => trans('admin.taking_backup_option_0'),
			'daily'   => trans('admin.taking_backup_option_1'),
			'weekly'  => trans('admin.taking_backup_option_2'),
			'monthly' => trans('admin.taking_backup_option_3'),
			'yearly'  => trans('admin.taking_backup_option_4'),
		];
	}
	
	/**
	 * @return array
	 */
	private static function backupFrequencyAt(): array
	{
		$hours = [];
		
		for ($i = 0; $i <= 23; $i++) {
			$hh = str_pad($i, 2, '0', STR_PAD_LEFT);
			for ($j = 0; $j <= 59; $j += 15) {
				$mm = str_pad($j, 2, '0', STR_PAD_LEFT);
				$hour = $hh . ':' . $mm;
				$hours[$hour] = $hour;
			}
		}
		
		return $hours;
	}
}
