<?php


function smarty_modifier_gravatar_info($email){
    $email = md5(strtolower(trim($email)));    
    $encode = "http://es.gravatar.com/".$email.".xml";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $encode);
    $data = curl_exec($ch);
    curl_close($ch);
    $xml = simplexml_load_string($data);
    echo $xml->entry->aboutMe; 
}