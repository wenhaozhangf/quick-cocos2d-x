<?php

require_once(__DIR__ . '/project_builder/functions.php');
require_once(__DIR__ . '/project_builder/ProjectConfig.php');
require_once(__DIR__ . '/project_builder/ProjectBuilder.php');

function help()
{
    echo <<<EOT

usage: build_project -c channel [options] [project_path]

optional:
    -p package_name, eg: com.quick-x.sample.benchmark
       if not specified, read package_name from build.json

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
    $ build_project -c general.ios -p com.quickx.sample.benchmark

    build project with \$QUICK_COCOS2DX_ROOT/template/general.ios.build



EOT;

}

if ($argc < 4)
{
    help();
    exit(1);
}

array_shift($argv);

$config = array(
    'orientation'  => 'portrait',
    'templatePath' => '',
    'packageName'  => '',
    'platform'     => '',
    'projectPath'  => '',
);

do
{
    if ($argv[0] == '-t')
    {
        $config['templatePath'] = $argv[1];
        array_shift($argv);
    }
    else if ($argv[0] == '-o')
    {
        $config['orientation'] = $argv[1];
        array_shift($argv);
    }
    elseif ($argv[0] == '-p')
    {
        $config['platform'] = $argv[1];
        array_shift($argv);
    }
    else if ($config['projectPath'] == '')
    {
        $config['projectPath'] = $argv[0];
    }
    else if ($config['packageName'] == '')
    {
        $config['packageName'] = $argv[0];
    }

    array_shift($argv);
} while (count($argv) > 0);

// check project path
$path = $config['projectPath'];
if (substr($path, 0, 1) != '/' && substr($path, 1, 1) != ':')
{
    $path = rtrim(getcwd(), '/\\') . DS . $path;
}
$config['projectPath'] = realpath($path);

if (is_dir($path))
{
    // read build settings
    $jsonString = @file_get_contents($path . DS . 'build.json');
    if ($jsonString)
    {
        $json = json_decode($jsonString, true);
        if ($config['orientation'] == '' && isset($json['orientation']) && $json['orientation'] != '')
        {
            $config['orientation'] = $json['orientation'];
        }
        if ($config['packageName'] == '' && isset($json['packageName']) && $json['packageName'] != '')
        {
            $config['packageName'] = $json['packageName'];
        }
    }
}

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
