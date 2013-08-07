<?php

require_once(__DIR__ . '/project_builder/ProjectBuilder.php');

function help()
{
    echo <<<EOT

usage: build_project [options] -c channel [project_path]

optional:
    -p package_name, eg: com.quick-x.sample.benchmark
       if not specified, read from scripts/config.lua

    -t channel templates path, eg: -l /my_channels/
       if not specified, use default path (\$QUICK_COCOS2DX_ROOT/template/)
       default template includes: general.ios, general.android

    project_path, eg: /my_games/game01/
       if not specified, use current directory

required:
    -c channel, eg: -c general.ios
       specify channel used for build

examples:

    $ cd benchmark
    $ build_project -c general.ios -p com.quick-x.sample.benchmark

    >>> build project with \$QUICK_COCOS2DX_ROOT/template/general.ios.build



EOT;

}

if ($argc < 3)
{
    help();
    exit(1);
}

array_shift($argv);

$config = array(
    'packageName'  => '',
    'templatesPath' => '',
    'channel' => '',
    'projectPath'  => '',
    'orientation' => '',
);

do
{
    if ($argv[0] == '-p')
    {
        $config['packageName'] = $argv[1];
        array_shift($argv);
    }
    if ($argv[0] == '-t')
    {
        $config['templatesPath'] = $argv[1];
        array_shift($argv);
    }
    else if ($argv[0] == '-c')
    {
        $config['channel'] = $argv[1];
        array_shift($argv);
    }
    else if ($config['projectPath'] == '')
    {
        $config['projectPath'] = $argv[0];
    }

    array_shift($argv);
} while (count($argv) > 0);

// check project path
$projectPath = $config['projectPath'];
if (substr($projectPath, 0, 1) != '/' && substr($projectPath, 1, 1) != ':')
{
    $projectPath = rtrim(getcwd(), '/\\') . DS . $projectPath;
}
$config['projectPath'] = realpath($projectPath);

$builder = new ProjectBuilder($config);
if ($builder->run())
{
    echo <<<EOT

DONE.


EOT;

}
else
{
    help();
}
