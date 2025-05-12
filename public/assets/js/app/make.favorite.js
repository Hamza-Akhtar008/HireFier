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

if (typeof isLogged === 'undefined') {
	var isLogged = false;
}

onDocumentReady((event) => {
	
	/* Save the Post */
	const makeFavoriteEls = document.querySelectorAll('a.make-favorite, a.save-job, a.saved-job');
	if (makeFavoriteEls.length > 0) {
		makeFavoriteEls.forEach((element) => {
			element.addEventListener('click', (event) => {
				event.preventDefault(); /* Prevents submission or reloading */
				
				if (isLogged !== true) {
					openLoginModal();
					return false;
				}
				
				savePost(event.target);
			});
		});
	}
	
	/* Save the Search */
	const saveSearchEl = document.getElementById('saveSearch');
	if (saveSearchEl) {
		saveSearchEl.addEventListener('click', (event) => {
			event.preventDefault(); /* Prevents submission or reloading */
			
			if (isLogged !== true) {
				openLoginModal();
				return false;
			}
			
			saveSearch(event.target);
		});
	}
	
});

/**
 * Save Ad
 * @param el
 * @returns {boolean}
 */
async function savePost(el) {
	if (el.tagName.toLowerCase() === 'span') {
		el = el.parentElement;
	}
	
	/* Get element's icon */
	let iconEl = null;
	if (el.tagName.toLowerCase() === 'a') {
		iconEl = el.querySelector('span') || el.querySelector('i');
	}
	
	const postId = el.closest('li').id;
	if (!postId) {
		console.error("Listing ID not found.");
		return false;
	}
	
	const url = `${siteUrl}/account/saved-posts/toggle`;
	const _tokenEl = document.querySelector('input[name=_token]');
	const data = {
		'post_id': postId,
		'_token': _tokenEl.value ?? null
	};
	
	showWaitingDialog();
	
	try {
		const json = await httpRequest('POST', url, data);
		
		hideWaitingDialog();
		
		if (json.isLogged === undefined) {
			return false;
		}
		
		const isNotLogged = (json.isLogged !== true);
		const isUnauthorized = (json.status && (json.status === 401 || json.status === 419));
		
		if (isNotLogged || isUnauthorized) {
			openLoginModal();
			
			if (json.message) {
				jsAlert(json.message, 'error', false);
			}
			
			return false;
		}
		
		if (json.isSaved === true) {
			if (el.classList.contains('btn')) {
				const saveBtnEl = document.getElementById(json.postId);
				saveBtnEl.classList.add('saved-job');
				
				const saveBtnLinkEl = document.querySelector(`#${json.postId} a`);
				saveBtnLinkEl.classList.add('saved-job');
			} else {
				const tooltip = 'data-bs-toggle="tooltip" title="' + lang.labelSavePostRemove + '"';
				el.innerHTML = '<i class="bi bi-bookmark-fill" ' + tooltip + '></i> ' + lang.labelSavePostRemove;
			}
			jsAlert(json.message, 'success');
		} else {
			if (el.classList.contains('btn')) {
				const saveBtnEl = document.getElementById(json.postId);
				saveBtnEl.classList.remove('saved-job');
				
				const saveBtnLinkEl = document.querySelector(`#${json.postId} a`);
				saveBtnLinkEl.classList.remove('saved-job');
			} else {
				const tooltip = 'data-bs-toggle="tooltip" title="' + lang.labelSavePostSave + '"';
				el.innerHTML = '<i class="bi bi-bookmark" ' + tooltip + '></i> ' + lang.labelSavePostSave;
			}
			jsAlert(json.message, 'success');
		}
		
		return false;
	} catch (error) {
		hideWaitingDialog();
		
		if (error.response && error.response.status) {
			const response = error.response;
			if (response.status === 401 || response.status === 419) {
				/*
				 * Since the modal login code is injected only for guests,
				 * the line below can be fired only for guests (i.e. when user is not logged in)
				 */
				openLoginModal();
				
				if (!isLogged) {
					return false;
				}
			}
		}
		
		const message = getErrorMessage(error);
		if (message !== null) {
			jsAlert(message, 'error', false);
		}
	}
	
	return false;
}

/**
 * Save Search
 * @param el
 * @returns {boolean}
 */
async function saveSearch(el) {
	if (el.tagName.toLowerCase() === 'i') {
		el = el.parentElement;
	}
	
	let searchUrl = el.dataset.searchUrl;
	let resultsCount = el.dataset.resultsCount;
	
	if (!searchUrl) {
		console.error("Search URL not found.");
		return false;
	}
	
	showWaitingDialog();
	
	let url = `${siteUrl}/account/saved-searches/store`;
	const _tokenEl = document.querySelector('input[name=_token]');
	const data = {
		'search_url': searchUrl,
		'results_count': resultsCount,
		'_token': _tokenEl.value ?? null
	};
	
	try {
		const json = await httpRequest('POST', url, data);
		
		hideWaitingDialog();
		
		if (typeof json.isLogged === 'undefined') {
			return false;
		}
		
		if (json.isLogged !== true) {
			openLoginModal();
			return false;
		}
		
		/* Logged Users - Notification */
		jsAlert(json.message, 'success');
	} catch (error) {
		hideWaitingDialog();
		
		if (error.response && error.response.status) {
			const response = error.response;
			if (response.status === 401 || response.status === 419) {
				openLoginModal();
				return false;
			}
		}
		
		const message = getErrorMessage(error);
		if (message !== null) {
			jsAlert(message, 'error', false);
		}
	}
	
	return false;
}
