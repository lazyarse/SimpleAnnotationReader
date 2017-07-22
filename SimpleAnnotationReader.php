<?php

namespace SimpleAnnotationReader;

/**
 * Get annotations for a class. 
 * This is a heavy class to run, so cache the data returned in production
 * 
 * annotations must:
 *  - start inside a comment block with a "/**" start and "*\/" end (no backslash) on separate lines
 *  - start with a @, e.g. @MyAnnotation, @PrimaryKey  etc
 *  - be placed on a new line
 * 
 * if an annotation has an arg list, they will be broken into an array
 * e.g. @Column(type=string, primary_key) will become:
 * ['Column' => ['type' => 'string', 'primary_key']]
 * 
 */
class AnnotationReader
{
	
	/**
	 * get annotation data for a class
	 */
	static public function getClassDocComment($className)
	{
		$rc = new \ReflectionClass($className);
		$comment = $rc->getDocComment();
		
		$meta = self::parse($comment);
		return $meta;
	}

	/**
	 * get annotation data for all properties / one property
	 */
	static public function getPropertyDocComments($className, $propertyName = null)
	{
		$rc = new \ReflectionClass($className);
		$props = $rc->getProperties();
		
		$data = [];
		foreach ($props as $prop)
		{
			$name = $prop->getName();

			if ($propertyName != null && $name != $propertyName)
				continue;
				
			$data[$prop->getName()] = self::parse($prop->getDocComment());
		}
		
		return $data;
	}
	
	/**
	 * this is the real heavy lifter of the reader class and parses the annotations
	 * into associative arrays
	 */
	private function parse($comment)
	{	
		// remove /** and */ at the start and end of the comment
		$comment = substr($comment, 3, -2);
			
		// regex to trim, and remove @ of line
		$regex = "#.*@(.*)\s*#";
		
		$parsed = [];
		
		// iterate over each line parsing it
		$lines = explode("\n", $comment);
		foreach ($lines as $line)
		{
			$line = trim($line);
			$line = preg_replace($regex, '$1', $line);
			
			if (strlen($line) == 0)
				continue;
			
			// if the annotation has a ( and ), then break the args out into an array
			// e.g. @Column(type=string, primary_key)
			// type=string will be keyed with 'type' and value 'string',
			// primary_key will be added to the array with a numerical key
			if (strpos($line, '(') !== false && strpos($line, ')') !== false)
			{
				// ascertain the name of the annotation
				$key = strtolower(trim(substr($line, 0, strpos($line, '('))));
				
				//die(":" . $line);
				// get args as string
				$startPos = strpos($line, '(') + 1;
				$length = strrpos($line, ')') - strlen($line);
				$argsAsString = substr($line, $startPos, $length);
				
				// explode and trim
				$args = explode(',', $argsAsString);
				$args = array_map(function($x){return trim($x);}, $args);
				
				// we iterate through each arg and check for foo=bar
				$cleanArgs = [];
				foreach ($args as $arg)
				{
					$arg = trim($arg);
					if (strlen($arg) === 0)
						continue;
						
					if (strpos($arg, '=') !== false)
					{
						$parts = explode('=', $arg);
						$cleanArgs[strtolower($parts[0])] = $parts[1];
					}
					else
						$cleanArgs[] = $arg;
				}
				
				$parsed[$key] = $cleanArgs;
			}
			else
			{
				$parsed[] = $line;
			}
		}
		
		return $parsed;
	}
}
