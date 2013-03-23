<?php

/* This file is part of the Eloq project, which is under MIT license */

namespace Pixel418\Eloq\Stack\Util;

class FormHelper {



	/*************************************************************************
	  ATTIBUTES
	 *************************************************************************/
	protected $fields = array( );
	protected $values = array( );
	protected $filters = array( );
	protected $messages = array( );
	protected $isTreated = FALSE;
	protected $isActive = FALSE;
	protected $isValid = FALSE;



	/*************************************************************************
	  FIELD METHODS
	 *************************************************************************/
	public function addField( $name, $address = NULL ) {
        if ( is_null( $address ) ) {
            $address = $name;
        }
        $this->fields[ $name ] = $address;
		$this->values[ $name ] = NULL;
        $this->filters[ $name ] = array( );
        $this->messages[ $name ] = array( );
		return $this;
	}
	
	public function removeField( $name ) {
        unset( $this->fields[ $name ] );
        unset( $this->values[ $name ] );
        unset( $this->filters[ $name ] );
        unset( $this->messages[ $name ] );
		return $this;
	}

    public function moveField( $name, $address = NULL ) {
        return $this->addField( $name, $address );
    }



    /*************************************************************************
    FIELD METHODS
     *************************************************************************/
    public function addFilter( $fieldName, $filterName, $message = 'Field invalid', $options = array( ) ) {
        $filter = new FormFilter( $filterName ,$message, $options );
        $this->filters[ $fieldName ][ $filterName ] = $filter;
        return $this;
    }

    public function removeFilter( $fieldName, $filterName ) {
        unset( $this->filters[ $fieldName ][ $filterName ] );
        return $this;
    }



	/*************************************************************************
	  GETTER METHODS
	 *************************************************************************/
    public function isActive( ) {
        $this->treat( );
        return $this->isActive;
    }

    public function isValid( ) {
		$this->treat( );
		return $this->isValid;
	}

	public function getFieldValue( $field ) {
		$this->treat( );
        if ( isset( $this->values[ $field ] ) ) {
            return $this->values[ $field ];
        }
		return NULL;
	}

    public function getValues( ) {
        $this->treat( );
        return $this->values;
    }

    public function getFieldMessages( $field ) {
        $this->treat( );
        if ( isset( $this->messages[ $field ] ) ) {
            return $this->messages[ $field ];
        }
        return array( );
    }

	public function getMessages( ) {
		$this->treat( );
		return $this->messages;
	}



	/*************************************************************************
	  PRIVATE METHODS
	 *************************************************************************/
	protected function initSubmitedValues( ) {
		if ( $this->isTreated ) {
			return NULL;
		}
		foreach( $this->fields as $name => $path ) {
			if ( \UArray::hasDeepSelector( $_POST, $path ) ) {
				$this->isActive = TRUE;
				$value = \UArray::getDeepSelector( $_POST, $path );
				$this->values[ $name ] = $value;
			}
		}
	}
	protected function treat( ) {
		if ( $this->isTreated ) {
			return NULL;
		}
		$this->initSubmitedValues( );
		$this->isTreated = TRUE;
		if ( ! $this->isActive ) {
			return NULL;
		}
		$this->isValid = TRUE;
		foreach( $this->filters as $fieldName => $filters ) {
			foreach( $filters as $filterName => $filter ) {
                $value =& $this->values[ $fieldName ];
                $filter->call( $value );
                if ( $value === NULL ) {
                    $this->messages[ $fieldName ][ $filterName ] = $filter->getMessage( );
                    $this->isValid = FALSE;
                }
			}
		}
	}
}