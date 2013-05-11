<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Example</title>
    <meta name="viewport" content="width=initial-scale=1.0">
    <link rel="stylesheet" href="http://css.cdn.tl/normalize.css">
    <style>
        form {
            width: 500px;
            margin: 0 auto;
            padding-top: 2em;
        }
        label {
            display: block;
            margin-bottom: 1em;
        }
        .label {
            width: 150px;
            float: left;
            line-height: 150%;
        }
        .alert {
            background-color: #f7f1e8;
            color: #c09853;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
            margin-top: 1em;
            margin-bottom: 0.5em;
            padding: 8px 35px 8px 14px;
            border: 1px solid #f0e6d6;
        }

        .alert-success {
            color: #468847;
            background-color: #dfeedf;
            border-color: #cee6ce;
        }

        .alert-danger,
        .alert-error {
            color: #b94a48;
            background-color: #f1dcdc;
            border-color: #eacac9;
        }
    </style>
</head>
<body>
    <?php
        $example = basename(dirname($_SERVER['SCRIPT_FILENAME']));
        include('../'.$example.'/template.php');
    ?>
</body>
</html>