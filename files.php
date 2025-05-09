<?php

$fileContents = json_decode($_POST["fileContents"]);
$fileName = "data/files/" . $fileContents->fileName;
file_put_contents("../../" . $fileName, base64_decode(substr($fileContents->contents, 22)));

$data = new stdClass();
$data->success = true;
$data->file = $fileName;
header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);
?>
