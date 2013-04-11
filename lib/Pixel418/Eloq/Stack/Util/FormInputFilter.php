<?php

/* This file is part of the Eloq project, which is under MIT license */

namespace Pixel418\Eloq\Stack\Util;

class FormInputFilter
{


    /*************************************************************************
    ATTRIBUTES
     *************************************************************************/
    static $filters = array();
    static $isInitialized = FALSE;
    protected $name;
    protected $callback;
    protected $error;


    /*************************************************************************
    CONSTRUCTOR METHODS
     *************************************************************************/
    public function __construct($name)
    {
        if (!static::$isInitialized) {
            $this->initializeExistingFilters();
        }
        $this->name = $name;
        $this->setError($error);
        $this->setOptions($options);
    }


    /*************************************************************************
    STATIC METHODS
     *************************************************************************/
    public function initializeExistingFilters()
    {
        static::addCustomFilter('required', array($this, 'filterRequired'));
        static::addCustomFilter('max_length', array($this, 'filterMaxLength'));
        static::addCustomFilter('min_length', array($this, 'filterMinLength'));
        static::$isInitialized = TRUE;
    }

    public static function addCustomFilter($name, $callback)
    {
        static::$filters[$name] = $callback;
    }


    /*************************************************************************
    GETTER METHODS
     *************************************************************************/
    public function call(&$field)
    {
        $callback = $this->callback;
        if (!is_callable($callback)) {
            throw new \Exception('Filter not callable: ' . $this->name);
        }
        return $callback($field);
    }

    public function getError()
    {
        return $this->error;
    }


    /*************************************************************************
    SETTER METHODS
     *************************************************************************/
    public function setError($error)
    {
        $this->error = $error;
        return $this;
    }

    public function setOptions($options)
    {
        if ($this->isPHPFilter($this->name)) {
            $callback = $this->getPHPFilter($this->name, $options);
        } else if ($this->isCustomFilter($this->name)) {
            $callback = $this->getCustomFilter($this->name, $options);
        } else {
            $callback = $this->getDefaultFilter();
        }
        $this->callback = $callback;
    }


    /*************************************************************************
    PROTECTED METHODS
     *************************************************************************/
    public function isPHPFilter(&$id)
    {
        if (is_string($id)) {
            return (filter_id($id) !== FALSE);
        }
        if (is_int($id)) {
            foreach (filter_list() as $name) {
                if (filter_id($name) == $id) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    public function getPHPFilter($id, $options)
    {
        if (is_string($id)) {
            $id = filter_id($id);
        }
        return function (&$field) use ($id, $options) {
            $options=array('options'=>$options);
            if ($id === FILTER_VALIDATE_BOOLEAN) {
                $options['flags'] = FILTER_NULL_ON_FAILURE;
            }
            $filtered = filter_var($field, $id, $options);
            if ($id !== FILTER_VALIDATE_BOOLEAN && $filtered === FALSE) {
                return FALSE;
            }
            if ($id === FILTER_VALIDATE_BOOLEAN && $filtered === NULL) {
                return FALSE;
            }
            $field = $filtered;
            return TRUE;
        };
    }

    public function isCustomFilter($name)
    {
        return isset(static::$filters[$name]);
    }

    public function getCustomFilter($name, $options)
    {
        $callback = static::$filters[$name];
        if (!is_callable($callback)) {
            throw new \Exception('Filter factory not callable: ' . $name);
        }
        return $callback($options);
    }

    public function getDefaultFilter()
    {
        return function ($field) {
            return $field;
        };
    }


    /*************************************************************************
    CUSTOM FILTER METHODS
     *************************************************************************/
    public static function filterRequired()
    {
        return function ($field) {
            return (!is_null($field) && $field !== '');
        };
    }

    public static function filterMaxLength($options)
    {
        return function ($field) use ($options) {
            if (!isset($options['length'])) {
                $options['length'] = '32';
            }
            return (strlen($field) <= $options['length']);
        };
    }

    public static function filterMinLength($options)
    {
        return function ($field) use ($options) {
            if (!isset($options['length'])) {
                $options['length'] = '8';
            }
            return (strlen($field) >= $options['length']);
        };
    }
}