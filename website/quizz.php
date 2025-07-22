<?php
include 'begin_php.php';

$task_id = $_SESSION['task_id'] ?? null;
unset($_SESSION['task_id']); // Supprimer l'ID pour ne pas réutiliser

// L'ancien code de récupération de données sera déclenché par JS
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
    const TASK_ID = "<?php echo $task_id; ?>";
  </script>
  <script src="assets/js/script.js"></script>
</head>

<body>
  <?php include 'header.html'; 
  ?>
  <div id="loader" style="display:none; text-align:center; margin-top:20px;">
      <div class="spinner" style="margin:auto; width:60px; height:60px; border:8px solid #f3f3f3; border-top:8px solid #3498db; border-radius:50%; animation:spin 1s linear infinite;"></div>
      <p>Traitement en cours, veuillez patienter...</p>
  </div>
  <div id="error-message" style="display:none; color:red; text-align:center;"></div>
  
  <textarea id="latex-input" style="display: none;">
    <?php echo $contenu2; ?>
  </textarea>

  <h1 id="course-title">Chargement du cours...</h1>

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
                        // Si 'processing', on ne fait rien et on attend le prochain appel
                    })
                    .catch(err => {
                        clearInterval(interval);
                        loader.style.display = 'none';
                        errorMessage.textContent = 'Erreur de communication avec le serveur de traitement.';
                        errorMessage.style.display = 'block';
                    });
            }, 5000); // Interroge toutes les 5 secondes
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
            if (quizContent) {
                const match = quizContent.match(/```json(.*?)```/s) || quizContent.match(/---QUIZ_START---(.*?)---QUIZ_END---/s);
                if (match && match[1]) {
                    window.quizData = JSON.parse(match[1].trim());
                    // Assurez-vous que les fonctions de script.js sont prêtes
                    if (typeof buildQuiz === 'function') {
                        buildQuiz();
                    }
                }
            }

            if (resumeContent) {
                const match = resumeContent.match(/\\begin{document}(.*?)\\end{document}/s);
                if (match && match[1]) {
                    document.getElementById('latex-input').value = match[1].trim();
                    // Assurez-vous que les fonctions de script.js sont prêtes
                    if (typeof generatePdfFromLatex === 'function') {
                        generatePdfFromLatex();
                    }
                }
            }
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