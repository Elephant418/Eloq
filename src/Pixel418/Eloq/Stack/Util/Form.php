<?php

/* This file is part of the Eloq project, which is under MIT license */

namespace Pixel418\Eloq\Stack\Util;

class Form
{


    /* ATTRIBUTES
     *************************************************************************/
    const INPUT_ARRAY = 418;
    static $defaultErrorMessages = [
        'required' => 'This field is required',
        'confirm' => 'This field does not match the previous one',
        'validate_regexp' => 'This field contains unauthorized characters',
        'validate_email' => 'This field must be a valid email',
        'validate_url' => 'This field must be a valid url',
        'validate_ip' => 'This field must be a valid IP',
        'max_length' => 'This field must have %s characters at maximum',
        'min_length' => 'This field must have %s characters at minimum',
        'default' => 'This field is not valid'
    ];
    private $namespace;
    protected $errorMessages;
    protected $population;
    protected $populationType;
    protected $inputs = array();
    protected $isTreated = FALSE;


    /* CONSTRUCTOR
     *************************************************************************/
    public function __construct($populationType = INPUT_POST)
    {
        $this->errorMessages = static::$defaultErrorMessages;
        $this->setPopulationType($populationType);
        $this->namespace = \UObject::getNamespace($this);
    }


    /* FORM SETTER METHODS
     *************************************************************************/
    public function setPopulationType($populationType)
    {
        $this->populationType = $populationType;
        return $this;
    }

    public function setPopulation($population)
    {
        $this->setPopulationType(self::INPUT_ARRAY);
        $this->population = $population;
        return $this;
    }

    public function addInput($name, $filters = NULL, $address = NULL, $populationType = NULL)
    {
        $inputClass = $this->namespace . '\\FormInput';
        $input = new $inputClass($name);
        $this->inputs[$name] = $input;
        if ($filters) {
            $this->addInputFilterList($name, $filters);
        }
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

    public function addInputFilter($inputName, $filterName, $filterCallback = NULL, $errorMessage = NULL)
    {
        if (is_null($errorMessage) && is_string($filterCallback) && \UString::has($filterCallback, ' ')) {
            $errorMessage = $filterCallback;
            $filterCallback = NULL;
        }
        $input = $this->getInput($inputName);
        $filterClass = $this->namespace . '\\FormInputFilter';
        $filter = new $filterClass($filterName, $filterCallback);
        $input->filters[$filter->getName()] = $filter;
        if (!is_null($errorMessage)) {
            $filter->errorMessage = $errorMessage;
        }
        return $this;
    }

    public function addInputFilterList($inputName, $filters, $errorMessage = NULL)
    {
        $filters = explode('|', $filters);
        foreach ($filters as $filterName) {
            $this->addInputFilter($inputName, $filterName, $errorMessage);
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

    public function setInputFilterOptions($inputName, $filterName, $options)
    {
        $filter = $this->getInputFilter($inputName, $filterName);
        $filter->setOptions($options);
        return $this;
    }

    public function setInputFilterErrorMessage($inputName, $filterName, $errorMessage)
    {
        $filter = $this->getInputFilter($inputName, $filterName);
        $filter->errorMessage = $errorMessage;
        return $this;
    }


    /* FORM GETTER METHODS
     *************************************************************************/
    public function treat()
    {
        if (!$this->isTreated) {
            $this->initFetchValues();
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
        if (!$this->isActive()) {
            return FALSE;
        }
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

    public function get($name)
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

    public function getInputErrorMessage($inputName)
    {
        $input = $this->getInput($inputName);
        $error = $input->error;
        if ($error) {
            $filter = $this->getInputFilter($inputName, $error);
            if (!is_null($filter->errorMessage)) {
                $errorMessage = $filter->errorMessage;
            } else if (isset($this->errorMessages[$error])) {
                $errorMessage = $this->errorMessages[$error];
            } else if (isset($this->errorMessages['default'])) {
                $errorMessage = $this->errorMessages['default'];
            } else {
                $errorMessage = 'The field is not valid';
            }
            $options = $filter->getOptions();
            return vsprintf($errorMessage, $options);
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
        if ($populationType === self::INPUT_ARRAY) {
            return $this->population;
        }
        return filter_input_array($populationType);
    }

    protected function initFetchValues()
    {
        foreach ($this->inputs as $input) {
            $population = $this->getPopulation($input->populationType);
            if (\UArray::hasDeepSelector($population, $input->address)) {
                $input->isActive = TRUE;
                $input->fetchValue = \UArray::getDeepSelector($population, $input->address);
            }
        }
    }

    protected function validFetchValues()
    {
        foreach ($this->inputs as $input) {
            foreach ($input->filters as $filterName => $filter) {
                if ($filter->apply($input->fetchValue, $this) === FALSE) {
                    $input->error = $filterName;
                    break;
                }
            }
        }
    }

    protected function getInput($name)
    {
        if (!isset($this->inputs[$name])) {
            throw new \RuntimeException('Try to get an unknown input: '.$name);
        }
        return $this->inputs[$name];
    }

    public function getInputFilter($inputName, $filterName)
    {
        $input = $this->getInput($inputName);
        if (!isset($input->filters[$filterName])) {
            throw new \RuntimeException('Try to get an unknown filter: '.$filterName.' on '.$inputName);
        }
        return $input->filters[$filterName];
    }


    /* ERROR MESSAGES METHODS
     *************************************************************************/
    public function addErrorMessage($name, $message)
    {
        $this->errorMessages[$name] = $message;
        return $this;
    }

    public function addErrorMessageMap($map)
    {
        foreach($map as $name => $message) {
            $this->errorMessages[$name] = $message;
        }
        return $this;
    }

    public static function defineErrorMessage($name, $message)
    {
        static::$defaultErrorMessages[$name] = $message;
    }

    public static function defineErrorMessageMap($map)
    {
        foreach($map as $name => $message) {
            static::$defaultErrorMessages[$name] = $message;
        }
    }
}