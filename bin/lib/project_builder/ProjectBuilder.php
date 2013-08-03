<?php

require_once(__DIR__ . '/ProjectConfig.php');
require_once(__DIR__ . '/ProjectCreator.php');

/*

Usage: xcodebuild [-project <projectname>]
                  [[-target <targetname>]...|-alltargets]
                  [-configuration <configurationname>]
                  [-arch <architecture>]...
                  [-sdk [<sdkname>|<sdkpath>]]
                  [-showBuildSettings]
                  [<buildsetting>=<value>]...
                  [<buildaction>]...

xcodebuild [-project <projectname>]
           -scheme <schemeName>
           [-configuration <configurationname>]
           [-arch <architecture>]...
           [-sdk [<sdkname>|<sdkpath>]]
           [-showBuildSettings]
           [<buildsetting>=<value>]...
           [<buildaction>]...

xcodebuild -workspace <workspacename>
           -scheme <schemeName>
           [-configuration <configurationname>]
           [-arch <architecture>]...
           [-sdk [<sdkname>|<sdkpath>]]
           [-showBuildSettings]
           [<buildsetting>=<value>]...
           [<buildaction>]...

xcodebuild -version [-sdk [<sdkfullpath>|<sdkname>] [<infoitem>] ]

xcodebuild -list [[-project <projectname>]|[-workspace <workspacename>]]

xcodebuild -showsdks

Options:
-usage                  print brief usage
-help                   print complete usage
-verbose                provide additional status output
-license                Show License agreement!
-project NAME           build the project NAME
-target NAME            build the target NAME
-alltargets             build all targets
-workspace NAME         build the workspace NAME
-scheme NAME            build the scheme NAME
-configuration NAME     use the build configuration NAME for building each target
-xcconfig PATH          apply the build settings defined in the file at PATH as overrides
-arch ARCH              build each target for the architecture ARCH; this will override architectures defined in the project
-sdk SDK                use SDK as the name or path of the base SDK when building the project
-toolchain NAME         use the toolchain with identifier or name NAME
-parallelizeTargets     build independent targets in parallel
-jobs NUMBER            specify the maximum number of concurrent build operations
-dry-run                do everything except actually running the commands
-showsdks               display a compact list of the installed SDKs
-showBuildSettings      display a list of build settings and values
-list                   lists the targets and configurations in a project, or the schemes in a workspace
-find-executable NAME   display the full path to executable NAME in the provided SDK and toolchain
-find-library NAME      display the full path to library NAME in the provided SDK and toolchain
-version                display the version of Xcode; with -sdk will display info about one or all installed SDKs

*/


class ProjectBuilder
{
    private $projectConfig;
    private $projectPath;
    private $config;

    function __construct(array $config)
    {
        $this->config = $config;
        $this->projectConfig = new ProjectConfig($config);

        if ($this->projectConfig->ready)
        {
            // check projectName
            $this->projectPath = $config['projectPath'];
            if (!is_dir($this->projectPath))
            {
                printf("ERROR: invalid project path \"%s\"\n", $this->projectPath);
            }
        }
    }

    function run()
    {
        if (!$this->projectConfig->ready) return false;

        // change current dir to tmp
        $tempDir = $this->projectPath . DS . 'build';
        if (!file_exists($tempDir) && !mkdir($tempDir))
        {
            printf("ERROR: can't create temp dir \"%s\"\n", $tempDir);
            return false;
        }
        chdir($tempDir);
        $this->config['force'] = true;
        $this->config['noproj'] = false;
        $creator = new ProjectCreator($this->config);
        $ret = $creator->run();

        if (!$ret)
        {
            rmdir($tempDir);
            return false;
        }

        $destDir = $tempDir . DS . $this->projectConfig->vars['__PROJECT_PACKAGE_LAST_NAME_L__'];
        $destResDir = $destDir . DS . 'res';
        if (!file_exists($destResDir) && !mkdir($destResDir))
        {
            printf("ERROR: can't create dest res dir \"%s\"\n", $destResDir);
            return false;
        }

        printf("\n");

        $len = strlen($this->projectPath) + 1;
        $paths = getPaths($destResDir);
        foreach ($paths as $path)
        {
            printf("remove exists file %s\n", substr($path, $len));
            unlink($path);
        }
        printf("\n");

        $srcs = getPaths($this->projectPath . DS . 'res');
        foreach ($srcs as $src)
        {
            $filename = substr($src, $len);
            $destPath = $destDir . DS . $filename;
            printf("copy file %s\n", substr($destPath, $len));
            copy($src, $destPath);
        }

        return true;
    }
}
