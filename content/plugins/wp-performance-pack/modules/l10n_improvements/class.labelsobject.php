<?php
/**
 * Class for translating strings "on demand".
 * Instead filling arrays with directly translated strings, LabelsObject executes
 * the translation only when the string is accessed, i.e. used. This prevents unnecessary
 * calls to translation functions for strings which are never used on a page.
 *
 * LabelsObject mimics an arry by implementing ArrayAccess so objects of LabelsObject can be
 * used like arrays of strings. This way usage of LabelsObject doesn't break any existing
 * code which uses arrays of strings.
 *
 * @author Björn Ahrens
 * @since 0.2.3
 */
 
class LabelsObject implements ArrayAccess, Iterator {

	private $input = array();
	private $output = array();
	public $default_translate_func = NULL;

	public function __construct( $args = NULL ) {
		$params = func_get_args ();
		if ( isset ( $params[0] ) && is_callable ( $params[0] ) ) {
			$this->default_translate_func = $params[0];
			$params = array_splice( $params, 1 );
		}
		if (isset ( $params[0] ) && ( is_array ( $params[0] ) || $params[0] instanceof LabelsObject) ) {
			$this->merge( $params[0] );
		}
	}

	/* ArrayAccess methods */

	public function offsetExists ( $offset ) {
		return ( isset( $this->input[$offset] ) || isset( $this->output[$offset] ) );
	}

	public function offsetGet ( $offset ) {
		if ( !isset( $this->output[$offset] ) && isset( $this->input[$offset] ) ) {
			/* if outpt is not set but input is set there are only two possible options:
			   1. input is a string to be translated by default_translate_func
			   2. input contains a function call
			   no need to check other options */
			if ( is_string( $this->input[$offset] ) ) {
				$func = $this->default_translate_func;
				$this->output[$offset] = $func ( $this->input[$offset] );
				$this->input[$offset] = NULL;
			} else {
				$func = reset ( $this->input[$offset] );
				if ( isset( $this->input[$offset][1] ) && is_array( $this->input[$offset][1] ) ) {
					// array ( function , array ( params ) )
					$this->output[$offset] = call_user_func_array( $func, $this->input[$offset][1] );
				} else if ( isset( $this->input[$offset][2] ) ) {
					// array ( function, param1, param2 )
					$this->output[$offset] = $func ( $this->input[$offset][1], $this->input[$offset][2] );
				} else if ( isset( $this->input[$offset][1] ) ) {
					// array (function, param1 )
					$this->output[$offset] = $func ( $this->input[$offset][1] );
				} else {
					// array ( function ) / array ( 'func' => closure )
					$this->output[$offset] = $func ();
				}
				$this->input[$offset] = NULL;
			}
		}
		
		if (isset ($this->output[$offset]))
			return $this->output[$offset];
		else
			return NULL;
	}

	public function offsetSet ( $offset , $value ) {
		if ( $this->default_translate_func !== NULL && is_string( $value ) ) {
			// if $value is a string and a default translate function is defined then translate on later access
			$this->input[$offset] = $value;
		} else if ( is_array( $value ) && reset( $value ) && is_callable( current( $value ) ) ) {
			// if $value is an array and it's first element is a function/closure then call it on later access
			$this->input[$offset] = $value;
		} else {
			// else store $value directly as value for this offset
			$this->input[$offset] = NULL;
			$this->output[$offset] = $value;
		}
	}

	public function offsetUnset ( $offset ) {
		unset( $this->input[$offset] );
		unset( $this->output[$offset] );
	}

	public function __get( $name ) {
		return $this->offsetGet( $name );
	}

	/* Iterator methods */

	public function current () {
		return$this->offsetGet(key($this->input));
	}

	public function key () {
		return key($this->input);
	}

	public function next () {
		next($this->input);
	}

	public function rewind () {
		reset($this->input);
	}

	public function valid () {
		return $this->current()!=false;
	}

	/* Other methos */

	public function merge ( $labels ) {
		if ( $labels instanceof LabelsObject ) {
			foreach ( $labels->output as $key => $value ) {
				$this->output[$key] = $value;
			}
			foreach ( $labels->input as $key => $value ) {
				if ( is_string ( $value ) ) {
					// a pure string only gets saved to input, if default_translate_func is specified
					if ( $labels->default_translate_func == $this->default_translate_func ) {
						$this->input[$key] = $value;
					} else {
						$this->input[$key] = array ( $labels->default_translate_func, $value );
					}
				} else {
					$this->input[$key] = array( $value );
				}
			}
		} else {
			foreach ( $labels as $key => $value ) {
				$this[$key] = $value;
			}
		}
	}

	public function keys () {
		return array_merge( array_keys( $this->input ), array_keys( $this->output ) );
	}
}

?>