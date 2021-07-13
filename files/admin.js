function importNextFromTravio() {
	Array.from(document.querySelectorAll('[data-import]')).some(div => {
		if (div.getAttribute('data-imported') !== null && div.getAttribute('data-imported') !== '')
			return false;

		let type = div.dataset.import;
		importFromTravio(type);
		return true;
	});
}

function importFromTravio(type) {
	let div = document.querySelector('[data-import="' + type + '"]');
	if (!div)
		return;

	div.setAttribute('data-imported', '0');
	div.loading();

	return ajax(PATH + 'import-from-travio', {'type': type}).then(r => {
		if (['services', 'packages'].includes(type)) {
			if (typeof r !== 'object' || !r.hasOwnProperty('items')) {
				alert(r);
				return;
			}

			setupItemsImport(type, r.items);
		} else if (r === 'ok') {
			div.innerHTML = 'Importato';
			div.setAttribute('data-imported', '1');

			importNextFromTravio();
		} else {
			alert(r);
			div.innerHTML = 'Errore';
		}

		return r;
	});
}

function setupItemsImport(type, items) {
	let div = document.querySelector('[data-import="' + type + '"]');
	if (!div)
		return;

	div.innerHTML = '<div class="travio-loading-import" id="travio-importing-' + type + '"><div style="width: 0%"></div></div>';
	importNextItem(type, items, 0);
}

function importNextItem(type, items, idx) {
	let itemsToUpdate = items.filter(item => item.update);
	if (itemsToUpdate.length < idx + 1) {
		return ajax(PATH + 'import-from-travio', {'type': type, 'finalize': JSON.stringify(items.map(item => item.id))}).then(r => {
			if (r === 'ok') {
				_('travio-importing-' + type).firstElementChild.style.width = '100%';
				document.querySelector('[data-import="' + type + '"]').setAttribute('data-imported', '1');
				importNextFromTravio();
			} else {
				alert(r);
			}
		});
	} else {
		return ajax(PATH + 'import-from-travio', {'type': type, 'item': JSON.stringify(itemsToUpdate[idx])}).then(r => {
			if (r === 'ok') {
				let percentage = (idx + 1) / (itemsToUpdate.length + 1) * 100;
				_('travio-importing-' + type).firstElementChild.style.width = percentage + '%';
				importNextItem(type, items, idx + 1);
			} else {
				alert(r);
			}
		});
	}
}
