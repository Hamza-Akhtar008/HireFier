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

/* Prevent errors, If these variables are missing. */
if (typeof oldInputAvailable === 'undefined') {
	var oldInputAvailable = false;
}
if (typeof packageIsEnabled === 'undefined') {
	var packageIsEnabled = false;
}
if (typeof editLabel === 'undefined') {
	var editLabel = 'Edit';
}

onDocumentReady((event) => {
	
	/* Select a category */
	getCategories(siteUrl, languageCode);
	$(document).on('click', '.cat-link, #selectCats .page-link', function (e) {
		e.preventDefault(); /* Prevents submission or reloading */
		
		getCategories(siteUrl, languageCode, this);
	});
	
});

/**
 * Get subcategories buffer and/or Append selected category
 *
 * @param siteUrl
 * @param languageCode
 * @param jsThis
 * @returns {boolean}
 */
function getCategories(siteUrl, languageCode, jsThis = null) {
	let csrfToken = $('input[name=_token]').val();
	
	/* Get Request URL */
	let url;
	
	let selectedId = $('#categoryId').val();
	let beingSelectedId;
	
	if (!isDefined(jsThis) || jsThis === null) {
		/* On page load, without click on the modal link */
		// ---
		beingSelectedId = !isEmpty(selectedId) ? selectedId : 0;
		
		/* Set the global selection URL */
		url = `${siteUrl}/browsing/categories/select`;
		
		if (!oldInputAvailable) {
			return false;
		}
		
	} else {
		/* Click on the modal link */
		// ---
		let thisEl = $(jsThis);
		
		/* Get the category selection URL */
		url = thisEl.attr('href');
		
		if (thisEl.hasClass('page-link')) {
			/* Get URL from pagination link */
			// ---
			
			/* Extract the category ID */
			beingSelectedId = 0;
			if (!isEmpty(url)) {
				beingSelectedId = urlQuery(url).getParameter('parentId') ?? 0;
			}
			
		} else {
			/* Get URL from data-selection-url */
			// ---
			
			if (thisEl.hasClass('open-selection-url')) {
				url = thisEl.data('selection-url');
			} else {
				/* Get the category ID */
				beingSelectedId = thisEl.data('id');
				beingSelectedId = !isEmpty(beingSelectedId) ? beingSelectedId : 0;
			}
			
		}
		
		/*
		 * Optimize the category selection
		 * by preventing AJAX request to append the selection
		 */
		let hasChildren = thisEl.data('has-children');
		if (isDefined(hasChildren) && (hasChildren === 0 || hasChildren === '0')) {
			let catName = thisEl.text();
			let catParentId = thisEl.data('parent-id');
			let catParentUrl = urlQuery(url).setParameters({parentId: catParentId}).toString();
			
			let linkText = `<i class="fa-regular fa-pen-to-square"></i> ${editLabel}`;
			let outputHtml = catName
				+ `[ <a href="#browseCategories"
						data-bs-toggle="modal"
						class="cat-link open-selection-url"
						data-selection-url="${catParentUrl}"
					>${linkText}</a> ]`;
			
			return appendSelectedCategory(beingSelectedId, outputHtml);
		}
	}
	
	const payload = {
		'parentId': beingSelectedId
	};
	if (!isEmpty(selectedId)) {
		payload['selectedId'] = selectedId;
	}
	
	/* Reorder the category list */
	/* const categoryListReorder = new BsRowColumnsReorder('#modalCategoryList', {defaultColumns: 6}); */
	
	/* AJAX Call */
	let ajax = $.ajax({
		method: 'GET',
		url: url,
		data: payload,
		beforeSend: function() {
			/*
			let spinner = '<i class="spinner-border"></i>';
			$('#selectCats').addClass('text-center').html(spinner);
			*/
			
			let selectCatsEl = $('#selectCats');
			selectCatsEl.empty().addClass('py-4').busyLoad('hide');
			selectCatsEl.busyLoad('show', {
				text: langLayout.loading,
				custom: createCustomSpinnerEl(),
				background: '#fff',
				containerItemClass: 'm-5',
			});
		}
	});
	ajax.done(function (xhr) {
		let selectCatsEl = $('#selectCats');
		selectCatsEl.removeClass('py-4').busyLoad('hide');
		
		if (!isDefined(xhr.html) || !isDefined(xhr.hasChildren)) {
			return false;
		}
		
		/* Get & append the category's children */
		if (xhr.hasChildren) {
			selectCatsEl.removeClass('text-center');
			selectCatsEl.html(xhr.html);
			
			/* Apply GridReorder to the new content */
			/* categoryListReorder.addNewContainers(selectCatsEl[0]); */
		} else {
			/*
			 * Section to append default category field info
			 * or to append selected category during form loading.
			 * Not intervene when the onclick event is fired.
			 */
			if (!isDefined(xhr.category) || !isDefined(xhr.category.id) || !isDefined(xhr.html)) {
				return false;
			}
			
			return appendSelectedCategory(xhr.category.id, xhr.html);
		}
	});
	ajax.fail(function(xhr) {
		let message = getErrorMessageFromXhr(xhr);
		if (message !== null) {
			jsAlert(message, 'error', false, true);
			
			/* Close the Modal */
			let modalEl = document.querySelector('#browseCategories');
			if (typeof modalEl !== 'undefined' && modalEl !== null) {
				let modalObj = bootstrap.Modal.getInstance(modalEl);
				if (modalObj !== null) {
					modalObj.hide();
				}
			}
		}
	});
}

/**
 * Append the selected category to its field in the form
 *
 * @param catId
 * @param outputHtml
 * @returns {boolean}
 */
function appendSelectedCategory(catId, outputHtml) {
	if (!isDefined(catId) || !isDefined(outputHtml)) {
		return false;
	}
	
	try {
		/* Select the category & append it */
		$('#catsContainer').html(outputHtml);
		
		/* Save data in hidden field */
		$('#categoryId').val(catId);
		
		/* Close the Modal */
		let modalEl = document.querySelector('#browseCategories');
		if (isDefined(modalEl) && modalEl !== null) {
			let modalObj = bootstrap.Modal.getInstance(modalEl);
			if (modalObj !== null) {
				modalObj.hide();
			}
		}
	} catch (e) {
		console.log(e);
	}
	
	return false;
}
