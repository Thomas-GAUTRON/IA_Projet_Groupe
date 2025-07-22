<?php
include 'begin_php.php';

$task_id = $_SESSION['task_id'] ?? null;
unset($_SESSION['task_id']); // Supprimer l'ID pour ne pas réutiliser

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
      if (preg_match_all('/begin{document}(.*?)\\\end{document}/s',  $result, $matches)) {
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

    // Données initiales (cas dashboard)
    const HAS_INITIAL_DATA = <?php echo isset($quizDataArr) && count($quizDataArr) ? 'true' : 'false'; ?>;
    <?php if (isset($quizDataArr)): ?>
    window.initialQuizArray = <?php echo json_encode($quizDataArr, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
    window.initialResumeArray = <?php echo json_encode($resumeArr ?? [], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
    <?php endif; ?>
  </script>
  <script src="assets/js/script.js"></script>
</head>

<body>
  <?php include 'header.html';
  ?>

  <div id="loader" style="display:none; text-align:center; margin-top:20px;">
      <div class="spinner" style="margin:auto; width:60px; height:60px; border:8px solid #f3f3f3; border-top:8px solid #3498db; border-radius:50%; animation:spin 1s linear infinite;"></div>
      <p id="loader-msg">Préparation...</p>
      <progress id="loader-bar" value="0" max="100" style="width:80%; height:20px;"></progress>
  </div>
  <div id="error-message" style="display:none; color:red; text-align:center;"></div>
  <textarea id="latex-input" style="display:none;"><?php echo $resumeArr[0]; ?></textarea>
  <textarea id="quiz-input" ><?php echo $quizDataArr[0]; ?></textarea>


  <h1 id="course-title">Chargement du cours...</h1>
  <select id="course-select" style="display:none; margin-bottom:10px;"></select>

  <!-- Onglets -->
  <div class="tabs">
    <button class="tab-button" data-view="quiz">Quiz</button>
    <button class="tab-button" data-view="resume">Résumé</button>
    <button class="tab-button active" data-view="both">Les deux</button>
  </div>

  <!-- Contenu -->
  <div class="container">
    <div class="pane" id="quiz-pane">
      <h2>Quiz</h2>
      <div id="quiz-container"></div>
      <button id="download-pdf">Télécharger le quiz en PDF</button>
    </div>

    <div class="pane" id="resume-pane">
      <h2>Résumé</h2>
      <div id="pdf-container" style="display:none; margin-top:20px; max-height:650px; border:1px solid #ccc; overflow:auto;">
        <iframe id="pdf-frame" style="width:100%; height:640px; border:none;"></iframe>
      </div>
      <p id="output" style="display:none;"></p>
      <button id="download-pdf-resume" onclick="downloadResumePdf()">Télécharger le résumé en PDF</button>
      <!-- Aperçu PDF -->
      
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const loader = document.getElementById('loader');
        const errorMessage = document.getElementById('error-message');
        const container = document.querySelector('.container');

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
                        } else if (data.status === 'failed') {
                            clearInterval(interval);
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
        }

        // Les cours seront chargés via loadCourse dans script.js

        function addToCourses(quizContent, resumeContent) {
            if (quizContent) {
                const match = quizContent.match(/```json(.*?)```/s) || quizContent.match(/---QUIZ_START---(.*?)---QUIZ_END---/s);
                if (match && match[1]) {
                    try {
                        courseQuizzes.push(JSON.parse(match[1].trim()));
                    } catch(e) { console.error('JSON parse error', e); }
                }
            }
            if (resumeContent) {
                const match = resumeContent.match(/\\begin{document}(.*?)\\end{document}/s);
                if (match && match[1]) {
                    courseResumes.push(match[1].trim());
                } else {
                    courseResumes.push('');
                }
            }
        }

        function processResults(results) {
            // Sauvegarder les résultats en arrière-plan
            fetch('save_result.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ result: results })
            }).then(res => res.json()).then(console.log).catch(console.error);

            let quizContent = '';
            let resumeContent = '';

            const quizPrefix = '---QUIZ_START---';
            const abstractPrefix = '---ABSTRACT START---';

            results.forEach(element => {
                if (element.startsWith(quizPrefix)) {
                    quizContent += element;
                } else if (element.startsWith(abstractPrefix)) {
                    resumeContent += element;
                }
            });

            // Mettre à jour le contenu de la page
            addToCourses(quizContent, resumeContent);
            populateCourseSelector();
            loadCourse(0);
        }
    });

    const buttons = document.querySelectorAll('.tab-button');
    const quizPane = document.getElementById('quiz-pane');
    const resumePane = document.getElementById('resume-pane');

    buttons.forEach(button => {
      button.addEventListener('click', () => {
        // Réinitialise les boutons
        buttons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');

        const view = button.dataset.view;

        if (view === 'quiz') {
          quizPane.classList.remove('hidden');
          resumePane.classList.add('hidden');
        } else if (view === 'resume') {
          quizPane.classList.add('hidden');
          resumePane.classList.remove('hidden');
        } else {
          quizPane.classList.remove('hidden');
          resumePane.classList.remove('hidden');
        }
      });
    });
  </script>
  <?php include 'footer.html'; ?>
</body>

</html>