<?php

require_once(__DIR__ . '/project_builder/LuaPackager.php');

function help()
{
    echo <<<EOT
usage: compile_scripts [options] dirname output_filename

options:
    -zip package to zip
    -suffix package file extension name
    -p prefix package name
    -x exclude packages, eg: -x framework.server, framework.tests
    -q quiet

examples:

    * packageing scripts/*.lua to res/game.zip
    compile_scripts -zip scripts/ res/game

EOT;

}

if ($argc < 3)
{
    help();
    exit(1);
}

array_shift($argv);

$config = array(
    'packageName'        => '',
    'excludes'           => array(),
    'srcdir'             => '',
    'outputFileBasename' => '',
    'zip'                => false,
    'suffixName'         => 'zip',
    'quiet'              => false,
);

do
{
    if ($argv[0] == '-p')
    {
        $config['packageName'] = $argv[1];
        array_shift($argv);
    }
    else if ($argv[0] == '-x')
    {
        $excludes = explode(',', $argv[1]);
        foreach ($excludes as $k => $v)
        {
            $v = trim($v);
            if (empty($v))
            {
                unset($excludes[$k]);
            }
            else
            {
                $excludes[$k] = $v;
            }
        }
        $config['excludes'] = $excludes;
        array_shift($argv);
    }
    else if ($argv[0] == '-q')
    {
        $config['quiet'] = true;
    }
    else if ($argv[0] == '-zip')
    {
        $config['zip'] = true;
    }
    else if ($argv[0] == '-suffix')
    {
        $config['suffixName'] = $argv[1];
        array_shift($argv);
    }
    else if ($config['srcdir'] == '')
    {
        $config['srcdir'] = $argv[0];
    }
    else
    {
        $config['outputFileBasename'] = $argv[0];
    }

    array_shift($argv);
} while (count($argv) > 0);

$packager = new LuaPackager($config);
if ($config['zip'])
{
    $packager->dumpZip($config['outputFileBasename']);
}
else
{
    $packager->dump($config['outputFileBasename']);
}
