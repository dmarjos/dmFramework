<?php

error_reporting(E_ALL);
include "agent.php"; 
header("Cache-Control: no-cache, must-revalidate" ); 
header("Pragma: no-cache" );
header("Content-Type: text/html; charset=utf-8");


function total_by_search($keyword){
    $url = 'https://ajax.googleapis.com/ajax/services/search/web?v=1.0&q='.urlencode($keyword);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_REFERER, 'http://my.divalia.mx/');
    $data = curl_exec($ch);
    curl_close($ch);
    
    $content = json_decode($data);
    return $content->responseData->cursor->resultCount;
}

$lang = 'es';
$query = $_GET['query'];
if(empty($query)){
  $query = 'alojamiento web en mexico';   
} else {
  $query = $_GET['query'];  
}

$url = 'http://suggestqueries.google.com/complete/search?output=firefox&client=firefox&hl=' . $lang . '&q=' . urlencode($query);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, random_uagent());
$data = curl_exec($ch);
curl_close($ch);

$suggestions = json_decode($data, true);
array_shift($suggestions);
if ($suggestions) {
    echo 'Sugerencias: (Usar ?query=palabra o frase) para cambiar el resultado.';
    echo "<ul>";
    foreach($suggestions as $row => $innerArray){
      foreach($innerArray as $innerRow => $value){
        echo "<li><a href='test3.php?query=".$value."'>".$value." (".total_by_search($value).")</a></li>";
      }
    }
    echo "</ul>";
} else {
    echo 'no suggestion';
}