document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('publicListingForm');
    if (!form) return;

    var cfg = window.BILEN_LISTING_WIZARD || {};
    var maxPhotos = cfg.maxPhotos || 20;
    var steps = Array.prototype.slice.call(document.querySelectorAll('.im-wizard-step'));
    var panels = Array.prototype.slice.call(document.querySelectorAll('.im-wizard-panel'));
    var dropzone = document.getElementById('photoDropzone');
    var fileInput = document.getElementById('photoInput');
    var photoGrid = document.getElementById('photoGrid');
    var previewCard = document.getElementById('wizardPreviewCard');
    var previewModal = document.getElementById('previewModal');
    var pendingFiles = [];
    var existingImages = cfg.existingImages || [];

    function currentStep() {
        return parseInt(form.dataset.step || '1', 10);
    }

    function setStep(n) {
        n = Math.max(1, Math.min(panels.length, n));
        form.dataset.step = String(n);
        steps.forEach(function (el, i) {
            el.classList.toggle('is-active', i + 1 === n);
            el.classList.toggle('is-done', i + 1 < n);
        });
        panels.forEach(function (el, i) {
            el.classList.toggle('is-active', i + 1 === n);
        });
        var prevBtn = document.getElementById('wizardPrev');
        var nextBtn = document.getElementById('wizardNext');
        if (prevBtn) prevBtn.disabled = n <= 1;
        if (nextBtn) nextBtn.style.display = n >= panels.length ? 'none' : '';
        updateLivePreview();
    }

    steps.forEach(function (btn, i) {
        btn.addEventListener('click', function () { setStep(i + 1); });
    });
    var prevBtn = document.getElementById('wizardPrev');
    var nextBtn = document.getElementById('wizardNext');
    if (prevBtn) prevBtn.addEventListener('click', function () { setStep(currentStep() - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function () { setStep(currentStep() + 1); });

    function formatPrice(val) {
        var n = parseInt(val, 10) || 0;
        return n > 0 ? ('$' + n.toLocaleString()) : '—';
    }

    function fieldVal(name) {
        var el = form.querySelector('[name="' + name + '"]');
        if (!el) return '';
        return el.value || '';
    }

    function selectedText(name) {
        var el = form.querySelector('[name="' + name + '"]');
        if (!el || el.tagName !== 'SELECT') return '';
        var opt = el.options[el.selectedIndex];
        return opt && opt.value ? opt.textContent.trim() : '';
    }

    function buildTitle() {
        var title = fieldVal('title').trim();
        if (title) return title;
        var brand = selectedText('brand_id');
        var model = selectedText('model_id');
        var year = fieldVal('year');
        return [brand, model, year].filter(Boolean).join(' ') || cfg.draftLabel || 'Listing';
    }

    function allPreviewUrls() {
        var urls = existingImages.map(function (img) { return img.url; });
        pendingFiles.forEach(function (item) { urls.push(item.url); });
        return urls;
    }

    function renderPhotoGrid() {
        if (!photoGrid) return;
        photoGrid.innerHTML = '';
        var mainIndex = 0;

        existingImages.forEach(function (img, idx) {
            var tile = document.createElement('div');
            tile.className = 'im-photo-tile' + (img.is_main ? ' is-main' : '');
            tile.dataset.type = 'existing';
            tile.dataset.id = String(img.id);
            tile.innerHTML =
                (img.is_main ? '<span class="im-photo-badge">' + (cfg.mainLabel || 'Main') + '</span>' : '') +
                '<img src="' + img.url + '" alt="">' +
                '<div class="im-photo-tile-actions">' +
                '<button type="button" data-action="main">' + (cfg.setMainLabel || 'Main') + '</button>' +
                '<button type="button" class="is-danger" data-action="remove">' + (cfg.removeLabel || 'Remove') + '</button>' +
                '</div>';
            photoGrid.appendChild(tile);
            if (img.is_main) mainIndex = idx;
        });

        pendingFiles.forEach(function (item, idx) {
            var globalIdx = existingImages.length + idx;
            var tile = document.createElement('div');
            tile.className = 'im-photo-tile' + (existingImages.length === 0 && idx === 0 ? ' is-main' : '');
            tile.dataset.type = 'pending';
            tile.dataset.index = String(idx);
            tile.innerHTML =
                (existingImages.length === 0 && idx === 0 ? '<span class="im-photo-badge">' + (cfg.mainLabel || 'Main') + '</span>' : '') +
                '<img src="' + item.url + '" alt="">' +
                '<div class="im-photo-tile-actions">' +
                '<button type="button" class="is-danger" data-action="remove">' + (cfg.removeLabel || 'Remove') + '</button>' +
                '</div>';
            photoGrid.appendChild(tile);
            if (existingImages.length === 0 && idx === 0) mainIndex = globalIdx;
        });

        photoGrid.querySelectorAll('[data-action="remove"]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var tile = btn.closest('.im-photo-tile');
                if (!tile) return;
                if (tile.dataset.type === 'pending') {
                    var i = parseInt(tile.dataset.index, 10);
                    if (pendingFiles[i]) {
                        URL.revokeObjectURL(pendingFiles[i].url);
                        pendingFiles.splice(i, 1);
                        syncPendingInput();
                        renderPhotoGrid();
                        updateLivePreview();
                    }
                } else if (tile.dataset.type === 'existing' && cfg.carId) {
                    removeExistingImage(parseInt(tile.dataset.id, 10));
                }
            });
        });

        photoGrid.querySelectorAll('[data-action="main"]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var tile = btn.closest('.im-photo-tile');
                if (tile && tile.dataset.type === 'existing' && cfg.carId) {
                    setExistingMain(parseInt(tile.dataset.id, 10));
                }
            });
        });
    }

    function syncPendingInput() {
        if (!fileInput) return;
        try {
            var dt = new DataTransfer();
            pendingFiles.forEach(function (item) { dt.items.add(item.file); });
            fileInput.files = dt.files;
        } catch (err) {
            /* DataTransfer unsupported — files still submit on first pick */
        }
    }

    function addFiles(fileList) {
        Array.prototype.forEach.call(fileList || [], function (file) {
            if (!file.type || !file.type.startsWith('image/')) return;
            if (existingImages.length + pendingFiles.length >= maxPhotos) return;
            pendingFiles.push({ file: file, url: URL.createObjectURL(file) });
        });
        syncPendingInput();
        renderPhotoGrid();
        updateLivePreview();
    }

    if (dropzone && fileInput) {
        dropzone.addEventListener('click', function () { fileInput.click(); });
        dropzone.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropzone.classList.add('is-dragover');
        });
        dropzone.addEventListener('dragleave', function () {
            dropzone.classList.remove('is-dragover');
        });
        dropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            dropzone.classList.remove('is-dragover');
            addFiles(e.dataTransfer.files);
        });
        fileInput.addEventListener('change', function () {
            addFiles(fileInput.files);
        });
    }

    function updateLivePreview() {
        if (!previewCard) return;
        var urls = allPreviewUrls();
        var media = previewCard.querySelector('.im-wizard-preview-media');
        var titleEl = previewCard.querySelector('.im-wizard-preview-title');
        var priceEl = previewCard.querySelector('.im-wizard-preview-price');
        var metaEl = previewCard.querySelector('.im-wizard-preview-meta');
        if (media) {
            media.innerHTML = urls.length
                ? '<img src="' + urls[0] + '" alt="">'
                : '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#94a3b8;font-size:13px;">' + (cfg.noPhotoLabel || '') + '</div>';
        }
        if (titleEl) titleEl.textContent = buildTitle();
        if (priceEl) priceEl.textContent = formatPrice(fieldVal('price_usd'));
        if (metaEl) {
            var parts = [
                selectedText('brand_id'),
                fieldVal('year'),
                fieldVal('mileage') ? fieldVal('mileage') + ' km' : '',
                fieldVal('city')
            ].filter(Boolean);
            metaEl.textContent = parts.join(' · ');
        }
    }

    form.querySelectorAll('input, select, textarea').forEach(function (el) {
        el.addEventListener('input', updateLivePreview);
        el.addEventListener('change', updateLivePreview);
    });

    function openPreviewModal() {
        if (!previewModal) return;
        var urls = allPreviewUrls();
        var main = previewModal.querySelector('.im-preview-gallery-main img');
        var thumbs = previewModal.querySelector('.im-preview-thumbs');
        var title = previewModal.querySelector('.im-preview-modal-title');
        var price = previewModal.querySelector('.im-preview-modal-price');
        var desc = previewModal.querySelector('.im-preview-modal-desc');
        var specs = previewModal.querySelector('.im-preview-modal-specs');
        if (title) title.textContent = buildTitle();
        if (price) price.textContent = formatPrice(fieldVal('price_usd'));
        if (desc) desc.textContent = fieldVal('description') || '—';
        if (specs) {
            specs.innerHTML = [
                [cfg.labels && cfg.labels.brand, selectedText('brand_id')],
                [cfg.labels && cfg.labels.model, selectedText('model_id')],
                [cfg.labels && cfg.labels.year, fieldVal('year')],
                [cfg.labels && cfg.labels.mileage, fieldVal('mileage')],
                [cfg.labels && cfg.labels.city, fieldVal('city')],
                [cfg.labels && cfg.labels.transmission, selectedText('transmission')],
                [cfg.labels && cfg.labels.fuel, selectedText('fuel_type')]
            ].filter(function (row) { return row[1]; }).map(function (row) {
                return '<div><strong>' + row[0] + ':</strong> ' + row[1] + '</div>';
            }).join('');
        }
        if (main) {
            main.src = urls[0] || '';
            main.style.display = urls[0] ? '' : 'none';
        }
        if (thumbs) {
            thumbs.innerHTML = '';
            urls.forEach(function (url, i) {
                var img = document.createElement('img');
                img.src = url;
                if (i === 0) img.classList.add('is-active');
                img.addEventListener('click', function () {
                    if (main) main.src = url;
                    thumbs.querySelectorAll('img').forEach(function (t) { t.classList.remove('is-active'); });
                    img.classList.add('is-active');
                });
                thumbs.appendChild(img);
            });
        }
        previewModal.classList.add('is-open');
        previewModal.setAttribute('aria-hidden', 'false');
    }

    function closePreviewModal() {
        if (!previewModal) return;
        previewModal.classList.remove('is-open');
        previewModal.setAttribute('aria-hidden', 'true');
    }

    var previewBtn = document.getElementById('previewListingBtn');
    if (previewBtn) previewBtn.addEventListener('click', openPreviewModal);
    previewModal && previewModal.querySelectorAll('[data-close-preview]').forEach(function (btn) {
        btn.addEventListener('click', closePreviewModal);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closePreviewModal();
    });

    function apiHeaders() {
        return {
            'Content-Type': 'application/json',
            'X-CSRF-Token': cfg.csrf || ''
        };
    }

    function removeExistingImage(imageId) {
        fetch((window.BILEN_BASE || '') + '/api/listing-images.php', {
            method: 'DELETE',
            headers: apiHeaders(),
            body: JSON.stringify({ image_id: imageId, [cfg.csrfName || 'bilen_csrf_token']: cfg.csrf })
        }).then(function (r) { return r.json(); }).then(function () {
            existingImages = existingImages.filter(function (img) { return img.id !== imageId; });
            renderPhotoGrid();
            updateLivePreview();
        });
    }

    function setExistingMain(imageId) {
        fetch((window.BILEN_BASE || '') + '/api/listing-images.php', {
            method: 'PATCH',
            headers: apiHeaders(),
            body: JSON.stringify({ image_id: imageId, [cfg.csrfName || 'bilen_csrf_token']: cfg.csrf })
        }).then(function (r) { return r.json(); }).then(function () {
            existingImages.forEach(function (img) { img.is_main = img.id === imageId ? 1 : 0; });
            renderPhotoGrid();
            updateLivePreview();
        });
    }

    renderPhotoGrid();
    setStep(currentStep());
    updateLivePreview();
});