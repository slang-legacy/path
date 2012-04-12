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
	pathNormalize($array);
	//var_dump($array);

	$currentIndentation;
	return pathCompile($array);
}

function getIdAndClasses(&$array){//currently only called by path normalize (doesn't need to be seperate function)
	if(!is_string($array[0])){
		pathError('tag name is not string or attribute is array');
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
	//$tagName = $array[0];//add back at end of 
	//unset($array[0]);

	end($array);//need to eval once before loop start
	$each = each($array);

	while(count($array) > 0){
		$key = $each['key'];//shorter

		if(is_callable($array[$key]) && gettype($array[$key]) == "object"){//process anonymous functions
			$newValues = call_user_func($array[$key]);
			unset($array[$key]);//must remove function before merging, or could unset wrong thing due to change in keys during merge????
			if(is_array($newValues)){
				$array = array_merge($array, $newValues);
			} else {
				pathError('function did not return array');
			}
		} else {
			//recursively call path to process nested tags
			if(is_array($array[$key])) pathNormalize($array[$key]);

			//will overwrite non-numberic keys (if declared twice), and append numberic ones - needed due to function returning values
			is_numeric($key) ? $newArray[] = $array[$key] : $newArray[$key] = $array[$key];
			unset($array[$key]);
		}

		end($array);
		$each = each($array);
	}

	$array = array_reverse($newArray);
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
			pathError('self closing tag may have content');
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

function pathError($errorText){
	//TODO: add tag name n' other stuff to error logging
	echo 'error: ' . $errorText;
}

function pathErrorCheck(){
	//TODO: check for duplicate ids
}

$referanceHoldingVar = '';
function pathFind($query, &$array){
	global $pathOptions;

	if(!$pathOptions['manualNormalize']) pathNormalize($array);

	//find stuff

	$GLOBALS['referanceHoldingVar'] =& $array[2][3][1];
	return 'referanceHoldingVar';
} 

?>