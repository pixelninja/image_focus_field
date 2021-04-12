window.onload = (event) => {
	const fields = document.querySelectorAll('.field-imagefocusfield');

	// Iterate over each Focus field
	fields.forEach(field => {
		// Get the Media Library field ID
		const image_field_id = field.querySelector(':scope [data-field-id]').dataset.fieldId;
		// Get the Media Library element
		const image_field = document.getElementById('field-' + image_field_id);

		// No Media Library field has been linked to the focus field, so stop going any further
		if (!image_field) {
			field.querySelector('.image_focus_wrapper').innerHTML = '<p><i>No Media Library Field has been linked to this Image Focus Field.</i></p>';
			return false;
		}

		// Get the image path
		const image_path = image_field.querySelector(':scope input[name$="[0][value]"]').value || false;

		// No file has been saved yet so display a message instead
		if (!image_path) {
			field.querySelector('.image_focus_wrapper').innerHTML = '<p><i>An image must be uploaded and saved before selecting the focal point.</i></p>';
		}
		// The file attached is not an image
		else if (image_path && !/(jpg|gif|png|jpeg)$/i.test(image_path)) {
			field.querySelector('.image_focus_wrapper').innerHTML = '<p><i>The file selected is not an image. Please attach a JPG, PNG or GIF.</i></p>';
		}
		// We have an image set, so display the visualiser
		else if (image_path) {
			// Absolute path of the image
			const full_image_path = Symphony.Context.get('root') + image_path;

			// Image can't be found or loaded, so don't continue
			if (!imageExists(full_image_path)) {
				field.querySelector('.image_focus_wrapper').innerHTML = '<p><i>The image can\'t be found. Check it exists or check folder permissions.</i></p>';
				return false
			}

			// The HTML to add.
			let html = `
				<div class="image_focus">
					<span class="crosshair"></span>
					<img src="${full_image_path}" />
				</div>
				<div class="image_focus_visualiser">
					<div class="one">
						<img src="${full_image_path}">
					</div>
					<div class="two">
						<img src="${full_image_path}">
					</div>
					<div class="three">
						<img src="${full_image_path}">
					</div>
					<!-- Needed to ensure both parent divs are the same height in Primary column -->
					<div>
						<img src="${full_image_path}">
					</div>
				</div>
			`;
			// Add the HTML
			field.querySelector('.image_focus_wrapper').innerHTML = html;
			// Target the crosshair
			let crosshair = field.querySelector('.crosshair');
			// On click of the primary image, update the input value and the visualiser
			field.querySelector('.image_focus img').onclick = function(e) {
				// Store the body bounds to offset the top
				const body_bounds = document.body.getBoundingClientRect();
				// Image offsets
				const image_bounds = this.getBoundingClientRect();
				// Calculate both pixel and percentage coordinates
				const offset_x_px = e.pageX - image_bounds.left;
				const offset_y_px = e.pageY - image_bounds.top + body_bounds.top; // Make sure we include any scrolled offset
				const offset_x_pc = ((offset_x_px / this.width) * 100).toFixed(2);
				const offset_y_pc = ((offset_y_px / this.height) * 100).toFixed(2);
				// Move the crosshair into place
				crosshair.style.left = offset_x_px + 'px';
				crosshair.style.top = offset_y_px + 'px';
				// Update the hidden input value for saving
				field.querySelector('input[type="hidden"]').value = offset_x_pc + ',' + offset_y_pc;
				// Update each visualiser image position
				field.querySelectorAll('.image_focus_visualiser img').forEach(el => {
					el.style.objectPosition = `${offset_x_pc}% ${offset_y_pc}%`;
				});
				return false;
			};

			// Get the coordinates on load
			const coords = field.querySelector('input[type="hidden"]').value.split(',');
			// If some have already been set
			if(coords.length) {
				const image = field.querySelector('.image_focus img');
				const x = image.width * (coords[0] / 100);
				const y = image.height * (coords[1] / 100);
				// Update the crosshair
				crosshair.style.left = `${x}px`;
				crosshair.style.top = `${y}px`;
				// Update each visualiser image position
				field.querySelectorAll('.image_focus_visualiser img').forEach(el => {
					el.style.objectPosition = `${coords[0]}% ${coords[1]}%`;
				});
			}
		}
	});

	function imageExists(img){
		var http = new XMLHttpRequest();

		http.open('HEAD', img, false);
		http.send();

		return http.status != 404;
	}
};