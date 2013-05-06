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
    protected $options;
    protected $callback;


    /* CONSTRUCTOR METHODS
     *************************************************************************/
    public function __construct($name, $callback=NULL)
    {
        if (!static::$isInitialized) {
            $this->initializeExistingFilters();
        }
        $nameParts = explode(':', $name);
        $options = array_slice($nameParts,1);
        $name = $nameParts[0];
        if ($PHPFilterName = $this->getPHPFilterName($name)) {
            $this->isPHPFilter = TRUE;
            $name = $PHPFilterName;
        }
        $this->name = $name;
        $this->callback = $callback;
        $this->setOptions($options);
    }


    /* SETTER METHODS
     *************************************************************************/
    public function setOptions($options)
    {
        if (is_null($this->callback)) {
            if (is_string($options)) {
                $options = explode(':', $options);
            } else {
                $options = array_values($options);
            }
            if ($this->isPHPFilter) {

                // SPECIFIC PHP FILTER
                if (isset(static::$filters[$this->name])) {
                    $this->callback = $this->name;
                    $this->options = $options;
                }

                // GENERIC PHP FILTER
                else {
                    $this->callback = 'php';
                    array_unshift($options, $this->name);
                    $this->options = $options;
                }
            }

            // DEFINED FILTER
            else {
                $this->callback = $this->name;
                $this->options = $options;
            }
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
        if (is_string($this->callback)) {
            if (!isset(static::$filters[$this->callback])) {
                throw new \RuntimeException('Filter not callable: ' . $this->name);
            }
            $factory = static::$filters[$this->callback];
            $filter = $factory($this->options);
        } else {
            $filter = $this->callback;
        }
        return $filter($field);
    }


    /* PROTECTED METHODS
     *************************************************************************/
    protected function getPHPFilterName($id)
    {
        foreach (filter_list() as $name) {
            if (filter_id($name) == $id) {
                return $name;
            }
        }
        if (filter_id($id) === FALSE) {
            return FALSE;
        }
        return $id;
    }


    /* STATIC METHODS
     *************************************************************************/
    public function initializeExistingFilters()
    {
        static::addFilterDefinition('required', array($this, 'filterRequired'));
        static::addFilterDefinition('boolean', array($this, 'filterBoolean'));
        static::addFilterDefinition('validate_regexp', array($this, 'filterValidateRegexp'));
        static::addFilterDefinition('php', array($this, 'filterPHP'));
        static::addFilterDefinition('max_length', array($this, 'filterMaxLength'));
        static::addFilterDefinition('min_length', array($this, 'filterMinLength'));
        static::$isInitialized = TRUE;
    }

    public static function addFilterDefinition($name, $callback)
    {
        static::$filters[$name] = $callback;
    }


    /* CUSTOM FILTER METHODS
     *************************************************************************/
    public static function filterPHP($options)
    {
        if (!count($options)) {
            throw new \RuntimeException('Missing mandatory option: filter name');
        }
        $name = $options[0];
        return function (&$field) use ($name) {
            $filtered = filter_var($field, filter_id($name));
            if ($filtered === FALSE) {
                return FALSE;
            }
            $field = $filtered;
            return TRUE;
        };
    }

    public static function filterValidateRegexp($options)
    {
        if (!count($options)) {
            throw new \RuntimeException('Missing mandatory option: regexp');
        }
        $regexp = $options[0];
        return function (&$field) use ($regexp) {
            $options = ['options'=>['regexp' => $regexp]];
            $filtered = filter_var($field, FILTER_VALIDATE_REGEXP, $options);
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

    public static function filterMaxLength($options)
    {
        if (!count($options)) {
            throw new \RuntimeException('Missing mandatory option: maxLength');
        }
        $maxLength = $options[0];
        return function ($field) use ($maxLength) {
            if (is_null($maxLength)) {
                $maxLength = '255';
            }
            return (strlen($field) <= $maxLength);
        };
    }

    public static function filterMinLength($options)
    {
        if (!count($options)) {
            throw new \RuntimeException('Missing mandatory option: minLength');
        }
        $minLength = $options[0];
        return function ($field) use ($minLength) {
            if (is_null($minLength)) {
                $minLength = '8';
            }
            return (strlen($field) >= $minLength);
        };
    }
}