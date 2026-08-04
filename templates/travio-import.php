<div class="m-3 p-3" style="border: solid #999 1px; box-shadow: 0 0 10px #EEE">
	<div class="pb-3 row">
		<div class="col-4">
			<h2>Importazioni</h2>
		</div>
		<div class="col">
			<input type="button" class="btn btn-primary" value="Lancia importazioni" onclick="this.style.display = 'none'; importNextFromTravio()"/>
		</div>
	</div>

	<div class="py-2 row">
		<div class="col-4">
			Destinazioni
		</div>
		<div class="col" data-import="geo"></div>
	</div>

	<div class="py-2 row">
		<div class="col-4">
			Porti
		</div>
		<div class="col" data-import="ports"></div>
	</div>

	<div class="py-2 row">
		<div class="col-4">
			Aeroporti
		</div>
		<div class="col" data-import="airports"></div>
	</div>

	<div class="py-2 row">
		<div class="col-4">
			Tags
		</div>
		<div class="col" data-import="tags"></div>
	</div>

	<div class="py-2 row">
		<div class="col-4">
			Amenities
		</div>
		<div class="col" data-import="amenities"></div>
	</div>

	<div class="py-2 row">
		<div class="col-4">
			Classificazioni
		</div>
		<div class="col" data-import="classifications"></div>
	</div>

	<div class="py-2 row">
		<div class="col-4">
			Sottotipologie servizi
		</div>
		<div class="col" data-import="services-typologies"></div>
	</div>

	<div class="py-2 row">
		<div class="col-4">
			Anagrafiche
		</div>
		<div class="col" data-import="master-data"></div>
	</div>

	<div class="py-2 row">
		<div class="col-4">
			Servizi
		</div>
		<div class="col" data-import="services"></div>
	</div>

	<div class="py-2 row">
		<div class="col-4">
			Pacchetti
		</div>
		<div class="col" data-import="packages"></div>
	</div>

	<div class="py-2 row">
		<div class="col-4">
			Stazioni transfer
		</div>
		<div class="col" data-import="stations"></div>
	</div>

	<div class="py-2 row">
		<div class="col-4">
			Metodi di pagamento
		</div>
		<div class="col" data-import="payment-methods"></div>
	</div>

	<div class="py-2 row">
		<div class="col-4">
			Condizioni di pagamento
		</div>
		<div class="col" data-import="payment-conditions"></div>
	</div>
</div>

<div class="m-3 p-3" style="border: solid #999 1px; box-shadow: 0 0 10px #EEE">
	<div class="pb-3 row">
		<div class="col-4">
			<h2>Trova foto</h2>
		</div>
		<div class="col">
			<form class="row" onsubmit="findTravioPhoto(); return false">
				<div class="col">
					<input type="text" class="form-control" id="travio-find-photo-path" placeholder="/app-data/travio/cache/51/img/servizi/DSC06242-11236.jpg"/>
				</div>
				<div class="col-auto">
					<input type="submit" class="btn btn-primary" id="travio-find-photo-submit" value="Cerca"/>
				</div>
			</form>
		</div>
	</div>

	<div class="py-2 row">
		<div class="col-4">
			Risultato
		</div>
		<div class="col" id="travio-find-photo-result"></div>
	</div>
</div>

<script>
	window.findTravioPhoto = async function () {
		let input = document.getElementById('travio-find-photo-path');
		let button = document.getElementById('travio-find-photo-submit');
		let result = document.getElementById('travio-find-photo-result');
		if (!input || !result)
			return;

		let path = input.value.trim();
		if (!path) {
			result.textContent = '';
			return;
		}

		button.disabled = true;
		result.textContent = 'Ricerca in corso...';

		try {
			let response = await adminApiRequest('page/travio-import/find-photo', {path});
			renderTravioPhotoResults(result, response);
		} catch (err) {
			result.textContent = '';
			reportAdminError(err);
		} finally {
			button.disabled = false;
		}
	};

	window.renderTravioPhotoResults = function (container, response) {
		container.textContent = '';

		if (!response.results || response.results.length === 0) {
			container.textContent = 'Nessun servizio o pacchetto trovato per questo percorso.';
			return;
		}

		for (let item of response.results) {
			let row = document.createElement('div');
			row.className = 'py-1';

			let label = document.createElement('span');
			label.textContent = (item.type === 'service' ? 'Servizio' : 'Pacchetto') + ': ';
			row.appendChild(label);

			let link = document.createElement('a');
			link.href = '#';
			link.textContent = item.name || item.code || ('#' + item.id);
			link.addEventListener('click', event => {
				event.preventDefault();
				loadAdminPage(item.rule + '/edit/' + item.id);
			});
			row.appendChild(link);

			let details = [];
			if (item.via)
				details.push(item.via);
			if (item.code && item.name)
				details.push('codice ' + item.code);
			if (item.travio_id)
				details.push('id Travio ' + item.travio_id);
			details.push('foto #' + item.photo_id + ' (' + item.field + ')');
			if (item.description)
				details.push(item.description);
			if (!item.exact)
				details.push('corrispondenza approssimativa');

			let details_row = document.createElement('small');
			details_row.className = 'd-block text-muted';
			details_row.textContent = details.join(' — ');
			row.appendChild(details_row);

			container.appendChild(row);
		}
	};
</script>
