<?php

if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);


function getValueByKey($key, $string)
{
    $find = strstr($string, $key);
    if (!$find) return '';

    $find = explode('=', $find);
    $find = array_map(function($value) {
        return str_replace(array('"', "'", ' ', "\n", "\t"), '', $value);
    }, $find);

    if (count($find) < 2 || $find[0] != $key) return '';
    return $find[1];
}

function getPaths($dir)
{
    $files = array();
    $dir = rtrim($dir, "/\\") . DS;
    $dh = opendir($dir);
    if ($dh == false)
    {
        return $files;
    }

    while (($file = readdir($dh)) !== false)
    {
        if ($file{0} == '.') continue;

        $path = $dir . $file;
        if (is_dir($path))
        {
            $files = array_merge($files, getPaths($path));
        }
        elseif (is_file($path))
        {
            $files[] = $path;
        }
    }
    closedir($dh);
    return $files;
}

function getPathsWithDirectory($dir)
{
    if (!is_dir($dir)) return array();

    $files = array();
    $dir = rtrim($dir, "/\\") . DS;
    $dh = opendir($dir);
    if ($dh == false)
    {
        return $files;
    }

    while (($file = readdir($dh)) !== false)
    {
        if ($file == '.' || $file == '..') continue;

        $path = $dir . $file;
        $files[] = $path;
        if (is_dir($path))
        {
            $files = array_merge($files, getPathsWithDirectory($path));
        }
    }
    closedir($dh);

    rsort($files, SORT_STRING);
    return $files;
}
