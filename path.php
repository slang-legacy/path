<?php //PATH is (P)HP (A)rrays (T)o (H)TML

//TODO: add in import tag (gets & adds file in place of tag)
//TODO: switch from php array syntax
//TODO: fix issue with spaces between tags
//TODO: get selector library like sizzle for php

$pathOptions =[
	'selfClosingTags' => ['img', 'br', 'input', 'meta', 'link'],
	'extraSpace' => true,//if you want path to add extra space between tags (for browser compatibility)
	'indent' => true,
	'showErrors' => true
];

function getIdAndClasses($array){
	preg_match('/[^.#\n\s]*/', $array[0], $tagName);//get only element name
	
	preg_match('/#([^.\n\s]*)/', $array[0], $id);//get only id name
	if(count($id) != 0 && empty($array['id'])) $array['id'] = $id[1];//this will ignore the shorthand if a id is defined normally
	
	preg_match_all('/\.([^.\n\s]*)/', $array[0], $classes);//get class names

	$len = count($classes);
	for($i=0; $i < $len; $i++){ 
		$array['class'] .= ' ' . $classes[$i][1];
	}
	$array['class'] = trim($array['class']);
	if(empty($array['class'])) unset($array['class']);

	$array[0] = empty($tagName[0]) ? 'div' : $tagName[0];//normalize and provide div default

	return $array;
}

global $currentIndentation;//TODO: put currentIndentation into a class or something

function path($array){
	global $pathOptions;
	global $currentIndentation;

	$array = getIdAndClasses($array);

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
			if(is_array($array[$key])){//recursivly call path to process nested tags
				if($pathOptions['extraSpace'] && !$pathOptions['indent']){//indent must be false, otherwise the extra space would be useless
					$innerHTML .= ' ' . path($array[$key]) . ' ';
				} else {
					$innerHTML .= path($array[$key]);
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
		//encode any double quotes from string (these can't be in attributes)...this is important for attributes which contain script
		$value = preg_replace('/\"/', '&quot;', $value);
		$return .= ' ' . $key . '="' . $value . '"';
	}

	if(!$isSelfClosing){
		$return .= '>' . $innerHTML . '</' . $tagName . '>';//add stuff for regular tags
	} else {
		$return .= '/>';//add stuff for self closing tags
	}

	$currentIndentation = substr($currentIndentation,0,-1);
	return $return;
}
?>
