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

  // --- Event Listener for the Search Button ---
  button.addEventListener('click', async () => {
    const domain = input.value.trim().toLowerCase();

    // --- Basic Validation ---
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

    // --- Show Loading State ---
    resultsContainer.innerHTML = '<p class="domain-search-loading">Checking availability...</p>';
    button.disabled = true; // Prevent multiple clicks
    input.disabled = true; // Optional: disable input during search

    // --- Prepare API Request ---
    const formData = new FormData();
    formData.append('action', 'domain_search_check'); // Not strictly needed for REST, but good practice if using admin-ajax fallback later
    formData.append('domain', domain);
    formData.append('_wpnonce', domainSearchData.nonce); // Use the nonce passed from PHP

    try {
      // --- Make the Fetch Request ---
      const response = await fetch(domainSearchData.apiUrl, {
        // Use the API URL from PHP
        method: 'POST',
        headers: {
          // Content-Type is not needed for FormData with fetch; browser sets it with boundary
          // 'Content-Type': 'application/x-www-form-urlencoded', // Use if sending data differently
          'X-WP-Nonce': domainSearchData.nonce // Standard header for REST API nonce
        },
        body: formData // Send domain and nonce
      });

      // --- Handle Non-OK HTTP Responses (e.g., 403 Forbidden, 404 Not Found, 500 Server Error) ---
      if (!response.ok) {
        let errorMessage = `Error: ${response.status} ${response.statusText}`;
        try {
          // Try to get more specific error message from the API response body
          const errorData = await response.json();
          if (errorData.message) {
            errorMessage = errorData.message;
          }
        } catch (e) {
          // Ignore if response body is not JSON or empty
        }
        throw new Error(errorMessage); // Trigger the catch block
      }

      // --- Process Successful Response ---
      const data = await response.json(); // Parse the JSON body

      // --- Display Results ---
      // This part depends heavily on the structure of the 'data' object
      // returned by your handle_electrosuite_domain_search_request PHP function
      let resultsHTML = '';
      if (data.available === true) {
        resultsHTML = `<p class="domain-search-success">Congratulations! <strong>${escapeHTML(data.domain)}</strong> is available!</p>`;
        // TODO: Add purchase button/link if needed
      } else if (data.available === false) {
        resultsHTML = `<p class="domain-search-unavailable">Sorry, <strong>${escapeHTML(data.domain)}</strong> is not available.</p>`;
        // TODO: Display suggestions if available in 'data.suggestions'
        if (data.suggestions && data.suggestions.length > 0) {
          resultsHTML += '<h4>Suggestions:</h4><ul>';
          data.suggestions.forEach(suggestion => {
            resultsHTML += `<li>${escapeHTML(suggestion)}</li>`; // Make sure suggestions are safe too
          });
          resultsHTML += '</ul>';
        }
      } else {
        // Handle cases where availability is unknown or the API response was unexpected
        resultsHTML = `<p class="domain-search-error">Could not determine availability for <strong>${escapeHTML(data.domain)}</strong>. Please try again.</p>`;
        console.warn('Unexpected API response structure:', data);
      }
      resultsContainer.innerHTML = resultsHTML;
    } catch (error) {
      // --- Handle Fetch Errors (Network issues, etc.) or Thrown Errors ---
      console.error('Domain Search Error:', error);
      resultsContainer.innerHTML = `<p class="domain-search-error">An error occurred: ${escapeHTML(error.message)}. Please try again later.</p>`;
    } finally {
      // --- Always Re-enable Button/Input ---
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
            initializeDomainSearchBlock(node);
          }
          // Check if the added node contains the block(s)
          else if (node.nodeType === 1 && node.querySelector) {
            const blocksInside = node.querySelectorAll('.wp-block-create-block-domain-search');
            blocksInside.forEach(initializeDomainSearchBlock);
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