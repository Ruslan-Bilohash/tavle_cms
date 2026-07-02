window.BILEN_BASE = document.documentElement.dataset.bilenBase || '';

document.addEventListener('DOMContentLoaded', function () {
    function updateStickyOffsets() {
        var topbar = document.querySelector('.im-topbar');
        var header = document.querySelector('.im-header');
        var mobile = window.matchMedia('(max-width: 767px)').matches;
        var topbarH = topbar ? topbar.offsetHeight : 0;
        var headerH = header ? header.offsetHeight : 0;
        var root = document.documentElement;

        root.style.setProperty('--im-header-h', headerH + 'px');
        if (mobile) {
            root.style.setProperty('--im-topbar-h', '0px');
            root.style.setProperty('--im-sticky-nav-h', headerH + 'px');
        } else {
            root.style.setProperty('--im-topbar-h', topbarH + 'px');
            root.style.setProperty('--im-sticky-nav-h', (topbarH + headerH) + 'px');
        }
    }

    updateStickyOffsets();
    window.addEventListener('resize', updateStickyOffsets);
    if (window.ResizeObserver) {
        var stickyNodes = document.querySelectorAll('.im-topbar, .im-header');
        var stickyObserver = new ResizeObserver(updateStickyOffsets);
        stickyNodes.forEach(function (node) { stickyObserver.observe(node); });
    }

    var menuToggle = document.getElementById('menuToggle');
    var imNav = document.getElementById('imNav');
    if (menuToggle && imNav) {
        menuToggle.addEventListener('click', function () {
            var open = imNav.classList.toggle('open');
            menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    var langToggle = document.getElementById('langToggle');
    var langMenu = document.getElementById('langMenu');
    if (langToggle && langMenu) {
        langToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = langMenu.hidden;
            langMenu.hidden = !open;
            langMenu.setAttribute('aria-hidden', open ? 'false' : 'true');
            langToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.addEventListener('click', function () {
            langMenu.hidden = true;
            langMenu.setAttribute('aria-hidden', 'true');
            langToggle.setAttribute('aria-expanded', 'false');
        });
        langMenu.setAttribute('aria-hidden', 'true');
    }

    var catalogNavToggle = document.getElementById('catalogNavToggle');
    var catalogNavMenu = document.getElementById('catalogNavMenu');
    var catalogNavDropdown = document.getElementById('catalogNavDropdown');
    if (catalogNavToggle && catalogNavMenu) {
        catalogNavToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var willOpen = catalogNavMenu.hidden;
            catalogNavMenu.hidden = !willOpen;
            catalogNavToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            if (catalogNavDropdown) catalogNavDropdown.classList.toggle('open', willOpen);
        });
        document.addEventListener('click', function () {
            catalogNavMenu.hidden = true;
            catalogNavToggle.setAttribute('aria-expanded', 'false');
            if (catalogNavDropdown) catalogNavDropdown.classList.remove('open');
        });
        catalogNavMenu.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    var toggleFiltersBtn = document.getElementById('toggleFilters');
    var searchSpoiler = document.getElementById('searchSpoiler');
    var catalogToolbar = document.getElementById('catalogToolbar');
    var toolbarToggle = document.getElementById('toolbarToggle');
    if (catalogToolbar) {
        var collapseThreshold = 48;
        var scrollTicking = false;

        function setToolbarExpanded(expanded) {
            catalogToolbar.classList.toggle('im-toolbar--expanded', expanded);
            if (toolbarToggle) {
                toolbarToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            }
        }

        function updateToolbarCollapse() {
            var y = window.scrollY || window.pageYOffset;
            if (y > collapseThreshold) {
                catalogToolbar.classList.add('im-toolbar--collapsed');
                if (!catalogToolbar.classList.contains('im-toolbar--expanded')) {
                    if (searchSpoiler) searchSpoiler.classList.remove('open');
                    if (filterPanel) {
                        filterPanel.classList.remove('open');
                        if (filterOverlay) filterOverlay.classList.remove('open');
                        document.body.style.overflow = '';
                    }
                    if (toggleFiltersBtn) toggleFiltersBtn.setAttribute('aria-expanded', 'false');
                }
            } else {
                catalogToolbar.classList.remove('im-toolbar--collapsed', 'im-toolbar--expanded');
                if (toolbarToggle) toolbarToggle.setAttribute('aria-expanded', 'false');
            }
            scrollTicking = false;
        }

        window.addEventListener('scroll', function () {
            if (!scrollTicking) {
                scrollTicking = true;
                requestAnimationFrame(updateToolbarCollapse);
            }
        }, { passive: true });

        if (toolbarToggle) {
            toolbarToggle.addEventListener('click', function () {
                var willExpand = !catalogToolbar.classList.contains('im-toolbar--expanded');
                setToolbarExpanded(willExpand);
                if (willExpand) {
                    catalogToolbar.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                }
            });
        }

        updateToolbarCollapse();
    }

    var closeFilters = document.getElementById('closeFilters');
    var filterPanel = document.getElementById('filterPanel');
    var filterOverlay = document.getElementById('filterOverlay');
    var mobileFiltersMq = window.matchMedia('(max-width: 767px)');

    function toggleFilterPanel(open) {
        if (!filterPanel) return;
        filterPanel.classList.toggle('open', open);
        if (filterOverlay) {
            filterOverlay.classList.toggle('open', open);
            filterOverlay.setAttribute('aria-hidden', open ? 'false' : 'true');
        }
        filterPanel.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.style.overflow = open ? 'hidden' : '';
    }

    function toggleSearchSpoiler(open) {
        if (!searchSpoiler) return;
        if (typeof open === 'boolean') {
            searchSpoiler.classList.toggle('open', open);
        } else {
            searchSpoiler.classList.toggle('open');
        }
    }

    function isMobileFilters() {
        return mobileFiltersMq.matches;
    }

    if (toggleFiltersBtn) {
        toggleFiltersBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (catalogToolbar && catalogToolbar.classList.contains('im-toolbar--collapsed')) {
                catalogToolbar.classList.add('im-toolbar--expanded');
            }

            if (isMobileFilters()) {
                var willOpen = !filterPanel || !filterPanel.classList.contains('open');
                toggleSearchSpoiler(false);
                toggleFilterPanel(willOpen);
                toggleFiltersBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            } else if (searchSpoiler) {
                toggleFilterPanel(false);
                toggleSearchSpoiler();
                toggleFiltersBtn.setAttribute('aria-expanded', searchSpoiler.classList.contains('open') ? 'true' : 'false');
            } else if (filterPanel) {
                toggleFilterPanel(!filterPanel.classList.contains('open'));
            }
        });
    }

    if (closeFilters) {
        closeFilters.addEventListener('click', function () {
            toggleFilterPanel(false);
            if (toggleFiltersBtn) toggleFiltersBtn.setAttribute('aria-expanded', 'false');
        });
    }
    if (filterOverlay) {
        filterOverlay.addEventListener('click', function () {
            toggleFilterPanel(false);
            if (toggleFiltersBtn) toggleFiltersBtn.setAttribute('aria-expanded', 'false');
        });
    }

    document.querySelectorAll('.im-gallery-thumbs img').forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            var main = document.getElementById('mainImage');
            if (main) main.src = this.dataset.full || this.src;
            document.querySelectorAll('.im-gallery-thumbs img').forEach(function (t) { t.classList.remove('active'); });
            this.classList.add('active');
        });
    });

    document.querySelectorAll('img[data-fallback]').forEach(function (img) {
        img.addEventListener('error', function () {
            var fallback = img.getAttribute('data-fallback');
            if (fallback && img.src !== fallback) {
                img.src = fallback;
            }
        }, { once: true });
    });

    function hydrateSliderImages(slider) {
        if (slider.dataset.sliderHydrated === '1') return;
        var extraRaw = slider.getAttribute('data-slider-extra');
        if (!extraRaw) return;
        var track = slider.querySelector('.im-slider-track');
        if (!track) return;
        try {
            var extras = JSON.parse(extraRaw);
            extras.forEach(function (src) {
                var img = document.createElement('img');
                img.src = src;
                img.alt = track.querySelector('img') ? track.querySelector('img').alt : '';
                img.width = 400;
                img.height = 300;
                img.loading = 'lazy';
                img.decoding = 'async';
                if (track.querySelector('img')) {
                    img.setAttribute('data-fallback', track.querySelector('img').getAttribute('data-fallback') || '');
                }
                track.appendChild(img);
            });
            slider.dataset.sliderHydrated = '1';
        } catch (err) { /* ignore malformed JSON */ }
    }

    document.querySelectorAll('[data-slider]').forEach(function (slider) {
        var track = slider.querySelector('.im-slider-track');
        if (!track) return;
        var dots = slider.querySelectorAll('.im-slider-dots span');
        var prev = slider.querySelector('.im-slider-prev');
        var next = slider.querySelector('.im-slider-next');
        var hasExtra = slider.hasAttribute('data-slider-extra');
        if (!hasExtra && track.querySelectorAll('img').length < 2) return;

        var index = 0;
        function slides() {
            return track.querySelectorAll('img');
        }
        function show(i) {
            hydrateSliderImages(slider);
            var list = slides();
            if (list.length < 2) return;
            index = (i + list.length) % list.length;
            list.forEach(function (s, n) { s.classList.toggle('active', n === index); });
            dots.forEach(function (d, n) { d.classList.toggle('active', n === index); });
        }

        if (hasExtra) {
            slider.addEventListener('mouseenter', function () { hydrateSliderImages(slider); }, { once: true });
        }

        if (prev) prev.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); show(index - 1); });
        if (next) next.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); show(index + 1); });
        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); show(i); });
        });

        var startX = 0;
        slider.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
        slider.addEventListener('touchend', function (e) {
            var diff = e.changedTouches[0].clientX - startX;
            if (Math.abs(diff) > 40) show(index + (diff < 0 ? 1 : -1));
        });
    });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-reveal]');
        if (!btn || btn.classList.contains('is-revealed')) return;
        e.preventDefault();
        e.stopPropagation();
        var value = (btn.getAttribute('data-value') || '').trim();
        var type = btn.getAttribute('data-reveal') || '';
        var label = btn.querySelector('.im-reveal-label');
        if (!value) {
            btn.classList.add('is-revealed');
            if (label) label.textContent = '—';
            return;
        }
        if (type === 'phone') {
            var link = document.createElement('a');
            link.href = 'tel:' + value.replace(/[^\d+]/g, '');
            link.className = btn.className + ' is-revealed';
            link.setAttribute('data-reveal', 'phone');
            link.innerHTML = '<i class="bi bi-telephone-fill"></i><span class="im-reveal-label">' + value + '</span>';
            btn.replaceWith(link);
            return;
        }
        if (!label) return;
        label.textContent = value;
        btn.classList.add('is-revealed');
        if (type === 'vin' || type === 'plate') {
            label.classList.add('im-reveal-label--mono');
        }
    });

    var consent = document.getElementById('cookieConsent');
    if (consent && !localStorage.getItem('bilen_cookie_consent')) {
        consent.hidden = false;
    }
    var acceptBtn = document.getElementById('cookieAccept');
    var declineBtn = document.getElementById('cookieDecline');
    if (acceptBtn) acceptBtn.addEventListener('click', function () {
        localStorage.setItem('bilen_cookie_consent', 'accepted');
        if (consent) consent.hidden = true;
    });
    if (declineBtn) declineBtn.addEventListener('click', function () {
        localStorage.setItem('bilen_cookie_consent', 'declined');
        if (consent) consent.hidden = true;
    });
});