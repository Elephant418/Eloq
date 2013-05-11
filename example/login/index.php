<?php

require_once('../../vendor/autoload.php');
use Pixel418\Eloq\Stack\Util\Form;

// Login form definition
$form = (new Form)
    ->addInput('email', 'required|email|validate_email', 'login.email')
    ->addInput('password', 'required', 'login.pwd');

// Treatment
if ($form->isValid()) {
    $form->clear();
}

require_once('../_base/example.php');