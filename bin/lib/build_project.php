<?php

require(__DIR__ . '/project_functions.php');

function help()
{
    echo <<<EOT

usage: build_project [options] project_path package_name

options:
    -p platform, eg: -p ios,android
    -o screen orientation, eg: -o landscape . default is portrait
    -t template root path, eg: -t /quick-cocos2d-x/template/BUILD_TEMPLATE_01

    package name, eg: com.quickx.games.physics

examples:

    build_project -p ios benchmark com.quickx.sample.benchmark



EOT;

}

if ($argc < 2)
{
    help();
    exit(1);
}

array_shift($argv);

$config = array(
    'orientation'  => 'portrait',
    'templatePath' => '',
    'packageName'  => '',
    'platform'     => 'ios,android',
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
