<?php

require_once(__DIR__ . '/project_builder/functions.php');
require_once(__DIR__ . '/project_builder/ProjectCreatorConfig.php');
require_once(__DIR__ . '/project_builder/ProjectCreator.php');

function help()
{
    echo <<<EOT

usage: create_project [options] -p package_name [project_parent_dir]

optional:
    -f force overwrite files exists in project directory

    -o screen orientation, eg: -o landscape
       if not specified, default is portrait

    -t project template path, eg: -t /my_template/
       if not specified, use default path (\$QUICK_COCOS2DX_ROOT/template/newproject.general/)

    project_parent_dir, eg: /my_games/
       if not specified, use current directory as project's parent directory

required:
    -p package_name, eg: com.quick-x.sample.benchmark

examples:

    $ create_project -p com.quickx.game.physics /mygames/

    >>> create project in /mygames/physics/ ...



EOT;

}

if ($argc < 3)
{
    help();
    exit(1);
}

array_shift($argv);

$config = array(
    'templatePath' => '',
    'orientation' => 'portrait',
    'force' => false,
    'packageName' => '',
    'projectParentDir' => '',
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
    else if ($argv[0] == '-p')
    {
        $config['packageName'] = $argv[1];
        array_shift($argv);
    }
    else
    {
        $config['projectParentDir'] = $argv[0];
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
