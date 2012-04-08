<?php //PATH is (P)HP (A)rrays (T)o (H)TML

//TODO: add in import tag (gets & adds file in place of tag)
//TODO: switch from php array syntax???
//TODO: fix issue with spaces between tags
//TODO: get selector library like sizzle for php

$pathOptions = [
	'selfClosingTags' => ['img', 'br', 'input', 'meta', 'link'],
	'extraSpace' => true,//if you want path to add extra space between tags (for browser compatibility)
	'indent' => true,
	'showErrors' => true,
	'manualNormalize' => false //increase performance by only running normalize functions when needed
];

function path(&$array){//wrapper function
	global $pathOptions;
	
	if(!$pathOptions['manualNormalize']) pathNormalize($array);
	//var_dump($array);

	$currentIndentation;
	return pathCompile($array);
}

function getIdAndClasses(&$array){//currently only called by path normalize (doesn't need to be seperate function)
	if(!is_string($array[0])){
		echo 'error: tag name is not string or attribute is array';
		return;
	}

	preg_match('/[^.#\n\s]*/', $array[0], $tagName);//get only element name
	
	//get id
	preg_match('/#([^.\n\s]*)/', $array[0], $id);//get only id name
	if(count($id) != 0 && empty($array['id'])) $array['id'] = $id[1];//this will ignore the shorthand if a id is defined normally
	
	//get classes
	preg_match_all('/\.([^.\n\s]*)/', $array[0], $classes);

	if(empty($array['class'])) $array['class'] = '';//make sure class is defined... will unset later if not classes

	if(!empty($classes[1])){
		$classes = $classes[1];
		$len = count($classes);
		for($i=0; $i < $len; $i++){
			$array['class'] .= ' ' . $classes[$i];
		}
	}

	$array['class'] = trim($array['class']);
	if(empty($array['class'])) unset($array['class']);

	$array[0] = empty($tagName[0]) ? 'div' : $tagName[0];//normalize and provide div default
}

function pathNormalize(&$array){
	getIdAndClasses($array);
	$newArray[0] = $array[0];
	unset($array[0]);

	reset($array);//need to eval once before loop start
	$each = each($array);

	while($each !== false){
		$key = $each['key'];//shorter

		if(is_callable($array[$key]) && gettype($array[$key]) == "object"){//process anonymous functions
			$array = array_merge($array, call_user_func($array[$key]));//TODO: fix
			unset($array[$key]);
		} else {
			//recursively call path to process nested tags
			if(is_array($array[$key])) pathNormalize($array[$key]);

			//will overwrite non-numberic keys (if declared twice), and append numberic ones - needed due to function returning values
			is_numeric($key) ? $newArray[] = $array[$key] : $newArray[$key] = $array[$key];
			unset($array[$key]);
		}

		$each = each($array);
		reset($array);
	}

	$array = $newArray;
}

function pathCompile($array){
	global $pathOptions;
	global $currentIndentation;

	$tagName = $array[0];
	unset($array[0]);

	//make a way to manually specify a self closing tag
	$isSelfClosing = in_array($tagName, $pathOptions['selfClosingTags']);

	if($pathOptions['indent']){
		$return = "\n" . $currentIndentation . '<' . $tagName;
		$currentIndentation = $currentIndentation . '	';
	} else {
		$return = '<' . $tagName;
	}

	if(!$isSelfClosing){//self closing tags can't have innerHTML
		$key = 1;
		$innerHTML = '';

		while(array_key_exists($key, $array)){
			if(is_array($array[$key])){//recursively call path to process nested tags
				if($pathOptions['indent']) $containsNestedTags = true;//used for end tag
				if($pathOptions['extraSpace'] && !$pathOptions['indent']){//indent must be false, otherwise the extra space would be useless
					$innerHTML .= ' ' . pathCompile($array[$key]) . ' ';
				} else {
					$innerHTML .= pathCompile($array[$key]);
				}
			} else {
				$innerHTML .= $array[$key];
			}

			unset($array[$key]);
			$key++;
		}
	}

	//process attributes - processed last because above code removes numeric keys from array (not attributes)
	foreach($array as $key => $value){
		if(!is_numeric($key)){//in case of leftover numeric keys (like if self closing tag was given content)
			//encode any double quotes from string (these can't be in attributes)...this is important for attributes which contain script
			$value = preg_replace('/\"/', '&quot;', $value);
			$return .= ' ' . $key . '="' . $value . '"';
		} else {
			echo 'error: self closing tag may have content';//TODO: add tag name n' other stuff to error logging
			return;
		}
	}

	$currentIndentation = substr($currentIndentation,0,-1);

	if(!$isSelfClosing){
		if(isset($containsNestedTags) && $containsNestedTags){//isset prevents nasty undefined notice
			$return .= '>' . $innerHTML . "\n" . $currentIndentation . '</' . $tagName . '>';//add stuff for regular tags
		} else {
			$return .= '>' . $innerHTML . '</' . $tagName . '>';//add stuff for regular tags
		}
	} else {
		$return .= '/>';//add stuff for self closing tags
	}

	return $return;
}

/*
$fish = [
	'turtle' => [1,2,3],
	'buffalo' => [4,5,6]
];

$arrayGetter = 0;

function getStuff(){
	global $fish;

	//find stuff

	$GLOBALS['arrayGetter'] =& $fish['turtle'][0];
	return 'arrayGetter';
}

${getStuff()} = 300;
*/
?>