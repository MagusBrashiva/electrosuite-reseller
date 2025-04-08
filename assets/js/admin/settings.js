jQuery(window).load(function(){

	// Countries
	jQuery('select#electrosuite_reseller_multi_countries').change(function(){
		if (jQuery(this).val()=="specific") {
			jQuery(this).parent().parent().next('tr').show();
		}
		else {
			jQuery(this).parent().parent().next('tr').hide();
		}
	}).change();

	// Color picker
	jQuery('.colorpick').iris( {
		change: function(event, ui){
			jQuery(this).css( { backgroundColor: ui.color.toString() } );
		},
		hide: true,
		border: true
	} ).each( function() {
		jQuery(this).css( { backgroundColor: jQuery(this).val() } );
	})
	.click(function(){
		jQuery('.iris-picker').hide();
		jQuery(this).closest('.color_box, td').find('.iris-picker').show();
	});

	jQuery('body').click(function() {
		jQuery('.iris-picker').hide();
	});

	jQuery('.color_box, .colorpick').click(function(event){
		event.stopPropagation();
	});

	// Edit prompt
	jQuery(function(){
		var changed = false;

		jQuery('input, textarea, select, checkbox').change(function(){
			changed = true;
		});

		jQuery('.nav-tab-wrapper a').click(function(){
			if (changed) {
				window.onbeforeunload = function() {
					return electrosuite_reseller_settings_params.i18n_nav_warning;
				}
			}
			else {
				window.onbeforeunload = '';
			}
		});

		jQuery('.submit input').click(function(){
			window.onbeforeunload = '';
		});
	});

	// Chosen selects
	jQuery("select.chosen_select").chosen({
		width: '350px',
		disable_search_threshold: 5
	});

	jQuery("select.chosen_select_nostd").chosen({
		allow_single_deselect: 'true',
		width: '350px',
		disable_search_threshold: 5
	});


	// --- Add TLD Checkbox Limit Logic (Revised for Grid & noConflict) ---
	const maxTldsAllowed = 30; // Set limit
	// Target checkboxes specifically within the eNom grid container
	const gridContainerSelector = '.checkbox-grid.enom-tld-grid'; // CSS class for eNom grid div
	const tldCheckboxSelector = gridContainerSelector + ' input[type="checkbox"].checkbox-grid-input';
	const limitMessageId = 'enom-tld-limit-message';
	// Note: limitMessageRowId is no longer used in this version

	function updateEnomTldCheckboxState() {
		// Use jQuery instead of $
		const $checkboxes = jQuery(tldCheckboxSelector); // Selector like '.enom-tld-grid input.checkbox-grid-input'

		// If no checkboxes matching the selector exist on the page *at all*, exit early.
		if ($checkboxes.length === 0) {
			// Attempt to hide any stray message placeholders if they exist, though this scenario is less likely now.
			jQuery('.tld-limit-message').hide();
			return;
		}

		// --- Find the specific grid container and limit message relative to it ---
		// Find the closest grid container enclosing the checkboxes. This assumes checkboxes are direct children or grandchildren.
		const $gridContainer = $checkboxes.first().closest('.checkbox-grid'); // Find the relevant grid div

		// If we couldn't find the grid container for some reason, exit.
		if ($gridContainer.length === 0) {
			console.warn("TLD Limit JS: Could not find the parent '.checkbox-grid' container for the checkboxes being changed.");
			return;
		}

		// Find the limit message placeholder specific to this grid's row
		// Go up to the row (tr), find the header cell (th), then find the message placeholder by class within it.
		const $limitMessage = $gridContainer.closest('tr').find('th.titledesc .tld-limit-message');
		// --- End finding logic ---


		// Check if the placeholder element was found relative to this specific grid
		if ($limitMessage.length === 0) {
			// If the placeholder is missing for *this specific grid*, log a warning and exit.
			console.warn("TLD Limit JS: Could not find the message placeholder element (.tld-limit-message) in the <th> relative to the grid container. Check the PHP template structure.");
			console.warn("Debug Info: Grid container found:", $gridContainer.length > 0); // Add debug info
			return;
		}

		// Calculate checked count ONLY for the checkboxes within THIS specific grid container
		const checkedCount = $gridContainer.find('input[type="checkbox"].checkbox-grid-input:checked').length;
		const $allCheckboxesInThisGrid = $gridContainer.find('input[type="checkbox"].checkbox-grid-input'); // Select all checkboxes just within this grid

		// Update message text, styles, and checkbox states based on the found element
		// Apply styles (overrides initial placeholder styles)
		$limitMessage.css({ 'font-weight': 'bold', 'margin': '5px 0 0 0', 'padding': '0', 'font-size': '0.9em' }); // Adjusted styles slightly

		if (checkedCount >= maxTldsAllowed) {
			// Disable UNCHECKED checkboxes ONLY within this grid
			$allCheckboxesInThisGrid.filter(':not(:checked)').prop('disabled', true).closest('label').css('opacity', '0.5');
			// Update limit message
			$limitMessage.text(`Limit reached (${maxTldsAllowed} TLDs selected). Uncheck others to select more.`).css('color', 'red');
		} else {
			// Enable ALL checkboxes ONLY within this grid
			$allCheckboxesInThisGrid.prop('disabled', false).closest('label').css('opacity', '1.0');
			// Update limit message
			$limitMessage.text(`${checkedCount} / ${maxTldsAllowed} TLDs selected.`).css('color', '#666'); // Use a standard text color
		}
		$limitMessage.show(); // Ensure paragraph is visible
	}

	// Use jQuery instead of $ for event delegation
	jQuery(document).on('change', tldCheckboxSelector, updateEnomTldCheckboxState);

	// Initial check shortly after page load
	setTimeout(updateEnomTldCheckboxState, 300);
	// --- End TLD Checkbox Limit Logic ---



	// --- Add TLD Reset Defaults Logic ---
	// Use event delegation for potentially dynamically added grids/links
	jQuery(document).on('click', 'a.reset-tld-defaults', function (e) {
		e.preventDefault(); // Prevent link from navigating

		const $link = jQuery(this);
		// Find the grid container associated with this link
		// Assumes link is in a <p> right after the grid div, both inside a fieldset
		const $gridContainer = $link.closest('fieldset').find('.checkbox-grid');

		if ($gridContainer.length === 0) {
			console.warn("Reset TLDs: Could not find associated grid container.");
			return;
		}

		const defaultsJson = $gridContainer.attr('data-defaults');
		if (!defaultsJson) {
			console.warn("Reset TLDs: Could not find data-defaults attribute on grid container.");
			return;
		}

		let defaultTlds = [];
		try {
			defaultTlds = JSON.parse(defaultsJson);
			if (!Array.isArray(defaultTlds)) {
				throw new Error("Parsed data is not an array.");
			}
		} catch (error) {
			console.error("Reset TLDs: Failed to parse default TLDs JSON.", error);
			return;
		}

		// Find all checkboxes within THIS grid
		const $checkboxesInGrid = $gridContainer.find('input[type="checkbox"].checkbox-grid-input');

		// Uncheck all checkboxes first
		$checkboxesInGrid.prop('checked', false);

		// Check the ones specified in the defaults array
		if (defaultTlds.length > 0) {
			$checkboxesInGrid.each(function () {
				const $checkbox = jQuery(this);
				// Find the label text associated with this checkbox
				// Assumes structure: <div.item><label><input><span.label>TEXT</span></label></div>
				const labelText = $checkbox.siblings('.checkbox-grid-label').text().trim();
				// Remove the prefix (e.g., '.') if it exists, based on PHP item_prefix
				const tld = labelText.startsWith('.') ? labelText.substring(1) : labelText;

				if (defaultTlds.includes(tld)) {
					$checkbox.prop('checked', true);
				}
			});
		}

		// Trigger change event on the first checkbox in the grid to update the counter
		// This relies on updateEnomTldCheckboxState being correctly set up to handle the change event
		$checkboxesInGrid.first().trigger('change');

		// Optional: Provide user feedback
		// $link.text("Defaults Restored!"); // Example temporary feedback
		// setTimeout(() => { $link.text("Reset Default TLDs"); }, 2000);

	});
	// --- End TLD Reset Defaults Logic ---

});