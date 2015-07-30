<?php
namespace SlimPay;

class Cache {
	private static $dir = 'cache';
	private static $enabled = false;
	
	public static function getDir() {
		return self::$dir;
	}
	
	public static function setDir($dir) {
		self::$dir = $dir;
	}
	
	public static function enable() {
		self::$enabled = true;
	}
	
	public static function disable() {
		self::$enabled = false;
	}
	
	private static function isEnabled() {
		return self::$enabled === true;
	}
	
	/**
	 * @param	$file	The name of the file including its path
	 *
	 * @return	The content of the file if it exists, is readable
	 *			and the cache is activated
	 */
	public static function read($file) {
		if (self::isEnabled() && is_readable(self::$dir . $file))
			return unserialize(file_get_contents(self::$dir . $file));
		else
			return null;
	}
	
	/**
	 * @param	$file		The name of the file including its path
	 * @param	$content	The content to write (must be serializable)
	 */
	public static function save($file, $content) {
		$dirname = dirname(self::$dir . $file);
		if (self::isEnabled() && (is_dir($dirname) || mkdir($dirname, 0770, true)))
			@file_put_contents(self::$dir . $file, serialize($content));
	}
	
	/**
	 * @param	$dir		The path of the cache folder
	 * @param	$prefix		The prefix of the files to delete
	 *						(use the full name to delete one file)
	 */
	public static function delete($dir = null, $prefix = 'all') {
		if ($dir == null)
			$dir = self::$dir;
		
		if (self::isEnabled())
			foreach (scandir($dir) as $file)
				if (!in_array($file, array('.', '..')) && ($prefix == 'all' || strpos($file, $prefix) === 0))
					if (is_dir($dir . $file)) {
						self::delete($dir . $file . '/', $prefix);
						rmdir($dir . $file);
					} else
						unlink($dir . $file);
	}
}

?>