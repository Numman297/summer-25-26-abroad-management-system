</main>

<footer class="footer">
    &copy; <?= date('Y') ?> <?= esc(APP_NAME) ?> &mdash; Abroad Management System
</footer>

<script>
    window.APP_ROLE = '<?= esc(current_role()) ?>';
</script>
<script src="assets/js/app.js"></script>
</body>
</html>
