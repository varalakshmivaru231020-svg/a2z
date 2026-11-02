/* Admin panel behaviour (no dependencies) */
(function () {
    'use strict';

    // Confirm before destructive actions: <form data-confirm="Delete this?">
    document.addEventListener('submit', function (e) {
        var message = e.target.getAttribute('data-confirm');
        if (message && !window.confirm(message)) {
            e.preventDefault();
        }
    });

    // Live character counters for SEO fields: <input data-counter="60">
    document.querySelectorAll('[data-counter]').forEach(function (input) {
        var limit = parseInt(input.dataset.counter, 10);
        var out = document.querySelector('[data-counter-for="' + input.id + '"]');
        if (!out) return;

        function update() {
            var n = input.value.length;
            out.textContent = n + ' / ' + limit + ' characters' + (n > limit ? ' — may be cut off in search results' : '');
            out.classList.toggle('is-over', n > limit);
        }
        input.addEventListener('input', update);
        update();
    });

    // Mobile sidebar
    var toggle = document.querySelector('[data-sidebar-toggle]');
    var sidebar = document.getElementById('sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            var open = sidebar.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', String(open));
        });
    }

    // Upload preview count for the gallery uploader
    var picker = document.querySelector('[data-file-count]');
    if (picker) {
        var label = document.querySelector('[data-file-count-label]');
        picker.addEventListener('change', function () {
            if (label) label.textContent = picker.files.length ? picker.files.length + ' photo(s) selected' : '';
        });
    }

    // Dynamic service category filtering
    var filters = document.querySelector('[data-service-filters]');
    var container = document.querySelector('[data-services-container]');
    if (filters && container) {
        filters.addEventListener('click', function (e) {
            var link = e.target.closest('a[data-category]');
            if (!link) return;

            e.preventDefault();
            var category = link.getAttribute('data-category');
            var url = window.location.pathname + (category ? '?category=' + encodeURIComponent(category) : '');

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var newContent = doc.querySelector('[data-services-container]');
                if (newContent) container.innerHTML = newContent.innerHTML;

                // Update active button styles
                filters.querySelectorAll('a[data-category]').forEach(function (btn) {
                    var isActive = btn.getAttribute('data-category') === category;
                    if (isActive) {
                        btn.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
                        btn.classList.remove('bg-white', 'text-gray-700', 'border-gray-300');
                    } else {
                        btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
                        btn.classList.add('bg-white', 'text-gray-700', 'border-gray-300');
                    }
                });

                // Update browser history
                window.history.pushState({}, '', url);
            });
        });
    }
})();
