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

namespace App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel;

trait FakeColumns
{
	/**
	 * Returns an array of database columns names, that are used to store fake values.
	 * Returns ['extras'] if no columns have been found.
	 *
	 * @param string $form
	 * @return array|string[]
	 */
	public function getFakeColumnsAsArray($form = 'create')
	{
		$fakeFieldColumnsToEncode = [];
		
		// get the right fields according to the form type (create/update)
		switch (strtolower($form)) {
			case 'update':
				$fields = $this->updateFields;
				break;
			
			default:
				$fields = $this->createFields;
				break;
		}
		
		foreach ($fields as $k => $field) {
			// if it's a fake field
			if (isset($fields[$k]['fake']) && $fields[$k]['fake'] == true) {
				// add it to the request in its appropriate variable - the one defined, if defined
				if (isset($fields[$k]['store_in'])) {
					if (!in_array($fields[$k]['store_in'], $fakeFieldColumnsToEncode, true)) {
						array_push($fakeFieldColumnsToEncode, $fields[$k]['store_in']);
					}
				} else {
					// otherwise in the one defined in the $crud variable
					
					if (!in_array('extras', $fakeFieldColumnsToEncode, true)) {
						array_push($fakeFieldColumnsToEncode, 'extras');
					}
				}
			}
		}
		
		if (!count($fakeFieldColumnsToEncode)) {
			return ['extras'];
		}
		
		return $fakeFieldColumnsToEncode;
	}
}
