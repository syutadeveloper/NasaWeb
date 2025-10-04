<?php
function translate($text, $from, $to) {
    $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=$from&tl=$to&dt=t&q=" . urlencode($text);
    $response = @file_get_contents($url);

    if ($response === false) {
        return '';
    }

    $result = json_decode($response, true);
    return $result[0][0][0] ?? '';
}
