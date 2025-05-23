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

namespace App\Http\Controllers\Web\Admin\Panel\Traits;

trait AjaxTable
{
	/**
	 * The search function that is called by the data table.
	 *
	 * @return mixed [JSON] Array of cells in HTML form.
	 */
	public function search()
	{
		$this->xPanel->hasAccessOrFail('list');
		
		$totalRows = $filteredRows = $this->xPanel->count();
		
		// if a search term was present
		if ($this->request->input('search') && $this->request->input('search')['value']) {
			// filter the results accordingly
			$this->xPanel->applySearchTerm($this->request->input('search')['value']);
			// recalculate the number of filtered rows
			$filteredRows = $this->xPanel->count();
		}
		
		// start the results according to the datatables pagination
		if ($this->request->input('start')) {
			$this->xPanel->skip($this->request->input('start'));
		}
		
		// limit the number of results according to the datatables pagination
		if ($this->request->input('length')) {
			$this->xPanel->take($this->request->input('length'));
		}
		
		// overwrite any order set in the setup() method with the datatables order
		if ($this->request->input('order')) {
			$column_number = $this->request->input('order')[0]['column'];
			if ($this->xPanel->details_row) {
				$column_number = $column_number - 1;
			}
			$column_direction = $this->request->input('order')[0]['dir'];
			$column = $this->xPanel->findColumnById($column_number);
			
			if ($column['tableColumn']) {
				$this->xPanel->orderBy($column['name'], $column_direction);
			}
		}
		
		$entries = $this->xPanel->getEntries();
		
		return $this->xPanel->getEntriesAsJsonForDatatables($entries, $totalRows, $filteredRows);
	}
}
