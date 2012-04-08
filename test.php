<?php
error_reporting( E_ALL );
ini_set('display_errors', 1);

require 'path.php';

echo '<!DOCTYPE html>';
$pathArray =
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
			$array = ['i\'m','too','sexy','for','my','shirt'];
			$newStuff;
			foreach($array as $value){
				$newStuff[] = ['p', $value];
			}
			return $newStuff;
		},
		['#myId.myClass',5+4+3]
	]
];

echo path($pathArray);
?>