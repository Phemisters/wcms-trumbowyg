<?php
/**
 * Trumbowygeditor plugin.
 *
 * It transforms all the editable areas into the Trumbowyg inline editor.
 * Inspired by wondercms summernote editor plugin
 *
 * @author David Smith
 * @
 */

global $Wcms;

// Handle AJAX request for listing files
if ($Wcms->loggedIn && isset($_GET['listFiles'])) {
    header('Content-Type: application/json');
    
    $allowedTypes = ['images', 'docs', 'videos'];
    $type = $_GET['type'] ?? 'images';
    
    if (!in_array($type, $allowedTypes)) {
        header('HTTP/1.0 400 Bad Request');
        exit('Invalid file type request');
    }
    
    $type = $_GET['type'] ?? 'images';
    $filesPath = $Wcms->filesPath;
    $filteredFiles = [];
    
    // Logging setup
    $log = [];
    $log[] = "Received request for file type: $type";
    $log[] = "Scanning directory: $filesPath";

    if (!is_dir($filesPath)) {
        $log[] = "ERROR: Directory does not exist";
        echo json_encode(['error' => 'Files directory missing', 'log' => $log]);
        exit;
    }

    $allFiles = scandir($filesPath);
    if ($allFiles === false) {
        $log[] = "ERROR: scandir failed";
        echo json_encode(['error' => 'Failed to list files', 'log' => $log]);
        exit;
    }

    $log[] = "Found " . count($allFiles) . " items in directory";
    
    foreach ($allFiles as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $filePath = $filesPath . '/' . $file;
        if (!is_file($filePath)) {
            continue; // Skip directories
        }
        
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        switch ($type) {
            case 'images':
                $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                if (in_array($ext, $imageExts)) {
                    $filteredFiles[] = $file;
                }
                break;
            case 'docs':
                $docExts = ['pdf', 'doc', 'docx', 'txt', 'odt', 'zip'];
                if (in_array($ext, $docExts)) {
                    $filteredFiles[] = $file;
                }
                break;
            case 'videos':
                $videoExts = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mp3', 'wav', 'm4a'];
                if (in_array($ext, $videoExts)) {
                    $filteredFiles[] = $file;
                }
                break;
            default:
                $filteredFiles[] = $file;
                break;
        }
    }

    $log[] = "Filtered to " . count($filteredFiles) . " files";
    echo json_encode($filteredFiles);
    exit;
}

if (defined('VERSION')) {
    $Wcms->addListener('css', 'loadCSS');
    $Wcms->addListener('js', 'loadJS');
    $Wcms->addListener('editable', 'initialVariables');
}

function initialVariables($contents) {
    global $Wcms;
    $content = $contents[0];
    $subside = $contents[1];

    $contents_path = $Wcms->getConfig('contents_path');
    if (!$contents_path) {
        $Wcms->setConfig('contents_path', $Wcms->filesPath);
        $contents_path = $Wcms->filesPath;
    }
    $contents_path_n = trim($contents_path, "/");
    if ($contents_path != $contents_path_n) {
        $contents_path = $contents_path_n;
        $Wcms->setConfig('contents_path', $contents_path);
    }
    $_SESSION['contents_path'] = $contents_path;

    return array($content, $subside);
}

function loadJS($args) {
    global $Wcms;
    if ($Wcms->loggedIn) {
        $filesUrl = $Wcms->url('data/files/');
        $script = <<<EOT
        <script>
            var wcmsFilesUrl = '{$filesUrl}';
        </script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.31.0/dist/trumbowyg.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.31.0/dist/plugins/preformatted/trumbowyg.preformatted.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.0/dist/browser-image-compression.js" integrity="sha384-R5lOA9Vhja/AeGXhZyjsK0c+bpRhE5wPdquWfVrFgnHV6PtTQWggYgeqigzcRf+6" crossorigin="anonymous"></script>

<!-- Import Trumbowyg font size JS at the end of <body>...  -->
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.31.0/dist/plugins/fontsize/trumbowyg.fontsize.min.js"></script>

<!-- Import Trumbowyg colors JS at the end of <body>...  -->
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.31.0/dist/plugins/colors/trumbowyg.colors.min.js"></script>

<!-- Import Trumbowyg fontfamily JS at the end of <body>...  -->
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.31.0/dist/plugins/fontfamily/trumbowyg.fontfamily.min.js"></script>

<!-- Import Trumbowyg plugins...  -->
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.31.0/dist/plugins/pasteimage/trumbowyg.pasteimage.min.js"></script>

<!-- Import Trumbowyg plugins... -->
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.31.0/dist/plugins/table/trumbowyg.table.min.js"></script>

<!-- Import Trumbowyg plugins... -->
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.31.0/dist/plugins/resizimg/trumbowyg.resizimg.min.js"></script>


        <script src="{$Wcms->url('plugins/wcms-trumbowyg/js/jquery-resizable.min.js')}" type="text/javascript"></script>
        <script src="{$Wcms->url('plugins/wcms-trumbowyg/js/trumbowyg.upload.js')}" type="text/javascript"></script>
        <script src="{$Wcms->url('plugins/wcms-trumbowyg/js/admin.js')}" type="text/javascript"></script>
EOT;
        $args[0] .= $script;
    }
    return $args;
}


function loadCSS($args) {
    global $Wcms;
    if ($Wcms->loggedIn) {
        $script = <<<EOT
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.31.0/dist/ui/trumbowyg.min.css">
<!-- Import Trumbowyg colors style in <head>...  -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.31.0/dist/plugins/colors/ui/trumbowyg.colors.min.css">

<!-- Import Trumbowyg Table style in <head>... -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.31.0/dist/plugins/table/ui/trumbowyg.table.min.css">
        <!--
        <link rel="stylesheet" href="{$Wcms->url('plugins/wcms-trumbowyg/css/admin.css')}" type="text/css" media="screen">
        -->
EOT;
        $args[0] .= $script;
    }
    return $args;
}
