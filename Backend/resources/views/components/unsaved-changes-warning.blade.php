<script>
(function() {
    const form = document.querySelector('form[method="POST"]:not([data-no-unsaved])');
    if (!form) return;
    let dirty = false;
    form.querySelectorAll('input, textarea, select').forEach(el => {
        el.addEventListener('input', () => { dirty = true; });
        el.addEventListener('change', () => { dirty = true; });
    });
    form.addEventListener('submit', () => { dirty = false; });
    window.addEventListener('beforeunload', e => {
        if (dirty) { e.preventDefault(); e.returnValue = ''; }
    });
})();
</script>
