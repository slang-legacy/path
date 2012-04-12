<?php //PATH is (P)HP (A)rrays (T)o (H)TML

//TODO: add in import tag (gets & adds file in place of tag)
//TODO: switch from php array syntax???
//TODO: fix issue with spaces between tags
//TODO: get selector library like sizzle for php
class path {
	public $options = [
		'selfClosingTags' => ['img', 'br', 'input', 'meta', 'link'],
		'extraSpace' => true,//if you want path to add extra space between tags (for browser compatibility)
		'indent' => true,
		'showErrors' => true,
		'manualNormalize' => false //increase performance by only running normalize functions when needed
	];

	public $path = [];

	public function compile(){//wrapper function
		if(!$this->options['manualNormalize']) $this->normalize($this->path);

		$currentIndentation;
		return $this->compileProcess($this->path);
	}

	protected function getIdAndClasses(&$array){//currently only called by path normalize (doesn't need to be seperate function)
		if(!is_string($array[0])){
			error('tag name is not string or attribute is array');
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

	public function normalize(&$array){
		$this->getIdAndClasses($array);
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
					error('function did not return array');
				}
			} else {
				//recursively call path to process nested tags
				if(is_array($array[$key])) $this->normalize($array[$key]);

				//will overwrite non-numberic keys (if declared twice), and append numberic ones - needed due to function returning values
				is_numeric($key) ? $newArray[] = $array[$key] : $newArray[$key] = $array[$key];
				unset($array[$key]);
			}

			end($array);
			$each = each($array);
		}

		$array = array_reverse($newArray);
	}

	protected function compileProcess($array){//separate because this needs to be called recursively
		global $currentIndentation;

		$tagName = $array[0];
		unset($array[0]);

		//make a way to manually specify a self closing tag
		$isSelfClosing = in_array($tagName, $this->options['selfClosingTags']);

		if($this->options['indent']){
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
					if($this->options['indent']) $containsNestedTags = true;//used for end tag
					if($this->options['extraSpace'] && !$this->options['indent']){//indent must be false, otherwise the extra space would be useless
						$innerHTML .= ' ' . pathCompile($array[$key]) . ' ';
					} else {
						$innerHTML .= $this->compileProcess($array[$key]);
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
				error('self closing tag may have content');
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

	function error($errorText){
		//TODO: add tag name n' other stuff to error logging
		echo 'error: ' . $errorText;
	}

	function errorCheck(){
		//TODO: check for duplicate ids
	}

	public function &find($query){
		if(!$this->options['manualNormalize']) $this->normalize($this->path);

		if(substr($query,0,1) == '#') $found = &$this->getElementById($this->path);

		//$GLOBALS['referanceHoldingVar'] =& $found;
		return $found;
	}

	protected function &getElementById(&$array){
		return $array[2][3][1];
	}
}

class Fruit {
    private $color = "red";
 
    public function &getColorByRef() {
        return $this->color;
    }
 
    public function getColor() {
        return $this->color;
    }
 
    public function printColor() {
        
    }
} 
 
echo "\nTEST RUN 1:\n\n";
$fruit = new Fruit;
$color = $fruit->getColor();
echo "Fruit's color is $color\n"; 
$color = "green"; // does nothing, but bear with me
$color = $fruit->getColor();
echo "Fruit's color is $color\n"; 
 
echo "\nTEST RUN 2:\n\n";
$fruit = new Fruit;
$color = &$fruit->getColorByRef();
echo "Fruit's color is $color\n"; 
$color = "green"; // now this changes the actual property of $fruit
$color = $fruit->getColor();
echo "Fruit's color is $color\n";
?>