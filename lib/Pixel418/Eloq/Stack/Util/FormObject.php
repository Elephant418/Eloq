<?php

/* This file is part of the Eloq project, which is under MIT license */

namespace Pixel418\Eloq\Stack\Util;

class FormObject
{


    /*************************************************************************
    ATTRIBUTES
     *************************************************************************/
    const INPUT_ARRAY = 0;
    private $namespace;
    protected $populationType;
    protected $inputs = array();
    protected $isTreated = FALSE;


    /*************************************************************************
    CONSTRUCTOR METHODS
     *************************************************************************/
    public function __construct($populationType = INPUT_POST)
    {
        $this->setPopulationType($populationType);
        $this->namesapce = \UObject::getNamespace( $this );
    }


    /*************************************************************************
    INPUT GETTER METHODS
     *************************************************************************/
    public function getPopulation(int $populationType=NULL){
        if (is_null($populationType)) {
            $populationType = $this->populationType;
        }
        if ($populationType==self::INPUT_ARRAY) {
            return $this->population;
        }
        return filter_input_array($populationType);
    }


    /*************************************************************************
    SETTER METHODS
     *************************************************************************/
    public function setPopulationType($populationType)
    {
        $this->populationType = $populationType;
        return $this;
    }

    public function setPopulation(array $population){
        $this->populationType = self::INPUT_ARRAY;
        $this->population = $population;
        return $this;
    }

    public function addInput($input)
    {
        $inputClass = $this->namesapce.'\\FormInput';
        if (is_string($input)) {
            $input = new $inputClass($input);
        }
        if (!is_a($input, $inputClass)) {
            throw new \Exception('Wrong input type: '.get_class($input).' expected '.$inputClass);
        }
        $input->setFormObject($this);
        $this->inputs[$input->getName()] = $input;
        return $this;
    }

    public function removeInput(string $name)
    {
        unset($this->inputs[$name]);
        return $this;
    }


    /*************************************************************************
    GETTER METHODS
     *************************************************************************/
    public function isActive()
    {
        foreach ($this->inputs as $input) {
            if ($input->isActive()) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function isValid()
    {
        foreach ($this->inputs as $input) {
            if (!$input->isValid()) {
                return FALSE;
            }
        }
        return TRUE;
    }

    public function __get($name)
    {
        return $this->getInput($name);
    }

    public function getInput($name)
    {
        if (!isset($this->inputs[$name])) {
            return FALSE;
        }
        return $this->inputs[$name];
    }
}