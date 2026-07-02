    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function () {
        var btn = document.getElementById('adminMenuBtn');
        var sidebar = document.getElementById('adminSidebar');
        var overlay = document.getElementById('adminOverlay');
        function toggle(open) {
            if (!sidebar) return;
            sidebar.classList.toggle('show', open);
            if (overlay) overlay.classList.toggle('show', open);
        }
        if (btn) btn.addEventListener('click', function () { toggle(!sidebar.classList.contains('show')); });
        if (overlay) overlay.addEventListener('click', function () { toggle(false); });
    })();
    </script>
</body>
</html>