<?php
error_reporting( E_ALL );
ini_set('display_errors', 1);

require 'firephp/fb.php';

require 'path.php';
$test = new path;

echo '<!DOCTYPE html>';

$test->path =
['html',//TODO: add manifest="manifest.mf" + make file
	['head',
		['meta',
			'http-equiv' => "Content-Type",
			'content' => "text/html",
			'charset' => "utf-8"
		],
		['title', 'PATH'],
		['link#favicon',
			'href' => 'favicon.ico',
			'rel' => 'shortcut icon',
		],
	],
	['body#body',
		function(){
			$array = ['i\'m','too','sexy','for','my','shirt'];
			$newStuff;
			foreach($array as $value){
				$newStuff[] = ['p', $value];
			}
			return $newStuff;
		},
		'data-hot' => true,
		['#myId',
			function(){
				$array = ['i\'m','too','sexy','for','my','shirt'];
				$newStuff;
				foreach($array as $value){
					$newStuff[] = ['p', $value];
				}
				return $newStuff;
			}
		],
		['#myId.myClass',5+4+3],
		function(){
			$array = ['i\'m','too','sexy'];
			$newStuff;
			foreach($array as $value){
				$newStuff[] = ['p', $value];
			}
			return $newStuff;
		},
		['#myId.myClass',5+4+3]
	]
];

$value = &$test->find('#myId');
$value[] = ['p','one last thing'];
echo $test->compile();
?>