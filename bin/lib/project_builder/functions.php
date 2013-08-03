<?php

if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

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
        if ($file{0} == '.')
        {
            continue;
        }

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
