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
		if (r === 'ok') {
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
