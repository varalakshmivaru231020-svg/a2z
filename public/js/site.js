/* AKS Global Maintenance — public site behaviour (no dependencies) */
(function () {
    'use strict';

    // ---- Mobile navigation ------------------------------------------------
    var toggle = document.querySelector('[data-nav-toggle]');
    var nav = document.getElementById('primary-nav');

    function setNav(open) {
        if (!toggle || !nav) return;
        nav.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
    }

    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            setNav(toggle.getAttribute('aria-expanded') !== 'true');
        });
        nav.addEventListener('click', function (e) {
            if (e.target.closest('a')) setNav(false);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') setNav(false);
        });
    }

    // ---- Forms: block double submits, bring feedback into view ------------
    document.querySelectorAll('[data-form]').forEach(function (form) {
        var button = form.querySelector('[data-submit]');

        form.addEventListener('submit', function () {
            if (!button) return;
            button.dataset.label = button.textContent;
            button.disabled = true;
            button.textContent = 'Sending…';
        });

        // Coming back via the browser's back button restores the page as it was.
        window.addEventListener('pageshow', function (e) {
            if (e.persisted && button && button.dataset.label) {
                button.disabled = false;
                button.textContent = button.dataset.label;
            }
        });

        var feedback = form.querySelector('.alert--success, .alert--error, .has-error');
        if (feedback) {
            feedback.scrollIntoView({ block: 'center' });
        }
    });

    // ---- Contact page: switch the map between offices ---------------------
    var map = document.getElementById('office-map');
    var mapLink = document.getElementById('office-map-link');

    document.querySelectorAll('[data-office]').forEach(function (office) {
        var button = office.querySelector('[data-show-map]');
        if (!button || !map) return;

        button.addEventListener('click', function () {
            var query = encodeURIComponent(office.dataset.mapQuery);
            map.src = 'https://www.google.com/maps?q=' + query + '&output=embed';
            if (mapLink) mapLink.href = 'https://www.google.com/maps/search/?api=1&query=' + query;

            document.querySelectorAll('[data-office]').forEach(function (o) {
                o.classList.toggle('is-active', o === office);
            });
            map.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });

    // ---- Services page: category tabs filter the list without a reload -----
    // The tabs are real links (/services?category=facility), so this is only an enhancement.
    var tabs = document.querySelectorAll('[data-filter]');
    if (tabs.length) {
        var groups = document.querySelectorAll('[data-category]');
        var status = document.querySelector('[data-filter-status]');

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function (e) {
                if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return; // let "open in new tab" work

                e.preventDefault();
                var key = tab.dataset.filter; // '' means "All"

                groups.forEach(function (group) {
                    group.hidden = key !== '' && group.dataset.category !== key;
                });

                tabs.forEach(function (t) {
                    t.classList.toggle('is-active', t === tab);
                    if (t === tab) t.setAttribute('aria-current', 'true');
                    else t.removeAttribute('aria-current');
                });

                // Keep the address bar shareable (?category=facility) without adding history entries.
                var url = new URL(window.location.href);
                if (key) url.searchParams.set('category', key);
                else url.searchParams.delete('category');
                url.hash = '';
                window.history.replaceState(null, '', url);

                if (status) {
                    var label = tab.firstChild.textContent.trim();
                    var count = tab.querySelector('span').textContent.trim();
                    status.textContent = 'Showing ' + count + ' services' + (key ? ' in ' + label : '');
                }
            });
        });
    }

    // ---- Gallery lightbox --------------------------------------------------
    var links = Array.prototype.slice.call(document.querySelectorAll('[data-lightbox]'));
    if (!links.length) return;

    var box = document.createElement('div');
    box.className = 'lightbox';
    box.setAttribute('role', 'dialog');
    box.setAttribute('aria-modal', 'true');
    box.setAttribute('aria-label', 'Photo viewer');
    box.innerHTML =
        '<button type="button" class="lightbox__close" aria-label="Close">&#10005;</button>' +
        '<button type="button" class="lightbox__prev" aria-label="Previous photo">&#10140;</button>' +
        '<img class="lightbox__img" alt="">' +
        '<p class="lightbox__caption"></p>' +
        '<button type="button" class="lightbox__next" aria-label="Next photo">&#10140;</button>';
    document.body.appendChild(box);

    var img = box.querySelector('.lightbox__img');
    var caption = box.querySelector('.lightbox__caption');
    var index = 0;
    var opener = null;

    function show(i) {
        index = (i + links.length) % links.length;
        var link = links[index];
        var thumb = link.querySelector('img');
        img.src = link.href;
        img.alt = thumb ? thumb.alt : '';
        caption.textContent = link.dataset.caption || '';
    }

    function open(i, trigger) {
        opener = trigger;
        show(i);
        box.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        box.querySelector('.lightbox__close').focus();
    }

    function close() {
        box.classList.remove('is-open');
        document.body.style.overflow = '';
        img.removeAttribute('src');
        if (opener) opener.focus();
    }

    links.forEach(function (link, i) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            open(i, link);
        });
    });

    box.addEventListener('click', function (e) {
        if (e.target === box || e.target.closest('.lightbox__close')) close();
        else if (e.target.closest('.lightbox__prev')) show(index - 1);
        else if (e.target.closest('.lightbox__next')) show(index + 1);
    });

    document.addEventListener('keydown', function (e) {
        if (!box.classList.contains('is-open')) return;
        if (e.key === 'Escape') close();
        else if (e.key === 'ArrowLeft') show(index - 1);
        else if (e.key === 'ArrowRight') show(index + 1);
    });
})();
