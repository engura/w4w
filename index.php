<?php
/** Engura - Website Creation & Management System
		Copyright (C) 2006-2014 Vladimir Glytenko.
		Please see readme.txt for a description of the code, and
		license.txt for the license. */
header("HTTP/1.1 200 OK", true, 200);
header('X-PHP-Response-Code: 200', true, 200);

# Restructuring of the URL:
# http://www.domain.com/lang_code/template_opts/page.html
# -> lang_code/template_opts/page.htm
# -> lang_code/page.php
# -> template_opts/page
# -> create instance of Page from these uri.
if(!empty($_GET['u']) ) $uri = explode("/", trim($_GET['u'], "/"));
else $uri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
# print_r($uri); print_r($_GET);
include_once 'includes/page.php';
$lang = "";
$template = "";
$pageTitle = "";
# new Page should have the following format:
# Page(script.php|page.htm|path, normal|print|special, ru|en|jp);

# 2007.04.17 -> adding correct Marketplace split
# 2008.04.06 -> ummm finishing marketplace? :D haha! wow. that took a long time!
# /lang/template/pagename
# /lang/marketplace/cat1/cat2/cat3
# /marketplace/cat1/cat2/cat3
# /marketplace/
switch(count($uri))
{
	case 3:  # all 3 parameters present. No guessing necessary... except in the case of Marketplace~
		makeGET($uri[2]);
		if($uri[0] == "marketplace")
		{
			$template = array_shift($uri);
			$pageTitle = implode("/", $uri);
		}
		else
		{
			$lang = $uri[0];
			$template = $uri[1];
			$pageTitle = $uri[2];
		}
		break;
	case 2:  # the first item is either the lang_code or template_opts... or Marketplace
		makeGET($uri[1]);
		$pageTitle = $uri[1];
		if(strlen($uri[0]) == 2)
			$lang = $uri[0];
		else $template = $uri[0];
		break;
	case 1: # only one parameter after / -> page's title; other params are empty
		makeGET($uri[0]);
		$pageTitle = $uri[0];
		break;
	default: # we have more than 3 parameters. Must be a marketplace then...
		makeGET($uri[count($uri)-1]);
		if(strlen($uri[0]) == 2) # is the first item a language code??
			$lang = array_shift($uri);
		$template = array_shift($uri);
		$pageTitle = implode("/", $uri);
		break;
};

prepURI($pageTitle, $template, $lang);




if(!empty($_POST))
	$page=new Page($_POST['script'], '', $_POST['language']);
else $page=new Page($pageTitle, $template, $lang);

$page->echoPage();


function makeGET(&$path)
{
	if(strstr($path, "?"))
	{
		list($path, $params) = explode("?", $path);
		$keys = array();
		$values = array();

		$getarray = explode("&", $params); # makes array like {val1=2} {val2=3} {val3=4}
		for ($i = 0; $i < count($getarray); $i++)
		{
			list($key, $data) = explode("=", $getarray[$i], 2);
			if(strpos($key, "[]")) # there is an array within GET! spin thru it & accumulate data
			{
				$d_arr = array();
				$prev_key = '';
				do
				{
					$d_arr[] = $data;
					$prev_key = $key;
					$i++;
					list($key, $data) = explode("=", $getarray[$i], 2);
				}
				while($key == $prev_key);
				array_push($keys, str_replace("[]", "", $prev_key));
				array_push($values, $d_arr);
			}
			if (!empty($key) && !empty($data))
			{
				array_push($keys, $key);
				array_push($values, $data);
			}
		}
		if(!empty($keys) && !empty($values))
		$_GET = array_combine($keys, $values);
	}
}
?>
