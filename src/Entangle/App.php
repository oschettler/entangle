<?php namespace Entangle;

class App
{
	/**
	 * Similar to dispatch.scope, but keep values as stack
	 */
	public static function stack($name, $value = null)
	{

		static $_stash = array();

		if ($value === null) {
			return isset($_stash[$name]) ? array_pop($_stash[$name]) : NULL;
		}

		if (!isset($_stash[$name])) {
			$_stash[$name] = array();
		}
		return array_push($_stash[$name], $value);
	}


}