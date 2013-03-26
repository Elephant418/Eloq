<?php

/* This file is part of the Eloq project, which is under MIT license */

namespace Pixel418\Eloq\Stack\Util;

class FormHelper
{


    /*************************************************************************
    ATTIBUTES
     *************************************************************************/
    protected $fields = array();
    protected $values = array();
    protected $filters = array();
    protected $errors = array();
    protected $isTreated = FALSE;
    protected $isActive = FALSE;


    /*************************************************************************
    FIELD METHODS
     *************************************************************************/
    public function addField($name, $address = NULL)
    {
        if (is_null($address)) {
            $address = $name;
        }
        $this->fields[$name] = $address;
        $this->values[$name] = NULL;
        $this->filters[$name] = array();
        return $this;
    }

    public function removeField($name)
    {
        unset($this->fields[$name]);
        unset($this->values[$name]);
        unset($this->filters[$name]);
        return $this;
    }

    public function moveField($name, $address = NULL)
    {
        return $this->addField($name, $address);
    }


    /*************************************************************************
    FIELD METHODS
     *************************************************************************/
    public function addFilter($fieldName, $filterName, $error = 'Field invalid', $options = array())
    {
        if (!isset($this->fields[$fieldName])) {
            throw new \Exception('Unknown field: '.$name);
        }
        $FormFilterClass = \UObject::getNamespace($this) . '\\FormFilter';
        $filter = new $FormFilterClass($filterName, $error, $options);
        $this->filters[$fieldName][$filterName] = $filter;
        return $this;
    }

    public function setFilterError($fieldName, $filterName, $error)
    {
        $this->getFilter($fieldName, $filterName)
            ->setError($error);
        return $this;
    }

    public function setFilterOptions($fieldName, $filterName, $options)
    {
        $this->getFilter($fieldName, $filterName)
            ->setOptions($options);
        return $this;
    }

    public function removeFilter($fieldName, $filterName)
    {
        unset($this->filters[$fieldName][$filterName]);
        return $this;
    }


    /*************************************************************************
    GETTER METHODS
     *************************************************************************/
    public function isActive()
    {
        $this->treat();
        return $this->isActive;
    }

    public function isValid($field = NULL)
    {
        if (func_num_args() == 0) {
            return $this->isAllValid();
        }
        return $this->isFieldValid($field);
    }

    public function isFieldValid($field)
    {
        $errors = $this->getFieldErrors($field);
        return (count($errors) == 0);
    }

    public function isAllValid()
    {
        $errors = $this->getAllErrors();
        return (count($errors) == 0);
    }

    public function get($field = NULL)
    {
        if (func_num_args() == 0) {
            return $this->getAllValues();
        }
        return $this->getFieldValue($field);
    }

    public function getFieldValue($field)
    {
        $this->treat();
        if (isset($this->values[$field])) {
            return $this->values[$field];
        }
        return NULL;
    }

    public function getAllValues()
    {
        $this->treat();
        return $this->values;
    }

    public function getErrors($field = NULL)
    {
        if (func_num_args() == 0) {
            return $this->getAllErrors();
        }
        return $this->getFieldErrors($field);
    }

    public function getFieldErrors($field)
    {
        $this->treat();
        if (isset($this->errors[$field])) {
            return $this->errors[$field];
        }
        return array();
    }

    public function getAllErrors()
    {
        $this->treat();
        return $this->errors;
    }


    /*************************************************************************
    PRIVATE METHODS
     *************************************************************************/
    protected function initSubmittedValues()
    {
        if ($this->isTreated) {
            return NULL;
        }
        foreach ($this->fields as $name => $path) {
            if (\UArray::hasDeepSelector($_POST, $path)) {
                $this->isActive = TRUE;
                $value = \UArray::getDeepSelector($_POST, $path);
                $this->values[$name] = $value;
            }
        }
    }

    protected function getFilter($fieldName, $filterName) {
        if (!isset($this->fields[$fieldName])) {
            throw new \Exception('Unknown field: '.$name);
        }
        if (!isset($this->filters[$fieldName][$filterName])) {
            throw new \Exception('Filter not found: ' . $filterName . ' on ' . $fieldName);
        }
        return $this->filters[$fieldName][$filterName];
    }

    protected function treat()
    {
        if ($this->isTreated) {
            return NULL;
        }
        $this->initSubmittedValues();
        $this->isTreated = TRUE;
        if (!$this->isActive) {
            return NULL;
        }
        foreach ($this->filters as $fieldName => $filters) {
            foreach ($filters as $filterName => $filter) {
                $value =& $this->values[$fieldName];
                if (!$filter->call($value)) {
                    $this->errors[$fieldName][$filterName] = $filter->getError();
                    break;
                }
            }
        }
    }
}