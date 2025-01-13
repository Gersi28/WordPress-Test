document.addEventListener('DOMContentLoaded', () => {
  const filterButtons = document.querySelectorAll('#taxonomy-filter button');
  const postsContainer = document.querySelector('#posts-container');
  const loadMoreButton = document.querySelector('#load-more');
  let currentRequest = null;
  let currentPage = 1;
  let currentTermId = 6;
  let isLoading = false;

  const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  };

  const filterPosts = async (termId, page = 1, append = false) => {
    if (isLoading) return;
    isLoading = true;

    /**
     * If there already is a request active, cancel it.
     */
    if (currentRequest) {
      currentRequest.abort();
    }

    /**
     * Switching the active item only if the action isn't a 'load more' click.
     */
    if (!append) {
      filterButtons.forEach(button => button.classList.remove('active'));
      const activeButton = document.querySelector(`#taxonomy-filter button[data-term-id="${termId}"]`);
      if (activeButton) {
        activeButton.classList.add('active');
      }
    }

    try {
      /**
       * Loading on the load more button and the content.
       */
      if (!append) {
        postsContainer.innerHTML = '<p>Loading...</p>';
        loadMoreButton.style.display = 'none';
      } else {
        loadMoreButton.textContent = 'Loading...';
        loadMoreButton.disabled = true;
      }

      const params = new URLSearchParams();
      params.append('action', 'filter_posts');
      params.append('term_id', termId);
      params.append('page', page);
      params.append('_ajax_nonce', wpAjax.nonce);

      const response = await fetch(wpAjax.ajaxurl, {
        method: 'POST', credentials: 'same-origin', headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        }, body: params.toString(),
      });

      const data = await response.json();
      if (response.ok && data.success) {
        if (append) {
          postsContainer.insertAdjacentHTML('beforeend', data.data.html);
        } else {
          postsContainer.innerHTML = data.data.html;
        }

        loadMoreButton.style.display = data.data.hasMore ? 'block' : 'none';
        loadMoreButton.textContent = 'Load More';
        loadMoreButton.disabled = false;

        /**
         * Updating the current page state.
         *
         * @type {number}
         */
        currentPage = page;
        if (!append) {
          currentTermId = termId;
        }
      } else {
        if (!append) {
          postsContainer.innerHTML = '<p class="no-results">No benefits found.</p>';
        }
        loadMoreButton.style.display = 'none';      }
    } catch (error) {
      /**
       * In case of aborted request, do nothing.
       */
      if (error.name === 'AbortError') {
        return;
      }

      console.error('Error:', error);
      if (!append) {
        postsContainer.innerHTML = '<p class="error-message">There was an error processing your request.</p>';
      }

      loadMoreButton.style.display = 'none';
    } finally {
      currentRequest = null;
      isLoading = false;
    }
  };

  /**
   * Debouncing the function in case of server delay and the content served is the wrong one.
   * In our case this does nothing but in bigger applications this helps not to send unneeded requests
   * to the server.
   */
  const debouncedFilter = debounce(filterPosts, 300);
  filterButtons.forEach(button => {
    button.addEventListener('click', (e) => {
      const termId = e.currentTarget.dataset.termId;
      currentPage = 1;
      debouncedFilter(termId, currentPage, false);
    });
  });

  /**
   * There is no need to debounce on a 'load more' action.
   */
  loadMoreButton.addEventListener('click', () => {
    filterPosts(currentTermId, currentPage + 1, true);
  });

  filterPosts(6, 1, false);
});
