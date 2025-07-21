<?php
include 'begin_php.php';
$supabaseUrl = $config['SUPABASE_URL'];
$supabaseKey = $config['SUPABASE_KEY']; // généralement la clé anonyme (public)
$table = $config['SUPABASE_TABLE'];
if (isset($_SESSION["reponse"])) {
  $idRequest = $_SESSION["reponse"];
} else {
  $idRequest = 0;
}
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
usort($data, function ($a, $b) {
  return $a['id'] <=> $b['id'];
});

$page = "";
$result = "";
if (!empty($data) && isset($data[0]['content'])) {
  foreach ($data as $row) {
    if ($row['type'] == 'quiz') {
      $page = $page . $row['content'] . " ";
    } else {
      $result = $result . $row['content'] . " ";
    }
  }
}

if (preg_match('/```json(.*?)```/s',  $page, $matches)) {
  $contenu = trim($matches[1]); // On enlève les espaces inutiles
}
$contenu2 = "";
if (preg_match('/begin{document}(.*?)\\\end{document}/s',  $result, $matches)) {
  $contenu2 = trim($matches[1]); // On enlève les espaces inutiles
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script>
    const quizData = <?php echo $contenu; ?>;
  </script>
  <script src="assets/js/script.js"></script>
</head>

<body>
  <?php include 'header.html'; 
  ?>
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
      <p id="output"></p>
    </div>
  </div>
  <script>
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

</body>

</html>