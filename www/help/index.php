<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Usleju API Docs</title>
    <script type="text/javascript" src="/help/highlight/highlight.pack.js"></script>
    <link href="themes/md.css" rel="stylesheet">
    <link rel="stylesheet" href="/help/highlight/styles/default.css">
    <style type="text/css">
    pre, .hljs {
        background:#fff;
    }
    </style>
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

    <script type="text/javascript">
    hljs.initHighlightingOnLoad();
    </script>
</body>
</html>