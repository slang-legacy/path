<?php
require 'path.php';

$path = [
	'html',//TODO: add manifest="manifest.mf" + make file
	[
		'head',
		[
			'meta',
			'http-equiv' => "Content-Type",
			'content' => "text/html",
			'charset' => "utf-8"
		],
		['title', 'CSD'],
		[
			'link#favicon',
			'href' => 'favicon.ico',
			'rel' => 'shortcut icon',
		],
		'<!--[if lt IE 9]><link rel="stylesheet" type="text/css" href="css/style-ie.css" /><![endif]-->',
		//TODO: add meta tags for bookmarks and/or for search engines
	],
	[
		'body#body'
	]
];

echo path($path);
?>