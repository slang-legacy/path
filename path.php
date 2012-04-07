<?php //PATH is (P)HP (A)rrays (T)o (H)TML

//TODO: add in import tag (gets & adds file in place of tag)
//TODO: switch from php array syntax???
//TODO: fix issue with spaces between tags
//TODO: get selector library like sizzle for php

$pathOptions =[
	'selfClosingTags' => ['img', 'br', 'input', 'meta', 'link'],
	'extraSpace' => true,//if you want path to add extra space between tags (for browser compatibility)
	'indent' => true,
	'showErrors' => true
];

function getIdAndClasses(&$array){
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

$currentIndentation;//TODO: put currentIndentation into a class or something to prevent it from being global

function path($array){
	global $pathOptions;
	global $currentIndentation;

	getIdAndClasses($array);

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
			if(is_callable($array[$key])){//process anonymous functions
				$newValues = call_user_func($array[$key]);
				unset($array[$key]);
				$array = array_merge($array, $newValues);
				//die();
				$key = 0;//key gets reset
			} else {
				if(is_array($array[$key])){//recursively call path to process nested tags
					if($pathOptions['indent']) $containsNestedTags = true;//used for end tag
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
	}

	//process attributes - processed last because above code removes numeric keys from array (not attributes)
	foreach($array as $key => $value){
		//encode any double quotes from string (these can't be in attributes)...this is important for attributes which contain script
		$value = preg_replace('/\"/', '&quot;', $value);
		$return .= ' ' . $key . '="' . $value . '"';
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

var_dump($fish);
*/
?>