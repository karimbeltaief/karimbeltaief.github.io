<?php

function parseJson($string){
    return json_decode($string);
}

function pushEvent($array, $event){
    $array[] = $event;
    return $array;
}

function serializeJson($array){
    return json_encode($array);
}

function writeFile($fileName, $content){
    $fic = fopen($fileName, 'w+');
    if($fic != null){
        fprintf($fic, "%s", $content);
    }
    fclose($fic);
}
if(isset($_POST['eventName'])){
    $eventName = $_POST['eventName'];
    $eventStart= $_POST['eventStart'];
    $eventEnd  = $_POST['eventEnd'];
    $fileName = 'events.json';
    $event = [
        'eventName' => $eventName,
        'eventStart' => $eventStart,
        'eventEnd' => $eventEnd
    ];
    $content = file_get_contents($fileName);
    $jsonArray = parseJson($content);
    $jsonArray = pushEvent($jsonArray, $event);
    $content = serializeJson($jsonArray);
    writeFile($fileName, $content);
}else if(isset($_POST)){
    writeFile("posts.json", json_encode($_SERVER));
}