<?php

/* This file is part of the Eloq project, which is under MIT license */

namespace Pixel418\Eloq\Stack\Util;

class FormInput
{


    /*************************************************************************
    ATTRIBUTES
     *************************************************************************/
    protected $name;
    protected $populationType;
    protected $address;
    protected $defaultValue;
    protected $fetchValue;
    protected $filters = array();
    protected $error;
    protected $isTreated = FALSE;
    protected $isActive = FALSE;


    /*************************************************************************
    CONSTRUCTOR METHODS
     *************************************************************************/
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }


    /*************************************************************************
    DEFINITION METHODS
     *************************************************************************/
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function setAddress($address, $populationType=NULL)
    {
        $this->address = $address;
        if (!is_null($populationType)) {
            $this->populationType = $populationType;
        }
        return $this;
    }

    public function addFilter(FormInputFilter $filter)
    {
        $this->filters[$filter->getName()] = $filter;
        return $this;
    }

    public function removeFilter($name)
    {
        unset($this->filters[$name]);
        return $this;
    }


    /*************************************************************************
    RESULT METHODS
     *************************************************************************/
    public function treat($formObject)
    {
        if ($this->isTreated) {
            return NULL;
        }
        $this->initFetchValue($formObject);
        $this->isTreated = TRUE;
        if (!$this->isActive) {
            return NULL;
        }
        $this->validFetchValue();
    }

    public function isActive()
    {
        if (!$this->isTreated) {
            throw new \RuntimeException('Try to get the state of an untreat form');
        }
        return $this->isActive;
    }

    public function isValid()
    {
        if (!$this->isTreated) {
            throw new \RuntimeException('Try to get the state of an untreat form');
        }
        return is_null($this->error);
    }

    public function getValue()
    {
        if (!$this->isTreated) {
            throw new \RuntimeException('Try to get the state of an untreat form');
        }
        if (!is_null($this->fetchValue)) {
            return $this->fetchValue;
        }
        return $this->defaultValue;
    }

    public function __toString()
    {
        return $this->getValue();
    }

    public function getError()
    {
        return $this->error;
    }


    /*************************************************************************
    PRIVATE METHODS
     *************************************************************************/
    protected function initFetchValue($formObject)
    {
        $population = $this->getPopulation($formObject);
        $address = $this->getAddress();
        if (\UArray::hasDeepSelector($population, $address)) {
            $this->isActive = TRUE;
            $this->fetchValue = \UArray::getDeepSelector($population, $address);
        }
    }

    protected function validFetchValue()
    {
        foreach ($this->filters as $filterName => $filter) {
            if (!$filter->apply($this->fetchValue)) {
                $this->error = $filterName;
                break;
            }
        }
    }

    protected function getPopulation($formObject)
    {
        return $formObject->getPopulation($this->populationType);
    }

    protected function getAddress()
    {
        if (!is_null($this->address)) {
            return $this->address;
        }
        return $this->name;
    }
}