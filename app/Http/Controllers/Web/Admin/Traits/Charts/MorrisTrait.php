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

namespace App\Http\Controllers\Web\Admin\Traits\Charts;

use App\Helpers\Common\Date;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Carbon;

trait MorrisTrait
{
	/**
	 * Graphic chart: Get listings number per day (for X days)
	 *
	 * @param int $daysNumber
	 * @return array
	 */
	private function getLatestListingsForMorris(int $daysNumber = 7): array
	{
		// Init.
		$daysNumber = (is_numeric($daysNumber)) ? $daysNumber : 7;
		
		// Get start date
		$startDate = Carbon::now(Date::getAppTimeZone())->subDays($daysNumber);
		$startDate = $startDate->toDateString() . ' 00:00:00';
		
		// Get end date
		$endDate = Carbon::now(Date::getAppTimeZone());
		$endDate = $endDate->toDateString() . ' 23:59:59';
		
		// Select only required columns
		$select = ['id', 'created_at'];
		
		// Get listings from latest $daysNumber days
		$activatedPosts = Post::query()
			->withoutAppends()
			->verified()
			->where('created_at', '>=', $startDate)
			->where('created_at', '<=', $endDate)
			->orderByDesc('created_at')
			->get($select);
		
		$unactivatedPosts = Post::query()
			->withoutAppends()
			->unverified()
			->where('created_at', '>=', $startDate)
			->where('created_at', '<=', $endDate)
			->orderByDesc('created_at')
			->get($select);
		
		// Get listings number per day
		$currentDate = Carbon::now(Date::getAppTimeZone());
		$stats = [];
		for ($i = 1; $i <= $daysNumber; $i++) {
			$dateObj = ($i == 1) ? $currentDate : $currentDate->subDay();
			$date = $dateObj->toDateString();
			
			// Get start & end date|time
			$startDate = $date . ' 00:00:00';
			$endDate = $date . ' 23:59:59';
			
			// Count the listings of this day
			$countActivatedPosts = collect($activatedPosts)
				->filter(function ($item) use ($startDate, $endDate) {
					return (
						strtotime($item->created_at) >= strtotime($startDate)
						&& strtotime($item->created_at) <= strtotime($endDate)
					);
				})->count();
			
			$countUnactivatedPosts = collect($unactivatedPosts)
				->filter(function ($item) use ($startDate, $endDate) {
					return (
						strtotime($item->created_at) >= strtotime($startDate)
						&& strtotime($item->created_at) <= strtotime($endDate)
					);
				})->count();
			
			$stats['posts'][$i]['y'] = mb_ucfirst(Date::format($dateObj, 'stats'));
			$stats['posts'][$i]['activated'] = $countActivatedPosts;
			$stats['posts'][$i]['unactivated'] = $countUnactivatedPosts;
		}
		
		$stats['posts'] = array_reverse($stats['posts'], true);
		
		$data = json_encode(array_values($stats['posts']), JSON_NUMERIC_CHECK);
		
		return [
			'title' => trans('admin.Ads Stats'),
			'data'  => $data,
		];
	}
	
	/**
	 * Graphic chart: Get users number per day (for X days)
	 *
	 * @param int $daysNumber
	 * @return array
	 */
	private function getLatestUsersForMorris(int $daysNumber = 7): array
	{
		// Init.
		$daysNumber = (is_numeric($daysNumber)) ? $daysNumber : 7;
		
		// Get start date
		$startDate = Carbon::now(Date::getAppTimeZone())->subDays($daysNumber);
		$startDate = $startDate->toDateString() . ' 00:00:00';
		
		// Get end date
		$endDate = Carbon::now(Date::getAppTimeZone());
		$endDate = $endDate->toDateString() . ' 23:59:59';
		
		// Select only required columns
		$select = ['id', 'created_at'];
		
		// Get listings from latest $daysNumber days
		$activatedUsers = User::query()
			->withoutAppends()
			->doesntHave('permissions')
			->verified()
			->where('created_at', '>=', $startDate)
			->where('created_at', '<=', $endDate)
			->get($select);
		
		$unactivatedUsers = User::query()
			->withoutAppends()
			->doesntHave('permissions')
			->unverified()
			->where('created_at', '>=', $startDate)
			->where('created_at', '<=', $endDate)
			->get($select);
		
		// Get listings number per day
		$currentDate = Carbon::now(Date::getAppTimeZone());
		$stats = [];
		for ($i = 1; $i <= $daysNumber; $i++) {
			$dateObj = ($i == 1) ? $currentDate : $currentDate->subDay();
			$date = $dateObj->toDateString();
			
			// Get start & end date|time
			$startDate = $date . ' 00:00:00';
			$endDate = $date . ' 23:59:59';
			
			// Count the listings of this day
			$countActivatedUsers = collect($activatedUsers)
				->filter(function ($item) use ($startDate, $endDate) {
					return (
						strtotime($item->created_at) >= strtotime($startDate)
						&& strtotime($item->created_at) <= strtotime($endDate)
					);
				})->count();
			
			$countUnactivatedUsers = collect($unactivatedUsers)
				->filter(function ($item) use ($startDate, $endDate) {
					return (
						strtotime($item->created_at) >= strtotime($startDate)
						&& strtotime($item->created_at) <= strtotime($endDate)
					);
				})->count();
			
			$stats['users'][$i]['y'] = mb_ucfirst(Date::format($dateObj, 'stats'));
			$stats['users'][$i]['activated'] = $countActivatedUsers;
			$stats['users'][$i]['unactivated'] = $countUnactivatedUsers;
		}
		
		$stats['users'] = array_reverse($stats['users'], true);
		
		$data = json_encode(array_values($stats['users']), JSON_NUMERIC_CHECK);
		
		return [
			'title' => trans('admin.Users Stats'),
			'data'  => $data,
		];
	}
}
