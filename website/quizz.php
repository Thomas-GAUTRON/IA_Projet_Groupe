<?php
include 'begin_php.php';

$task_id = $_SESSION['task_id'] ?? null;
// Désactivé : on conserve désormais le task_id tant que la génération n’est pas terminée
unset($_SESSION['task_id']);

// Si pas de task_id, on récupère les données stockées (accès via dashboard)
if (!$task_id) {
  $supabaseUrl = $config['SUPABASE_URL'];
  $supabaseKey = $config['SUPABASE_KEY'];
  $table = $config['SUPABASE_TABLE'];
  $idRequest = $_SESSION['reponse'] ?? 0;

  if ($idRequest) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_URL, "$supabaseUrl/rest/v1/$table?id_request=eq.$idRequest");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "apikey: $supabaseKey",
      "Authorization: Bearer $supabaseKey",
      "Content-Type: application/json",
      "Accept: application/json"
    ]);
    $response = curl_exec($ch);
    $data = json_decode($response, true);
    curl_close($ch);

    if (is_array($data)) {
      usort($data, fn($a, $b) => $a['id'] <=> $b['id']);
      $page = '';
      $result = '';
      foreach ($data as $row) {
        if ($row['type'] == 'quiz') {
          $page .= $row['content'] . ' ';
        } else {
          $result .= $row['content'] . ' ';
        }
      }
      // Récupérer tous les quiz et résumés
      $quizDataArr = [];
      // Capturer plusieurs formats de quiz
      $patterns = [
        '/```json(.*?)```/s',
        '/```(.*?)```/s',
        '/---QUIZ_START---(.*?)---QUIZ_END---/s'
      ];
      foreach ($patterns as $pat) {
        if (preg_match_all($pat, $page, $m)) {
          foreach ($m[1] as $q) {
            $quizDataArr[] = trim($q);
          }
        }
      }

      $resumeArr = [];
      if (preg_match_all('/\\\begin{document}(.*?)\\\end{document}/s',  $result, $matches)) {
        foreach ($matches[1] as $l) {
          $resumeArr[] = trim($l);
        }
      }
    }
  }
}
?>
<?php if (!isset($_SESSION['access_token'])) {
  header('Location: login.php');
  exit;
} ?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <title>Quiz Dynamique</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
  <!-- Scripts -->
  <!-- MathJax Configuration -->
  <!-- MathJax Configuration -->
  <script>
    MathJax = {
      tex: {
        inlineMath: [
          ['\\(', '\\)'],
          ['$', '$']
        ],
        displayMath: [
          ['\\[', '\\]'],
          ['$$', '$$']
        ],
        processEscapes: true,
        processEnvironments: true,
        packages: {
          '[+]': ['ams', 'newcommand', 'configmacros']
        }
      },
      options: {
        skipHtmlTags: ['script', 'noscript', 'style', 'textarea', 'pre']
      }
    };
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/3.2.2/es5/tex-mml-chtml.min.js"></script>
  <!-- html2canvas nécessaire pour l’export PDF via jsPDF -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script>
    // Passer l'URL du serveur Flask et le task_id au JavaScript
    const FLASK_URL = "<?php echo rtrim($config['FLASK_URL'], '/'); ?>";
    const TASK_ID = "<?php echo $task_id ?? ''; ?>";
    // Stockage global désormais géré par header.php
    if (TASK_ID) {
       localStorage.setItem('current_task_id', TASK_ID);
     }

    // Données initiales (cas dashboard)
    const HAS_INITIAL_DATA = <?php echo (isset($quizDataArr) && !empty($quizDataArr)) || (isset($resumeArr) && !empty($resumeArr)) ? 'true' : 'false'; ?>;
    <?php if (isset($quizDataArr) || isset($resumeArr)): ?>
    window.initialQuizArray = <?php echo json_encode($quizDataArr ?? [], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
    window.initialResumeArray = <?php echo json_encode($resumeArr ?? [], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
    <?php endif; ?>
  </script>
  <script src="assets/js/script.js"></script>
</head>

<body>
    <div class="page-container">
        <?php include 'header.php'; ?>

        <main class="container quiz-layout">
            <h1 id="course-title">Chargement du cours...</h1>
            <select id="course-select" class="course-selector"></select>

            <!-- Onglets -->
            <div class="tabs">
                <button onclick="updatePanesVisibility(event)" class="tab-button" data-view="quiz">Quiz</button>
                <button onclick="updatePanesVisibility(event)" class="tab-button" data-view="resume">Résumé</button>
                <button onclick="updatePanesVisibility(event)" class="tab-button active" data-view="both">Les deux</button>
            </div>

            <!-- Contenu -->
            <div class="content-container">
                <div class="pane" id="quiz-pane">
                    <div class="pane-header">
                        <h2>Quiz</h2>
                        <button id="download-pdf" class="btn btn-sm">Télécharger en PDF</button>
                    </div>
                    <div id="quiz-container"></div>
                </div>

                <div class="pane" id="resume-pane">
                    <div class="pane-header">
                        <h2>Résumé</h2>
                        <button id="download-pdf-resume" class="btn btn-sm" onclick="downloadResumePdf()">Télécharger en PDF</button>
                    </div>
                    <iframe id="pdf-frame" class="pdf-iframe"></iframe>
                </div>
            </div>
        </main>
    </div>

    <div id="loader" class="loader-overlay">
        <div class="spinner"></div>
        <p id="loader-msg" class="loader-text">Préparation...</p>
        <progress id="loader-bar" class="loader-bar" value="0" max="100"></progress>
    </div>
    <div id="error-message" class="error-banner"></div>

    <textarea id="latex-input" style="display:none;"><?php echo isset($resumeArr) ? (is_array($resumeArr) ? ($resumeArr[0] ?? '') : $resumeArr) : ''; ?></textarea>
    <textarea id="quiz-input" style="display:none;"><?php echo isset($quizDataArr) ? (is_array($quizDataArr) ? ($quizDataArr[0] ?? '') : $quizDataArr) : ''; ?></textarea>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const loader = document.getElementById('loader');
        const errorMessage = document.getElementById('error-message');
        const container = document.querySelector('.container');

        // Cas 1: Les données sont déjà chargées par PHP (via le Dashboard)
        if (HAS_INITIAL_DATA) {
            // Le nouveau script.js gère l'initialisation, donc on n'a plus besoin
            // de la logique complexe ici. On s'assure juste que le loader est masqué.
            loader.style.display = 'none';
            return; // Le reste est géré par script.js
        }

        // Cas 2: Un ID de tâche est présent, on doit interroger le serveur
        if (TASK_ID) {
            loader.style.display = 'block';
            container.style.display = 'none';

            const interval = setInterval(() => {
                fetch(`${FLASK_URL}/result/${TASK_ID}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'completed') {
                             clearInterval(interval);
                            loader.style.display = 'none';
                            container.style.display = 'flex';
                            processResults(data.result);
                            return;
                        } else if (data.status === 'failed') {
                            clearInterval(interval);
                            localStorage.removeItem('current_task_id');
                            loader.style.display = 'none';
                            errorMessage.textContent = 'Une erreur est survenue lors du traitement : ' + data.error;
                            errorMessage.style.display = 'block';
                        }
                        // Mise à jour du message/progression
                        if (data.progress) {
                            document.getElementById('loader-msg').textContent = data.progress;
                        }
                        if (typeof data.percent !== 'undefined') {
                            document.getElementById('loader-bar').value = data.percent;
                        }
                        // Si 'processing', on attend le prochain appel
                    })
                    .catch(err => {
                        clearInterval(interval);
                        loader.style.display = 'none';
                        errorMessage.textContent = 'Erreur de communication avec le serveur de traitement.';
                        errorMessage.style.display = 'block';
                    });
            }, 5000); // Interroge toutes les 5 secondes
        } else {
            // Cas 3: Aucune donnée et aucun ID de tâche
            loader.style.display = 'none';
            errorMessage.textContent = "Aucun quiz à charger. Veuillez en générer un nouveau.";
            errorMessage.style.display = 'block';
        }

        // Les cours seront chargés via loadCourse dans script.js

        function processResults(results) {
            // Sauvegarder les résultats en arrière-plan
            fetch('save_result.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ result: results })
            }).then(res => res.json()).then(console.log).catch(console.error);

            // Nouvelle logique pour traiter les résultats sans recharger
            window.courses = []; // Réinitialiser les cours

            let initialQuizzes = [];
            let initialResumes = [];

            const quizPrefix = '---QUIZ_START---';
            const abstractPrefix = '---ABSTRACT START---';

            results.forEach(element => {
                if (element.startsWith(quizPrefix)) {
                    initialQuizzes.push(element);
                } else if (element.startsWith(abstractPrefix)) {
                    initialResumes.push(element);
                }
            });

            // Recréer la structure de cours comme dans le script.js
            const numCourses = Math.max(initialQuizzes.length, initialResumes.length);
            for (let i = 0; i < numCourses; i++) {
                const quizContent = initialQuizzes[i] || null;
                const resumeContent = initialResumes[i] || null;
                
                const quiz = quizContent ? parseQuizJson(quizContent) : null;
                const resume = resumeContent ? resumeContent.match(/\\begin{document}(.*?)\\end{document}/s)?.[1].trim() : null;

                if (quiz || resume) {
                    let title = `Cours ${i + 1}`;
                    if (quiz && quiz.courseTitle) {
                        title = quiz.courseTitle;
                    } else if (resume) {
                        const match = resume.match(/section\*\{([^}]+)\}/);
                        if (match) title = match[1].replace(/\\[a-zA-Z]+/g, '').trim();
                    }
                    window.courses.push({ title: title, quizData: quiz, resumeData: resume });
                }
            }
            
            // Mettre à jour l'interface
            populateCourseSelector();
            loadCourse(0);
            updateTabsAndPanesForCourse(window.courses[0]);
        }

        // La gestion des boutons est maintenant dans script.js
    });
  </script>
  <?php include 'footer.html'; ?>
</body>

</html>