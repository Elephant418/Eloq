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


    /*************************************************************************
    TREATMENTS METHODS
     *************************************************************************/
    public function initFetchValue($population)
    {
        if (\UArray::hasDeepSelector($population, $this->address)) {
            $this->isActive = TRUE;
            $this->fetchValue = \UArray::getDeepSelector($population, $this->address);
        }
    }

    public function validFetchValue()
    {
        foreach ($this->filters as $filterName => $filter) {
            if ($filter->apply($this->fetchValue) === FALSE) {
                $this->error = $filterName;
                break;
            }
        }
    }

    public function getFilter($name)
    {
        if (!isset($this->filters[$name])) {
            throw new \RuntimeException('Try to get an unknown filter: ' . $name);
        }
        return $this->filters[$name];
    }
}