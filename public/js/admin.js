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
})();
