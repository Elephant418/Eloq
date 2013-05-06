<?php

/* This file is part of the Eloq project, which is under MIT license */

namespace Pixel418\Eloq\Stack\Util;

class Form
{


    /* ATTRIBUTES
     *************************************************************************/
    const INPUT_ARRAY = 0;
    private $namespace;
    protected $lang;
    protected $population;
    protected $populationType;
    protected $inputs = array();
    protected $isTreated = FALSE;


    /* CONSTRUCTOR
     *************************************************************************/
    public function __construct($populationType = INPUT_POST)
    {
        $this->setPopulationType($populationType);
        $this->namespace = \UObject::getNamespace($this);
        $filterClass = $this->namespace . '\\FormInputFilter';
        $this->setLang($filterClass::$lang);
    }


    /* FORM SETTER METHODS
     *************************************************************************/
    public function setPopulationType($populationType)
    {
        $this->populationType = $populationType;
        return $this;
    }

    public function setPopulation(array $population)
    {
        $this->setPopulationType(self::INPUT_ARRAY);
        $this->population = $population;
        return $this;
    }

    public function addInput($name, $address = NULL, $populationType = NULL)
    {
        $inputClass = $this->namespace . '\\FormInput';
        $input = new $inputClass($name);
        $this->inputs[$name] = $input;
        if ($address) {
            $this->setInputAddress($name, $address, $populationType);
        }
        return $this;
    }

    public function removeInput($name)
    {
        unset($this->inputs[$name]);
        return $this;
    }

    public function clear()
    {
        foreach ($this->inputs as $input) {
            $input->fetchValue = NULL;
        }
        return $this;
    }

    public function setLang($lang)
    {
        $this->lang = $lang;
        return $this;
    }


    /* INPUT SETTER METHODS
     *************************************************************************/
    public function setInputAddress($name, $address, $populationType = NULL)
    {
        $input = $this->getInput($name);
        $input->address = $address;
        $input->populationType = $populationType;
        return $this;
    }

    public function setInputDefaultValue($name, $defaultValue)
    {
        $input = $this->getInput($name);
        $input->defaultValue = $defaultValue;
        return $this;
    }


    /* FILTER SETTER METHODS
     *************************************************************************/
    public function setInputFilter($inputName, $filters)
    {
        $input = $this->getInput($inputName);
        $input->filters = [];
        $this->addInputFilter($inputName, $filters);
        return $this;
    }

    public function addInputFilter($inputName, $filterName, callable $filterCallback = NULL)
    {
        $input = $this->getInput($inputName);
        $filterClass = $this->namespace . '\\FormInputFilter';
        $filter = new $filterClass($filterName, $filterCallback);
        $input->filters[$filter->getName()] = $filter;
        return $this;
    }

    public function addInputFilters($inputName, $filters)
    {
        $input = $this->getInput($inputName);
        $filterClass = $this->namespace . '\\FormInputFilter';
        $filters = explode('|', $filters);
        foreach ($filters as $inputName) {
            $filter = new $filterClass($inputName);
            $input->filters[$filter->getName()] = $filter;
        }
        return $this;
    }

    public function removeInputFilter($inputName, $filters)
    {
        $input = $this->getInput($inputName);
        $filterClass = $this->namespace . '\\FormInputFilter';
        $filters = explode('|', $filters);
        foreach ($filters as $inputName) {
            $filter = new $filterClass($inputName);
            unset($input->filters[$filter->getName()]);
        }
        return $this;
    }

    public function setInputFilterOption($inputName, $filterName, $option)
    {
        $input = $this->getInput($inputName);
        $filter = $input->getFilter($filterName);
        $filter->setOption($option);
        return $this;
    }


    /* FORM GETTER METHODS
     *************************************************************************/
    public function treat()
    {
        if (!$this->isTreated) {
            $population = $this->getPopulation();
            $this->initFetchValues($population);
            $this->isTreated = TRUE;
            if ($this->isActive()) {
                $this->validFetchValues();
            }
        }
        return $this;
    }

    public function isActive()
    {
        $this->treat();
        foreach ($this->inputs as $input) {
            if ($input->isActive) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function isValid()
    {
        $this->treat();
        foreach ($this->inputs as $input) {
            if (!$input->isValid()) {
                return FALSE;
            }
        }
        return TRUE;
    }


    /* INPUT GETTER METHODS
     *************************************************************************/
    public function __get($name)
    {
        return $this->getInputValue($name);
    }

    public function getInputValue($name)
    {
        $input = $this->getInput($name);
        return $input->getValue();
    }

    public function getInputError($name)
    {
        $input = $this->getInput($name);
        return $input->error;
    }

    public function getInputErrorMessage($name, $label=NULL)
    {
        $error = $this->getInputError($name);
        if ($error) {
            if (isset($this->lang[$error])) {
                $message = $this->lang[$error];
            } else if (isset($this->lang['default'])) {
                $message = $this->lang['default'];
            } else {
                $message = 'The field is not valid';
            }
            if (is_null($label)) {
                $label = $name;
            }
            return sprintf($message, $label);
        }
    }

    public function isInputValid($name)
    {
        $this->treat();
        $input = $this->getInput($name);
        return $input->isValid();
    }


    /* PROTECTED METHODS
     *************************************************************************/
    protected function getPopulation($populationType = NULL)
    {
        if (is_null($populationType)) {
            $populationType = $this->populationType;
        }
        if ($populationType == self::INPUT_ARRAY) {
            return $this->population;
        }
        return filter_input_array($populationType);
    }

    protected function initFetchValues($population)
    {
        foreach ($this->inputs as $input) {
            $input->initFetchValue($population);
        }
    }

    protected function validFetchValues()
    {
        foreach ($this->inputs as $input) {
            $input->validFetchValue();
        }
    }

    protected function getInput($name)
    {
        if (!isset($this->inputs[$name])) {
            throw new \RuntimeException('Try to get an unknown input: ' . $name);
        }
        return $this->inputs[$name];
    }
}