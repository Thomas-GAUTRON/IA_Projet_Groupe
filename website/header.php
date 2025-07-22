<div class="header">
    <a href="index.php">Accueil</a>
    <a href="form.php">Ajouter pdf</a>
    <a href="quizz.php">Quiz</a>
    <a href="dashboard.php">Dashboard</a>
    <a href="logout.php">Déconnexion</a>
</div>

<!-- Loader global (visible sur toutes les pages) -->
<div id="global-loader" style="display:none;position:fixed;top:0;left:0;width:100%;background:#007bff;color:#fff;text-align:center;padding:5px 0;font-size:0.9em;z-index:9999;">
    <span id="global-loader-text">Traitement en cours…</span>
    <progress id="global-loader-bar" value="0" max="100" style="width:150px;height:10px;vertical-align:middle;"></progress>
</div>

<?php // Script de suivi global
if (isset($config['FLASK_URL'])): ?>
<script>
(function() {
    const flaskUrl = "<?php echo rtrim($config['FLASK_URL'], '/'); ?>";
    const loader = document.getElementById('global-loader');
    const bar = document.getElementById('global-loader-bar');
    const text = document.getElementById('global-loader-text');

    function poll() {
        const taskId = localStorage.getItem('current_task_id');
        if (!taskId) { loader.style.display = 'none'; return; }

        fetch(`${flaskUrl}/result/${taskId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'processing') {
                    loader.style.display = 'block';
                    if (data.progress) text.textContent = data.progress;
                    if (typeof data.percent !== 'undefined') bar.value = data.percent;
                    setTimeout(poll, 5000);
                } else {
                    loader.style.display = 'none';
                    localStorage.removeItem('current_task_id');
                }
            })
            .catch(() => {
                loader.style.display = 'none';
                setTimeout(poll, 10000);
            });
    }

    poll();
})();
</script>
<?php endif; ?> 