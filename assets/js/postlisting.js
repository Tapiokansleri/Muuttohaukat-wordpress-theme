/**
 * Haukka theme — PostListing vanilla JS.
 * Progressive enhancement for PostListing blocks: AJAX pagination and taxonomy filtering.
 */
(function () {
  'use strict';

  var translations = (window.wptheme && window.wptheme.translations) || {};

  /**
   * Get the base pathname, stripping /page/N/ from the URL.
   */
  function getBasepathname() {
    var pathname = window.location.pathname;
    var parts = pathname.replace(/\/$/, '').split('/');

    if (pathname.indexOf('/page/') !== -1) {
      // Remove /page/N from the end
      parts.pop(); // N
      parts.pop(); // "page"
    }

    return parts.join('/');
  }

  /**
   * Remove all children from a DOM element.
   */
  function removeAllChildren(el) {
    while (el && el.firstChild) {
      el.removeChild(el.firstChild);
    }
  }

  /**
   * Fetch new posts from the REST endpoint and update the list HTML.
   */
  function fetchPosts(list, query, template, signal) {
    var url = '/wp-json/muuttohaukat/v1/postlisting/query?template=' +
      encodeURIComponent(template) +
      '&args=' + encodeURIComponent(JSON.stringify(query));

    return fetch(url, { signal: signal })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (!signal || !signal.aborted) {
          list.innerHTML = json.html;
        }
        return json;
      });
  }

  /**
   * Initialize a single PostListing block.
   */
  function initPostListing(listing) {
    var filterEl = listing.querySelector('.mh-postlisting__filters');
    var listEl = listing.querySelector('.mh-postlisting__list');
    var paginationEl = listing.querySelector('.mh-postlisting__pagination');

    if (!listEl) return;

    // Parse initial data from data attributes
    var query = JSON.parse(listEl.getAttribute('data-query') || '{}');
    var template = listEl.getAttribute('data-template') || 'Card';
    var trackStateInUrl = listEl.getAttribute('data-trackstateinurl') === 'true';
    var totalPages = paginationEl ? parseInt(paginationEl.getAttribute('data-total') || '0', 10) : 0;
    var basepathname = getBasepathname();

    // Taxonomy terms state
    var taxonomyTerms = null;
    if (filterEl && filterEl.getAttribute('data-taxterms')) {
      taxonomyTerms = JSON.parse(filterEl.getAttribute('data-taxterms'));
      taxonomyTerms.forEach(function (t) { t.active = false; });
    }

    var abortController = null;

    // Clean up data attributes
    if (filterEl) filterEl.removeAttribute('data-taxterms');
    if (paginationEl) paginationEl.removeAttribute('data-total');
    listEl.removeAttribute('data-query');

    /**
     * Read URL params and restore filter state on page load.
     */
    function restoreFromUrl() {
      if (!trackStateInUrl || !taxonomyTerms) return;

      try {
        var urlParams = new URL(window.location.href).searchParams;
        var argsStr = urlParams.get('args');
        if (!argsStr) return;

        var args = JSON.parse(argsStr);
        if (!args || !args.taxonomies) return;

        var taxonomies = args.taxonomies;
        Object.keys(taxonomies).forEach(function (taxonomy) {
          taxonomies[taxonomy].forEach(function (termId) {
            var term = taxonomyTerms.find(function (t) {
              return t.term_id === termId && t.taxonomy === taxonomy;
            });
            if (term) {
              activateTerm(term);
            }
          });
        });

        updateList();
      } catch (e) {
        // Ignore invalid URL params
      }
    }

    /**
     * Activate a taxonomy term and update the query.
     */
    function activateTerm(term) {
      term.active = true;
      var tax = term.taxonomy;
      var id = term.term_id;

      if (tax === 'post_tag') {
        if (!Array.isArray(query.tag__in)) query.tag__in = [];
        if (query.tag__in.indexOf(id) === -1) query.tag__in.push(id);
      } else if (tax === 'category') {
        if (!Array.isArray(query.category__in)) query.category__in = [];
        if (query.category__in.indexOf(id) === -1) query.category__in.push(id);
      }
      query.paged = 0;
    }

    /**
     * Deactivate a taxonomy term and update the query.
     */
    function deactivateTerm(term) {
      term.active = false;
      var tax = term.taxonomy;
      var id = term.term_id;

      if (tax === 'post_tag' && Array.isArray(query.tag__in)) {
        var idx = query.tag__in.indexOf(id);
        if (idx > -1) query.tag__in.splice(idx, 1);
      } else if (tax === 'category' && Array.isArray(query.category__in)) {
        var idx = query.category__in.indexOf(id);
        if (idx > -1) query.category__in.splice(idx, 1);
      }
      query.paged = 0;
    }

    /**
     * Update the URL to reflect the current state.
     */
    function updateUrl() {
      if (!trackStateInUrl) return;

      var activeTerms = taxonomyTerms ? taxonomyTerms.filter(function (t) { return t.active; }) : [];
      var taxonomies = {};

      activeTerms.forEach(function (term) {
        if (!taxonomies[term.taxonomy]) taxonomies[term.taxonomy] = [];
        taxonomies[term.taxonomy].push(term.term_id);
      });

      var hasFilters = Object.keys(taxonomies).length > 0;
      var url = basepathname +
        (query.paged > 0 ? '/page/' + query.paged + '/' : '/') +
        (hasFilters ? '?args=' + encodeURIComponent(JSON.stringify({ taxonomies: taxonomies })) : '');

      window.history.pushState(null, '', url);
    }

    /**
     * Fetch new content and update the list.
     */
    function updateList() {
      if (abortController) {
        abortController.abort();
      }
      abortController = new AbortController();

      fetchPosts(listEl, query, template, abortController.signal)
        .then(function (json) {
          totalPages = json.pages;
          renderPagination();
          listing.scrollIntoView({ block: 'start', behavior: 'smooth' });
          abortController = null;
        })
        .catch(function (e) {
          if (e.name === 'AbortError') return;

          var errorEl = document.createElement('h2');
          if (e.name === 'TypeError') {
            errorEl.textContent = translations['PostListFetchError'] || 'Something went wrong! Try again in a moment.';
          } else if (e.name === 'SyntaxError') {
            errorEl.textContent = translations['PostListJsonError'] || 'Something is wrong. Try again later.';
          } else {
            errorEl.textContent = e.message;
          }
          removeAllChildren(listEl);
          listEl.appendChild(errorEl);
          abortController = null;
        });

      updateUrl();
    }

    /**
     * Render taxonomy filter buttons.
     */
    function renderFilters() {
      if (!filterEl || !taxonomyTerms) return;

      removeAllChildren(filterEl);

      // Group terms by taxonomy
      var grouped = {};
      taxonomyTerms.forEach(function (term) {
        if (!grouped[term.taxonomy]) grouped[term.taxonomy] = [];
        grouped[term.taxonomy].push(term);
      });

      Object.keys(grouped).forEach(function (taxName) {
        var wrapper = document.createElement('div');

        var heading = document.createElement('h5');
        var strong = document.createElement('strong');
        strong.textContent = translations[taxName] || taxName;
        heading.appendChild(strong);
        wrapper.appendChild(heading);

        grouped[taxName].forEach(function (term) {
          var btn = document.createElement('a');
          btn.setAttribute('role', 'button');
          btn.setAttribute('data-id', term.term_id);
          btn.setAttribute('data-taxonomy', term.taxonomy);
          btn.className = 'mh-button term ' + (term.active ? 'active' : 'inactive');
          btn.textContent = term.name;

          btn.addEventListener('click', function (e) {
            e.preventDefault();
            if (term.active) {
              deactivateTerm(term);
            } else {
              activateTerm(term);
            }
            renderFilters();
            updateList();
          });

          wrapper.appendChild(btn);
        });

        filterEl.appendChild(wrapper);
      });
    }

    /**
     * Build the list of page numbers to show (condensed when there are many pages).
     */
    function getPageRange(current, total) {
      if (total <= 7) {
        var all = [];
        for (var i = 1; i <= total; i++) {
          all.push(i);
        }
        return all;
      }

      var pages = [1];

      if (current > 3) {
        pages.push('…');
      }

      var start = Math.max(2, current - 1);
      var end = Math.min(total - 1, current + 1);

      for (var n = start; n <= end; n++) {
        pages.push(n);
      }

      if (current < total - 2) {
        pages.push('…');
      }

      pages.push(total);
      return pages;
    }

    /**
     * Render pagination buttons.
     */
    function renderPagination() {
      if (!paginationEl) return;

      removeAllChildren(paginationEl);

      if (totalPages <= 1) return;

      var currentPage = query.paged === 0 ? 1 : query.paged;

      var container = document.createElement('div');
      container.className = 'btn-group justify-center flex-row flex-wrap gap-2 py-4';
      container.setAttribute('role', 'navigation');
      container.setAttribute('aria-label', translations['Pagination'] || 'Pagination');

      function goToPage(pageNum, e) {
        if (e) e.preventDefault();
        query.paged = pageNum;
        updateList();
      }

      if (currentPage > 1) {
        var prev = document.createElement('a');
        prev.href = basepathname + '/page/' + (currentPage - 1) + '/';
        prev.className = 'pagination-button btn pagination-button--nav';
        prev.setAttribute('aria-label', translations['Pagination: Previous'] || 'Previous');
        prev.innerHTML = '<span class="pagination-button__label pagination-button__label--full">' +
          (translations['Pagination: Previous'] || 'Previous') + '</span>' +
          '<span class="pagination-button__label pagination-button__label--compact" aria-hidden="true">&lsaquo;</span>';
        prev.addEventListener('click', function (e) {
          goToPage(currentPage - 1, e);
        });
        container.appendChild(prev);
      }

      getPageRange(currentPage, totalPages).forEach(function (page) {
        if (page === '…') {
          var ellipsis = document.createElement('span');
          ellipsis.className = 'pagination-ellipsis';
          ellipsis.textContent = '…';
          ellipsis.setAttribute('aria-hidden', 'true');
          container.appendChild(ellipsis);
          return;
        }

        var btn = document.createElement('a');
        btn.href = basepathname + '/page/' + page + '/';
        btn.className = 'pagination-button btn number' + (page === currentPage ? ' btn-active' : '');
        btn.textContent = page;
        if (page === currentPage) {
          btn.setAttribute('aria-current', 'page');
        }
        btn.addEventListener('click', function (e) {
          goToPage(page, e);
        });
        container.appendChild(btn);
      });

      if (currentPage < totalPages) {
        var next = document.createElement('a');
        next.href = basepathname + '/page/' + (currentPage + 1) + '/';
        next.className = 'pagination-button btn pagination-button--nav';
        next.setAttribute('aria-label', translations['Pagination: Next'] || 'Next');
        next.innerHTML = '<span class="pagination-button__label pagination-button__label--full">' +
          (translations['Pagination: Next'] || 'Next') + '</span>' +
          '<span class="pagination-button__label pagination-button__label--compact" aria-hidden="true">&rsaquo;</span>';
        next.addEventListener('click', function (e) {
          goToPage(currentPage + 1, e);
        });
        container.appendChild(next);
      }

      paginationEl.appendChild(container);
    }

    // Initialize
    renderFilters();
    renderPagination();
    restoreFromUrl();
  }

  // -------------------------------------------------------------------
  // Init all PostListing blocks on the page
  // -------------------------------------------------------------------
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.mh-postlisting').forEach(initPostListing);
  });
})();
