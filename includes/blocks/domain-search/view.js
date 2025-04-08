/******/ (() => { // webpackBootstrap
/*!***********************************!*\
  !*** ./src/domain-search/view.js ***!
  \***********************************/
/**
 * Front-end JavaScript for the Domain Search Block.
 *
 * Handles user input, makes API requests via the WordPress REST API,
 * and displays results.
 */

// Function to run for each domain search block instance on the page
function initializeDomainSearchBlock(blockElement) {
  // Find the necessary elements within this specific block instance
  const input = blockElement.querySelector('.domain-search-input');
  const button = blockElement.querySelector('.domain-search-button');
  const resultsContainer = blockElement.querySelector('.domain-search-results');

  // Exit if essential elements aren't found (safety check)
  if (!input || !button || !resultsContainer) {
    console.error('Domain Search Block: Missing required elements inside:', blockElement);
    return;
  }
  const sldRegex = /^[a-z0-9-]+$/; // Allowed characters for SLD
  // Regex for invalid hyphen use: starts/ends with -, or '--' unless preceded by 'xn--' (basic IDN check)
  const invalidHyphenRegex = /(^-|-$|^.{0,1}-(?!--)|(?<!xn)-{2,})/;
  function validateDomainInput() {
    const domainValue = input.value.trim().toLowerCase();
    let isValid = true;
    let errorMessage = '';

    // Clear previous visual error state from input field
    input.classList.remove('domain-input-error');

    // Find or create an element to display feedback message
    let feedbackElement = blockElement.querySelector('.domain-input-feedback');
    if (!feedbackElement) {
      feedbackElement = document.createElement('p');
      feedbackElement.className = 'domain-input-feedback domain-feedback-message'; // Add general class
      // Insert after the input's wrapper if possible, or before results as fallback
      const wrapper = input.closest('.domain-search-wrapper') || input.parentNode;
      if (wrapper && wrapper.parentNode) {
        wrapper.parentNode.insertBefore(feedbackElement, wrapper.nextSibling);
      } else {
        resultsContainer.parentNode.insertBefore(feedbackElement, resultsContainer);
      }
    }
    feedbackElement.textContent = ''; // Clear previous message
    feedbackElement.className = 'domain-input-feedback domain-feedback-message'; // Reset class

    // Only validate non-empty input
    if (domainValue) {
      // Split input for basic SLD/TLD separation during validation
      const parts = domainValue.split('.');
      const potentialSld = parts.length > 1 ? parts.slice(0, -1).join('.') : domainValue;
      const potentialTld = parts.length > 1 ? parts.pop() : '';

      // --- Apply eNom SLD Rules ---
      if (!sldRegex.test(potentialSld)) {
        isValid = false;
        errorMessage = 'Invalid characters (use only a-z, 0-9, -).';
      } else if (invalidHyphenRegex.test(potentialSld)) {
        isValid = false;
        errorMessage = 'Cannot start/end with hyphen or use invalid "--".';
      } else if (potentialSld.length < 2 || potentialSld.length > 63) {
        isValid = false;
        errorMessage = `Domain name part must be 2-63 characters (currently ${potentialSld.length}).`;
      }
      // --- Optional: Basic TLD check ---
      // else if (potentialTld && potentialTld.length < 2) {
      //    isValid = false;
      //    errorMessage = 'TLD part seems too short.';
      // }

      // Apply visual feedback if invalid
      if (!isValid) {
        input.classList.add('domain-input-error'); // Add error class to input
        feedbackElement.textContent = errorMessage; // Show error message
        feedbackElement.classList.add('domain-feedback-error'); // Style as error
      } else {
        // Optional: Show valid state? Or just clear error.
        // feedbackElement.textContent = 'Format OK';
        // feedbackElement.classList.add('domain-feedback-ok');
      }
    } // End if (domainValue)
  } // End validateDomainInput function

  // --- Add event listener to trigger validation on input ---
  input.addEventListener('input', validateDomainInput);

  // --- Event Listener for the Search Button ---
  button.addEventListener('click', async () => {
    const domain = input.value.trim().toLowerCase();

    // --- Basic Validation ---
    /*
    if (!domain) {
    	resultsContainer.innerHTML = '<p class="domain-search-error">Please enter a domain name.</p>';
    	input.focus(); // Focus back on the input
    	return;
    }
    // Very basic domain format check (doesn't validate TLD)
    if (domain.indexOf('.') === -1 || domain.startsWith('.') || domain.endsWith('.')) {
    	resultsContainer.innerHTML = `<p class="domain-search-error">Please enter a valid domain format (e.g., example.com).</p>`;
    	input.focus();
    	return;
    }
    */

    if (input.classList.contains('domain-input-error')) {
      input.focus(); // Focus on the invalid input
      // Optionally update the main results area too
      // resultsContainer.innerHTML = '<p class="domain-search-error">Please correct the domain format above.</p>';
      return; // Stop processing if input is currently marked invalid
    }

    // --- Show Loading State ---

    // Clear validation message when starting a new search
    let feedbackElement = blockElement.querySelector('.domain-input-feedback');
    if (feedbackElement) feedbackElement.textContent = '';
    resultsContainer.innerHTML = '<p class="domain-search-loading">Checking availability...</p>';
    button.disabled = true; // Prevent multiple clicks
    input.disabled = true; // Optional: disable input during search

    // --- Prepare API Request ---
    const formData = new FormData();
    // 'action' is not typically used/needed for WP REST API calls like this
    // formData.append('action', 'domain_search_check');
    formData.append('domain', domain);
    // Nonce is sent via header, but can be included in body too if needed/preferred by backend logic
    // formData.append('_wpnonce', domainSearchData.nonce);

    try {
      // --- Make the Fetch Request ---
      const response = await fetch(domainSearchData.apiUrl, {
        // Use the API URL from PHP
        method: 'POST',
        headers: {
          // Let browser set Content-Type for FormData
          'X-WP-Nonce': domainSearchData.nonce // Standard header for REST API nonce
        },
        body: formData // Send domain
      });

      // --- Get response as raw text first ---
      const responseText = await response.text();

      // --- Check if response was ok (status 200-299) ---
      if (!response.ok) {
        let errorMessage = `Error: ${response.status} ${response.statusText}`;
        // Try to parse error message from the text, but catch if it's not JSON
        try {
          // Attempt to parse the text we already retrieved
          const errorData = JSON.parse(responseText);
          // Check specifically for a message property common in WP_Error JSON responses
          if (errorData.message) {
            errorMessage = errorData.message; // Use the specific message from backend
          }
        } catch (e) {
          console.warn('Could not parse error response body as JSON:', e);
          // Optionally include raw text in error if short and not HTML
          if (responseText && responseText.length < 100 && !responseText.trim().startsWith('<')) {
            errorMessage += ` - Server response: ${responseText}`;
          }
        }
        // Throw the error to be caught by the outer catch block
        throw new Error(errorMessage);
      }

      // --- If response.ok, try to parse the text as JSON ---
      let data;
      try {
        // Attempt to parse the text we already retrieved
        data = JSON.parse(responseText);
      } catch (error) {
        // If JSON parsing fails even on a 2xx response, log details and throw generic error
        console.error("JSON Parse Error on successful response:", error);
        throw new Error('Received an invalid format from the server.');
      }

      // --- Display Results (Processing Array) ---
      let resultsHTML = '';

      // Check if data is an array and has items
      if (Array.isArray(data) && data.length > 0) {
        resultsHTML = '<ul>'; // Start a list for the results

        data.forEach(item => {
          // Ensure item is an object before accessing properties
          if (item && typeof item === 'object') {
            resultsHTML += '<li>';
            // Display domain name (escape it)
            resultsHTML += `<strong>${escapeHTML(item.domain || '')}</strong>: `;

            // Display status based on item.available
            if (item.available === true) {
              resultsHTML += '<span class="domain-search-success">Available</span>';
              // Display price if available and valid
              if (item.adjusted_price && item.adjusted_price !== 'N/A') {
                // TODO: Add currency symbol from settings?
                resultsHTML += ` - $${escapeHTML(item.adjusted_price)}`;
                // TODO: Add Register button?
              } else if (item.adjusted_price === 'N/A') {
                resultsHTML += ' (Pricing N/A)';
              } else {
                resultsHTML += ' (Pricing unavailable)';
              }
            } else if (item.available === false) {
              resultsHTML += '<span class="domain-search-unavailable">Unavailable</span>';
            } else if (item.available === 'error') {
              // Use the message returned from the backend for this specific error
              resultsHTML += `<span class="domain-search-error">Error: ${escapeHTML(item.message || 'Could not check status.')}</span>`;
            } else {
              // Fallback for truly unexpected 'available' value
              resultsHTML += `<span class="domain-search-error">Unknown status</span>`;
            }
            resultsHTML += '</li>';
          } else {}
        }); // End forEach loop

        resultsHTML += '</ul>'; // Close the list
      } else if (Array.isArray(data) && data.length === 0) {
        // Handle case where backend returned an empty array (e.g., no TLDs checked?)
        resultsHTML = '<p>No results were returned for the requested TLDs.</p>';
      } else {
        // Handle case where 'data' wasn't an array after successful parsing
        resultsHTML = '<p class="domain-search-error">Received an unexpected response format from the server.</p>';
      }
      resultsContainer.innerHTML = resultsHTML; // Update the container with the generated list
    } catch (error) {
      // --- Handle Fetch Errors (Network issues, etc.) or Thrown Errors ---
      console.error('Domain Search Error:', error); // Log the error object
      // Display the specific error message property
      resultsContainer.innerHTML = `<p class="domain-search-error">An error occurred: ${escapeHTML(error.message)}. Please try again later.</p>`;
    } finally {
      // --- Always Re-enable Button/Input ---
      // This block executes regardless of whether try succeeded or failed
      button.disabled = false;
      input.disabled = false;
    }
  }); // End button click listener

  // Optional: Allow search on pressing Enter in the input field
  input.addEventListener('keypress', event => {
    if (event.key === 'Enter') {
      event.preventDefault(); // Prevent form submission if it were inside a form
      button.click(); // Trigger the button click handler
    }
  });
} // End initializeDomainSearchBlock

// --- Helper function to escape HTML to prevent XSS ---
// Simple version; consider a more robust library for complex needs
function escapeHTML(str) {
  if (typeof str !== 'string') {
    console.warn("escapeHTML called with non-string value:", str);
    return ''; // Return empty string for non-strings
  }
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

// --- Initialize all instances of the block on the page ---
// Run after the DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
  const domainSearchBlocks = document.querySelectorAll('.wp-block-create-block-domain-search');
  domainSearchBlocks.forEach(initializeDomainSearchBlock);
});

// --- Handle potential dynamic loading (e.g., in Full Site Editor or with other JS frameworks) ---
// More robust check for blocks added after initial load (optional but good practice)
if (window.MutationObserver) {
  const observer = new MutationObserver(mutationsList => {
    for (const mutation of mutationsList) {
      if (mutation.type === 'childList') {
        mutation.addedNodes.forEach(node => {
          // Check if the added node is the block itself
          if (node.nodeType === 1 && node.matches('.wp-block-create-block-domain-search')) {
            // Check if it's already initialized (e.g., by DOMContentLoaded)
            if (!node.dataset.domainSearchInitialized) {
              initializeDomainSearchBlock(node);
              node.dataset.domainSearchInitialized = 'true'; // Mark as initialized
            }
          }
          // Check if the added node contains the block(s)
          else if (node.nodeType === 1 && node.querySelector) {
            const blocksInside = node.querySelectorAll('.wp-block-create-block-domain-search:not([data-domain-search-initialized])');
            blocksInside.forEach(block => {
              initializeDomainSearchBlock(block);
              block.dataset.domainSearchInitialized = 'true'; // Mark as initialized
            });
          }
        });
      }
    }
  });
  observer.observe(document.body, {
    childList: true,
    subtree: true
  });
}
/******/ })()
;
//# sourceMappingURL=view.js.map