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

/**
 * Set user type
 * @param userTypeId
 * @returns {boolean}
 */
function setUserType(userTypeId) {
	let companyElSelector = '#companyBloc';
	let resumeElSelector = '#resumeBloc';
	
	setElementsVisibility("hide", companyElSelector);
	setElementsVisibility("hide", resumeElSelector);
	
	let coNameDivEl;
	const coNameEl = document.querySelector('input[name="company[name]"]');
	if (coNameEl) {
		coNameDivEl = coNameEl.closest("div");
		if (coNameDivEl) {
			coNameDivEl.classList.remove("required");
		}
	}
	
	let coDescriptionDivEl;
	const coDescriptionEl = document.querySelector('textarea[name="company[description]"]');
	if (coDescriptionEl) {
		coDescriptionDivEl = coDescriptionEl.closest("div");
		if (coDescriptionDivEl) {
			coDescriptionDivEl.classList.remove("required");
		}
	}
	
	if (userTypeId === 1 || userTypeId === '1') {
		setElementsVisibility("show", companyElSelector);
		if (coNameDivEl) {
			coNameDivEl.classList.add("required");
		}
		if (coDescriptionDivEl) {
			coDescriptionDivEl.classList.add("required");
		}
	}
	
	if (userTypeId === 2 || userTypeId === '2') {
		setElementsVisibility("show", resumeElSelector);
	}
}
