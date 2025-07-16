<?php
include 'begin_php.php';
$supabaseUrl = $config['supabase_url'];
$supabaseKey = $config['supabase_key']; // généralement la clé anonyme (public)
$table = $config['table_name'];
$idRequest = json_decode($_SESSION['response']);

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

if (!empty($data) && isset($data[0]['content'])) {
  $page = "";
  $result = "";
  foreach ($data as $row) {
    if (isset($row['metadata'])) {
      $metadata = json_decode($row['metadata'], true);
      if (isset($metadata['line']) && isset($row['content']) && $metadata['line'] == 2) {
        $page = $page . $row['content'] . "\n\n"; // Séparé par deux sauts de ligne
      }
      if (isset($metadata['line']) && isset($row['content']) && $metadata['line'] == 1) {
        $result = $result . $row['content'] . "\n\n"; // Séparé par deux sauts de ligne
      }
    }
  }
}
if (preg_match('/```json(.*?)```/s',  $page, $matches)) {
  $contenu = trim($matches[1]); // On enlève les espaces inutiles
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <title>Quiz Dynamique</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
   <!-- Scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script>
    const quizData = <?php echo $contenu; ?>;
  </script>
  <script src="assets/js/script.js"></script>
  
</head>

<body>
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
      <p><?php
      if (preg_match('/---ABSTRACT START---(.*?)---ABSTRACT END---/s',  $result, $matches)) {
        $contenu2 = trim($matches[1]); // On enlève les espaces inutiles
        echo $contenu2;
      }
       ?></p>
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
 <?php // include "footer.html"; ?>
 
</body>
</html>