<?php

require(__DIR__ . '/project_functions.php');

function help()
{
    echo <<<EOT

usage: create_project [options] package_name

options:
    -f force copy files to project dir
    -o screen orientation, eg: -o landscape . default is portrait
    -t template path, eg: -t /quick-cocos2d-x/template/PROJECT_TEMPLATE_01
    -noproj skip create projects

    package name, eg: com.quickx.games.physics

examples:

    create_project com.quickx.game.physics



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
    'force'        => false,
    'templatePath' => '',
    'packageName'  => '',
    'noproj'       => false,
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
    else if ($argv[0] == '-f')
    {
        $config['force'] = true;
    }
    else if ($argv[0] == '-noproj')
    {
        $config['noproj'] = true;
    }
    else if ($config['packageName'] == '')
    {
        $config['packageName'] = $argv[0];
    }

    array_shift($argv);
} while (count($argv) > 0);

$creator = new ProjectCreator($config);
if ($creator->run())
{
    echo <<<EOT

DONE.


EOT;

}
else
{
    help();
}
