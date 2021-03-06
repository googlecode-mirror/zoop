<?php

define('_n', "\n");

function _en()
{
	echo _n;
}

function _mkdir($path, $mode = 0775)
{
	if(is_dir($path))
	{
		echo "notice: '$path' already exists\n";
		return;
	}
	
	_status("creating directory '$path'");
	mkdir($path, $mode, true);
}

function _chgrp($path, $group, $recursive = false)
{
	$r = $recursive ? 'recursively' : '';
	_status("changing group of '$path' to '$group' $r");
	if($recursive)
		_chgrp_r($path, $group);
	else
		chgrp($path, $group);
}

function _chgrp_r($path, $group)
{
	chgrp($path, $group);
	if(!is_dir($path))
		return;
	
	$dir = new DirectoryIterator($path);
	foreach($dir as $fileinfo)
	    if(!$fileinfo->isDot())
			_chgrp_r($path . '/' . $fileinfo->getFilename(), $group);
}

function _chmod($path, $mode, $recursive = false)
{
	$r = $recursive ? 'recursively' : '';
	$m = decoct($mode);
	_status("setting mode of '$path' to '$m' $r");
	if($recursive)
		_chmod_r($path, $mode);
	else
		chmod($path, $mode);
}

function _chmod_r($path, $mode)
{
	chmod($path, $mode);
	if(!is_dir($path))
		return;

	$dir = new DirectoryIterator($path);
	foreach($dir as $fileinfo)
	    if(!$fileinfo->isDot())
			_chmod_r($path . '/' . $fileinfo->getFilename(), $mode);
}

/*
function _chmod($path, $mode, $recursive = false)
{
	$m = decoct($mode);
	
	if($recursive)
	{
		_status("setting mode of '$path' to '$m' recursively");
		_chmod_r($path, $mode);
	}
	else
	{
		echo "path = $path\n";
		$curmodString = substr(decoct(fileperms ($path)), -3, 3);
		var_dump($curmodString);
		$newmodString = substr(decoct($mode), -3, 3);
		if($curmodString != $newmodString)
		{
			_status("mode of '$path' is already '$m'");
		}
		else
		{
			_status("setting mode of '$path' to '$m'");
			chmod($path, $mode);
		}
	}
}

function _chmod_r($path, $mode)
{
	chmod($path, $mode);
	if(!is_dir($path))
		return;

	$dir = new DirectoryIterator($path);
	foreach($dir as $fileinfo)
		if(!$fileinfo->isDot())
			_chmod_r($path . '/' . $fileinfo->getFilename(), $mode);
}
*/

//  this is a hack, we need to just set this up with proper OOP and conveninece functions
function _forcegen()
{
    global $FORCEGEN;
    $FORCEGEN = true;
}

function _fetch($path, $params = array())
{
	global $_assigns;
	
	$templatePath = $path;
	
	$gui = new gui();
	if($_assigns)
		foreach($_assigns as $name => $value)
			$gui->assign($name, $value);
	
	foreach($params as $name => $value)
	{
		// echo "param: $name => $value\n";
		$gui->assign($name, $value);
	}
	
	return $gui->fetch($templatePath . '.tpl');
}

function _gen($path, $filePath = '', $params = array())
{
	global $FORCEGEN;
	if(!$filePath)
		$filePath = $path;
	$content = _fetch($path, $params);
	_status("creating generated file '" . getcwd() . '/' . $filePath . "'");
	
	if(isset($FORCEGEN) && $FORCEGEN)
	    $forcegen = true;
	else
	    $forcegen = false;
	
	if(file_exists($filePath) && !$forcegen)
		_status("There is already a file at $filePath");
	
	file_put_contents($filePath, $content);
}

function _cd($path)
{
	_status("changing directory to '$path'");
	chdir($path);
}

function _ln($target, $link)
{
	global $FORCEGEN;
	_status("trying to link '$link' to '$target'");
	
	if(file_exists($link))
	{
		if($FORCEGEN)
		{
			if(is_link($link))
			{
				_status("Removing existing symlink at at $link");
				unlink($link);
			}
			else
			{
				_status("There is already a non-symlink file at $link");
				return;
			}
		}
		else
		{
			_status("There is already a file at $link");
			return;
		}
	}
	
	_status("linking '$link' to '$target'");
	symlink($target, $link);
}

function _status($message)
{
	echo "status: $message" . _n;
}

function _assign($name, $value)
{
	global $_assigns;
	_status("assigning '$value' to '$name'");
	return $_assigns[$name] = $value;
}

function _run($command, $params)
{
	Ex::echoOn();
	return Ex::pass($command, $params);
	Ex::echoOff();
}
