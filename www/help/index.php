<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Wesnail Docs</title>
    <link href="themes/md.css" rel="stylesheet">
</head>
<body>  
    <?php
    if(empty($_GET)) exit;

    list($fileId) = array_keys($_GET);
    $helpFile = __DIR__."/docs/{$fileId}.md";

    if(! file_exists($helpFile)) {
        echo 'Invalid doc id';
        exit;
    }

    include __DIR__.'/Parsedown.php';
    $md = new Parsedown();
    $md->setBreaksEnabled(true);

    $mdContent = file_get_contents($helpFile);
    echo $md->text($mdContent);
    ?>
</body>
</html>