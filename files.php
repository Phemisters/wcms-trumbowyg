<?php

$fileContents = json_decode($_POST["fileContents"]);
$originalName = $fileContents->fileName;
$fileName = $originalName;
$dir    = '../../data/files';

# Get the base name and extension in case need to add an index to make the file name unique
$elements = explode(".", $fileName);
if (sizeof($elements) < 2) {
  $baseName = $fileName;
  $ext = "";
} else {
  $baseName = implode(array_splice($elements, 0, sizeof($elements)-1));
  $ext = "." . end($elements);
}

# Get the existsing file names`
$fileList = scandir($dir);

# If the current name is not unique try with an index
$index = 1;
while (in_array($fileName, $fileList)) {
  $fileName = $baseName . "_" . $index . $ext;
  $index++;
}

# Write the data to the file
$filePath = $dir . "/" . $fileName;
file_put_contents($filePath, base64_decode(substr($fileContents->contents, 22)));

# Prepare the response
$data = new stdClass();
$data->success = true;
# Need to remove the ../../ part
$data->file = substr($filePath, 6);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);
?>
