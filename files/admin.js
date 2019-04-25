function importFromTravio(type) {
	toolbarButtonLoading('import');
	ajax(PATH + 'import-from-travio', {'type': type}).then(r => {
		if (r === 'ok') {
			document.location.reload();
		} else {
			alert(r);
			toolbarButtonRestore('import');
		}
	});
}
