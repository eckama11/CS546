<?php

class GetterSetter {

	/**
	 * If a property is requested that is not explicitly defined, call any existing getter method for the property.
	 *
	 * If a getter method is found matching the property name, that method will be called, and the result returned.
	 * If no matching getter is found, an exception will be thrown.
	 *
	 * For a method to be considered a getter for a property, it must be named as follows:
	 * The method name must start with "get", followed by the name of the property with the
	 * first letter of the property upper-cased.
	 *
	 * For example:
	 * Given a property "name", the corresponding get method would be named "getName".
	 *
	 * @param	$propName	The name of the property to call the getter for.  If a method is found matching
	 *						the property name, it will be called, and the result returned.
	 *
	 * @return	The return value of the property getter.
	 */
	public function __get( $propName ) {
		// If a matching get method exists, call it
		$method = 'get'. ucfirst($propName);
		if (method_exists($this, $method)) {
			return $this->{$method}();
		} else {
			throw new Exception("Attempt to get unknown property '$propName' for object of class '". get_class($this) ."'");
		}
	} // __get( $propName )


	/**
	 * If an attempt is made to set a property that is not explicitly defined, call any existing setter method for the property.
	 *
	 * If a setter method is found matching the property name, that method will be called.
	 * If no matching getter is found, an exception will be thrown.
	 *
	 * For a method to be considered a setter for a property, it must be named as follows:
	 * The method name must start with "set", followed by the name of the property with the
	 * first letter of the property upper-cased.
	 *
	 * For example:
	 * Given a property "name", the corresponding set method would be named "setName".
	 *
	 * @param	$propName	The name of the property to call the setter for.  If a method is found matching
	 *						the property name, it will be called.
	 * @param	$value		The value to assign to the property.  This value will be passed to the setter method unchanged.
	 */
	public function __set( $propName, $value ) {
		// If a matching set method exists, call it
		$method = 'set'. ucfirst($propName);
		if (method_exists($this, $method)) {
			return $this->{$method}($value);
		} else {
			throw new Exception("Attempt to set unknown property '$propName' for object of class '". get_class($this) ."'");
		}
	} // __set( $propName, $value )

} // class GetterSetter
