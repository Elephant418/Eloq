<?php

/* This file is part of the Eloq project, which is under MIT license */

namespace Pixel418\Eloq\Stack\Util;

class FormInput
{


    /* ATTRIBUTES
     *************************************************************************/
    public $populationType;
    public $address;
    public $defaultValue;
    public $fetchValue;
    public $filters = array();
    public $error;
    public $errorMessage;
    public $isActive = FALSE;


    /* CONSTRUCTOR METHODS
     *************************************************************************/
    public function __construct($name)
    {
        $this->address = $name;
    }


    /* GETTER METHODS
     *************************************************************************/
    public function getValue()
    {
        if (!is_null($this->fetchValue)) {
            return $this->fetchValue;
        }
        return $this->defaultValue;
    }

    public function isValid()
    {
        return ($this->error == NULL);
    }
}