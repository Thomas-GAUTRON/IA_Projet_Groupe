<?php
include "begin_php.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['result']) && is_array($data['result'])) {
        $tableau = $data['result'];
        $_SESSION['result'] = $tableau;
        $prefix1 = '---ABSTRACT START---';
        $prefix2 = '---QUIZ_START---';

        // Fonction pour générer un ID unique (similaire à l'ancien load.php)
        function random_int64_positive()
        {
            $bytes = random_bytes(8);
            $hex = bin2hex($bytes);
            $unsigned = bchexdec($hex);
            $max_positive_int64 = '9223372036854775807';
            if (bccomp($unsigned, $max_positive_int64) === 1) {
                $unsigned = bcmod($unsigned, bcadd($max_positive_int64, '1'));
            }
            return $unsigned;
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

        $randomInt64Pos = random_int64_positive();
        $_SESSION['reponse'] = $randomInt64Pos;
        // Inclure la logique de découpage si elle est dans un autre fichier
        // require_once 'path/to/split_text_function.php';

        foreach ($tableau as $element) {
            if (substr($element, 0, strlen($prefix1)) === $prefix1) {
                // Assurez-vous que split_text est disponible
                $sub_text = function_exists('split_text') ? split_text($element) : [$element];
                foreach ($sub_text as $chunk) {
                    $db_data = [
                        'content' => $chunk,
                        'id_request' => $randomInt64Pos,
                        'id_user' => $_SESSION['user_id'],
                        'type' => 'resume'
                    ];
                    insert_in_supabase($config['SUPABASE_URL'], $config['SUPABASE_KEY'], $config['SUPABASE_TABLE'], $db_data);
                }
            } elseif (substr($element, 0, strlen($prefix2)) === $prefix2) {
                $sub_text = function_exists('split_text') ? split_text($element) : [$element];
                foreach ($sub_text as $chunk) {
                    $db_data = [
                        'content' => $chunk,
                        'id_request' => $randomInt64Pos,
                        'id_user' => $_SESSION['user_id'],
                        'type' => 'quiz'
                    ];
                    insert_in_supabase($config['SUPABASE_URL'], $config['SUPABASE_KEY'], $config['SUPABASE_TABLE'], $db_data);
                }
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Résultats sauvegardés.', 'id_request' => $randomInt64Pos]);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Données de résultat invalides.']);
    }
}
?> 