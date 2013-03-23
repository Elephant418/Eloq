<?php

/* This file is part of the Eloq project, which is under MIT license */

namespace Pixel418\Eloq\Stack\Util;

class FormFilter {



	/*************************************************************************
	  ATTIBUTES
	 *************************************************************************/
    static $filters = array( );
    static $isInitialized = FALSE;
	protected $callback;
	protected $errorMessage;



	/*************************************************************************
	  CONSTRUCTOR METHODS
	 *************************************************************************/
	public function __construct( $name, $errorMessage, $options ) {
        if ( ! static::$isInitialized ) {
            $this->initializeExistingFilters( );
        }
        if ( $this->isPHPFilter( $name ) ) {
            $callback = $this->getPHPFilter( $name, $options );
        } else if ( $this->isCustomFilter( $name ) ) {
            $callback = $this->getCustomFilter( $name, $options );
        } else {
            $callback = $this->getDefaultFilter( );
        }
        $this->errorMessage = $errorMessage;
        $this->callback = $callback;
    }



    /*************************************************************************
    STATIC METHODS
     *************************************************************************/
    public static function initializeExistingFilters( ) {
        static::addCustomFilter( 'required', function( ) {
            return function( $field ) {
                if ( ! is_null( $field ) &&  $field != '' ) {
                    return $field;
                }
            };
        } );
        static::$isInitialized = TRUE;
    }
    public static function addCustomFilter( $name, $callback ) {
        static::$filters[ $name ] = $callback;
    }



    /*************************************************************************
    GETTER METHODS
     *************************************************************************/
    public function call( &$field ) {
        $callback = $this->callback;
        $field = $callback( $field );
    }
    public function getMessage( ) {
        return $this->errorMessage;
    }



    /*************************************************************************
    PROTECTED METHODS
     *************************************************************************/
    public function isPHPFilter( &$id ) {
        if ( is_string( $id ) ) {
            return ( filter_id( $id ) !== FALSE );
        }
        if ( is_int( $id ) ) {
            foreach( filter_list() as $name ) {
                if ( filter_id( $name ) == $id ) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    public function getPHPFilter( $id, $options ) {
        if ( is_string( $id ) ) {
            $id = filter_id( $id );
        }
        return function( $field ) use ( $id, $options ) {
            if ( $id === FILTER_VALIDATE_BOOLEAN ) {
                $options[ 'flags' ] = FILTER_NULL_ON_FAILURE;
            }
            $filtered =  filter_var( $field, $id, $options );
            if ( $id !== FILTER_VALIDATE_BOOLEAN && $filtered === FALSE ) {
                $filtered = NULL;
            }
            return $filtered;
        };
    }
    public function isCustomFilter( $name ) {
        return isset( static::$filters[ $name ] );
    }
    public function getCustomFilter( $name, $options ) {
        $callback = static::$filters[ $name ];
        return $callback( $options );
    }
    public function getDefaultFilter( ) {
        return function( $field ) {
            return $field;
        };
    }
}