<?php

/* This file is part of the Eloq project, which is under MIT license */

namespace Pixel418\Eloq\Stack\Util;

class FormInputFilter
{


    /* ATTRIBUTES
     *************************************************************************/
    static $filters = array();
    static $isInitialized = FALSE;
    protected $name;
    protected $isPHPFilter = FALSE;
    protected $option;
    protected $callback;


    /* CONSTRUCTOR METHODS
     *************************************************************************/
    public function __construct($name)
    {
        if (!static::$isInitialized) {
            $this->initializeExistingFilters();
        }
        $option = NULL;
        if (\UString::has($name, ':')) {
            $option = \UString::substrAfterLast($name, ':');
            \UString::doSubstrBeforeLast($name, ':');
        }
        if ($this->isPHPFilter($name)) {
            $this->isPHPFilter = TRUE;
            $name = $this->getPHPFilterName($name);
        }
        $this->name = $name;
        $this->setOption($option);
    }


    /* SETTER METHODS
     *************************************************************************/
    public function setOption($option)
    {
        if ($this->isPHPFilter) {

            // SPECIFIC PHP FILTER
            if (isset(static::$filters[$this->name])) {
                $this->callback = $this->name;
                $this->option = $option;
            }

            // GENERIC PHP FILTER
            else {
                $this->callback = 'php';
                $this->option = $this->name;
            }
        }

        // CUSTOM FILTER
        else {
            $this->callback = $this->name;
            $this->option = $option;
        }
    }


    /* GETTER METHODS
     *************************************************************************/
    public function getName()
    {
        return $this->name;
    }

    public function apply(&$field)
    {
        if (!isset(static::$filters[$this->callback])) {
            throw new \Exception('Filter not callable: ' . $this->name);
        }
        $factory = static::$filters[$this->callback];
        $filter = $factory($this->option);
        return $filter($field);
    }


    /* PROTECTED METHODS
     *************************************************************************/
    protected function isPHPFilter(&$name)
    {
        if (is_string($name)) {
            return (filter_id($name) !== FALSE);
        }
        if (is_int($name)) {
            return ($this->getPHPFilterName($name) !== FALSE);
        }
        return FALSE;
    }

    protected function getPHPFilterName($id)
    {
        foreach (filter_list() as $name) {
            if (filter_id($name) == $id) {
                return $name;
            }
        }
        return FALSE;
    }


    /* STATIC METHODS
     *************************************************************************/
    public function initializeExistingFilters()
    {
        static::addFilterDefinition('required', array($this, 'filterRequired'));
        static::addFilterDefinition('boolean', array($this, 'filterBoolean'));
        static::addFilterDefinition('php', array($this, 'filterPHP'));
        static::addFilterDefinition('maxLength', array($this, 'filterMaxLength'));
        static::addFilterDefinition('minLength', array($this, 'filterMinLength'));
        static::$isInitialized = TRUE;
    }

    public static function addFilterDefinition($name, $callback)
    {
        static::$filters[$name] = $callback;
    }


    /* CUSTOM FILTER METHODS
     *************************************************************************/
    public static function filterPHP($name)
    {
        return function (&$field) use ($name) {
            $filtered = filter_var($field, filter_id($name));
            if ($filtered === FALSE) {
                return FALSE;
            }
            $field = $filtered;
            return TRUE;
        };
    }

    public static function filterBoolean()
    {
        return function (&$field) {
            $options = ['flags' => FILTER_NULL_ON_FAILURE];
            $filtered = filter_var($field, FILTER_VALIDATE_BOOLEAN, $options);
            if ($filtered === NULL) {
                return FALSE;
            }
            $field = $filtered;
            return TRUE;
        };
    }

    public static function filterRequired()
    {
        return function ($field) {
            return (!is_null($field) && $field !== '');
        };
    }

    public static function filterMaxLength($maxLength)
    {
        return function ($field) use ($maxLength) {
            if (is_null($maxLength)) {
                $maxLength = '255';
            }
            return (strlen($field) <= $maxLength);
        };
    }

    public static function filterMinLength($minLength)
    {
        return function ($field) use ($minLength) {
            if (is_null($minLength)) {
                $minLength = '8';
            }
            return (strlen($field) >= $minLength);
        };
    }
}