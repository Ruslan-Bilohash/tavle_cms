document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-brand-select').forEach(function (brandSelect) {
        var modelId = brandSelect.dataset.modelTarget;
        var modelSelect = modelId ? document.getElementById(modelId) : null;
        if (!modelSelect) return;

        var allLabel = modelSelect.dataset.allLabel || 'All';

        brandSelect.addEventListener('change', function () {
            var brandId = this.value;
            modelSelect.innerHTML = '<option value="">' + allLabel + '</option>';
            if (!brandId) return;

            var base = window.BILEN_BASE || '';
            fetch(base + '/api/models.php?brand_id=' + encodeURIComponent(brandId))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    (data.models || []).forEach(function (m) {
                        var opt = document.createElement('option');
                        opt.value = m.id;
                        opt.textContent = m.name;
                        modelSelect.appendChild(opt);
                    });
                })
                .catch(function () {});
        });
    });
});