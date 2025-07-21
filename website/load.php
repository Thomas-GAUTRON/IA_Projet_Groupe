<?php
include "begin_php.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flask_url = $config["FLASK_URL"];
    // Préparer les données POST
    $postData = [
        'option' => $_POST['option'] ?? '',
        'mod' => $_POST['mod'] ?? '',
    ];

    // Gérer les fichiers uploadés
    if (isset($_FILES['files']) && is_array($_FILES['files']['tmp_name'])) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) {
                $filePath = $tmp_name;
                $fileName = $_FILES['files']['name'][$key];
                // Use standard array notation for multiple files
                $postData['files[]'] = new CURLFile($filePath, mime_content_type($filePath), $fileName);
            }
        }
    }

    $ch = curl_init($flask_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Erreur cURL : ' . curl_error($ch);
        exit;
    }

    // Stocker la réponse dans une variable d'environnement
    $_SESSION["result"] = $response;
    curl_close($ch);



    function random_int64_positive()
    {
        $bytes = random_bytes(8);
        $hex = bin2hex($bytes);

        // Convertir en entier non signé (0 à 2^64 - 1)
        $unsigned = bchexdec($hex);

        $max_positive_int64 = '9223372036854775807'; // 2^63 - 1

        // Si la valeur dépasse la moitié signée positive, on la réduit par modulo pour rester dans la plage positive signée
        if (bccomp($unsigned, $max_positive_int64) === 1) {
            $unsigned = bcmod($unsigned, bcadd($max_positive_int64, '1')); // modulo 2^63
        }

        return $unsigned; // string positive <= 2^63-1
    }

    function bchexdec(string $hex): string
    {
        $dec = '0';
        $len = strlen($hex);
        for ($i = 0; $i < $len; $i++) {
            $dec = bcmul($dec, '16', 0);
            $dec = bcadd($dec, hexdec($hex[$i]), 0);
        }
        return $dec;
    }


    $tableau = json_decode($response, true); // Le second paramètre "true" retourne un tableau associatif
    $prefix1 = '---ABSTRACT START---';
    $prefix2 = '```json';

    // Exemple d'utilisation
    $randomInt64Pos = random_int64_positive();
    $_SESSION['reponse'] = $randomInt64Pos;

    $id = getUserIdFromAccessToken($_SESSION['access_token'], $config['SUPABASE_URL'], $config['SUPABASE_KEY']);

    foreach ($tableau as $element) {
        if (substr($element, 0, strlen($prefix1)) === $prefix1) {
            $sub_text = split_text($element);
            foreach ($sub_text as $chunk) {
                $data = [
                    'content' => $chunk,
                    'id_request' => $randomInt64Pos,
                    'id_user' => $id,
                    'type' => 'resume'
                ];
                $rep = insert_in_supabase($config['SUPABASE_URL'], $config['SUPABASE_KEY'], $config['SUPABASE_TABLE'], $data);
                echo "Abstract : " . $rep['http_code'] . "<br>";
            }
        } elseif (substr($element, 0, strlen($prefix2)) === $prefix2) {
            $sub_text = split_text($element);
            foreach ($sub_text as $chunk) {
                $data = [
                    'content' => $chunk,
                    'id_request' => $randomInt64Pos,
                    'id_user' => $id,
                    'type' => 'quiz'
                ];
                $rep = insert_in_supabase($config['SUPABASE_URL'], $config['SUPABASE_KEY'], $config['SUPABASE_TABLE'], $data);
                echo "Quiz : " . $rep['http_code'] . "<br>";
            }
        } else {
            echo "❌ '$element' ne commence pas par '$prefix'<br>";
        }
    }

    // Afficher la réponse
    header("Location: quizz");
    exit;
}
