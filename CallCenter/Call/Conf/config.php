<?php

$config	=	require 'config.php';

$array = array(

	
	// template file suffix
	'TMPL_TEMPLATE_SUFFIX'=>'.htm', 
	
	'DEFAULT_THEME'  => 'default', 

		// filter
	'DEFAULT_FILTER'=>'strip_tags,htmlspecialchars',
	
	// file path replace
	'TMPL_PARSE_STRING'  =>array(
     '__JS__' => '/Public/js/',		// JS file path change
     '__UPLOAD__' => '/Public/Uploads/',	//	upload file path change
	 '__CSS__' => '/Public/style/',
	),

	'TMPL_L_DELIM'=>'<{',
	'TMPL_R_DELIM'=>'}>',

	
);

return array_merge($config,$array);
?>