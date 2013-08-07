<?php

require_once(__DIR__ . '/functions.php');
require_once(__DIR__ . '/ProjectBuilderConfig.php');
require_once(__DIR__ . '/ProjectCreator.php');
require_once(__DIR__ . '/LuaPackager.php');


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
    public $projectConfig;
    public $config;

    function __construct(array $config)
    {
        // read build settings
        $projectPath = $config['projectPath'];
        $readConfigFromScript = empty($config['orientation']) || empty($config['packageName']);
        $scriptConfigPath = $projectPath . DS . 'scripts' . DS . 'config.lua';
        $scriptConfigIsExists = file_exists($scriptConfigPath);

        if ($readConfigFromScript)
        {
            if (!$scriptConfigIsExists)
            {
                printf("ERROR: can't read config from script file\n");
                $this->projectConfig->ready = false;
                return;
            }

            $lines = file($scriptConfigPath);
            foreach ($lines as $line)
            {
                if (empty($config['orientation']))
                {
                    $value = getValueByKey('CONFIG_SCREEN_ORIENTATION', $line);
                    if (!empty($value))
                    {
                        $config['orientation'] = $value;
                        continue;
                    }
                }

                if (empty($config['packageName']))
                {
                    $value = getValueByKey('CONFIG_APP_PACKAGE_NAME', $line);
                    if (!empty($value))
                    {
                        $config['packageName'] = $value;
                        continue;
                    }
                }

                $readConfigFromScript = empty($config['orientation']) || empty($config['packageName']);
                if (!$readConfigFromScript) break;
            }
        }

        $this->projectConfig = new ProjectBuilderConfig($config);
        $this->config = $config;
        $this->config['templatePath'] = $this->projectConfig->channelPath;
        $this->config['projectParentDir'] = $this->projectConfig->projectPath . DS . 'tmp';
        $this->config['projectDirectoryName'] = 'build.' . $this->projectConfig->channel;
        $this->config['force'] = true;
        $this->config['quiet'] = true;
    }

    function run()
    {
        if (!$this->projectConfig->ready) return false;

        // change current directory to tmp
        $tempDir = $this->projectConfig->projectPath . DS . 'tmp';
        if (!is_dir($tempDir) && !mkdir($tempDir))
        {
            printf("ERROR: can't create temp dir \"%s\"\n", $tempDir);
            return false;
        }
        chdir($tempDir);

        // cleanup
        $buildProjectPath = $this->config['projectParentDir'] . DS . $this->config['projectDirectoryName'];
        $files = getPathsWithDirectory($buildProjectPath);
        foreach ($files as $path)
        {
            if (is_file($path))
            {
                unlink($path);
            }
            else
            {
                rmdir($path);
            }
        }
        if (is_dir($buildProjectPath)) rmdir($buildProjectPath);

        // create build project
        $creator = new ProjectCreator($this->config);
        $ret = $creator->run();
        if (!$ret)
        {
            return false;
        }

        // compile scripts to zip
        $buildProjectAssetsPath = $creator->projectPath . DS . 'assets';
        mkdir($buildProjectAssetsPath);
        $buildProjectResPath = $buildProjectAssetsPath . DS . 'res';
        if (!is_dir($buildProjectResPath) && !mkdir($buildProjectResPath))
        {
            printf("ERROR: can't create temp dir \"%s\"\n", $buildProjectResPath);
            return false;
        }

        $scriptsZipPath = $buildProjectResPath . DS . 'scripts';
        $packagerConfig = array(
            'packageName'        => '',
            'excludes'           => array(),
            'srcdir'             => $this->projectConfig->projectPath . DS . 'scripts',
            'zip'                => true,
            'suffixName'         => 'zip',
            'quiet'              => true,
        );
        $packager = new LuaPackager($packagerConfig);
        $packager->dumpZip($scriptsZipPath);

        // copy assets
//        $srcs = getPaths($this->projectPath . DS . 'res');
//        foreach ($srcs as $src)
//        {
//            $filename = substr($src, $len);
//            $destPath = $destDir . DS . $filename;
//            printf("copy file %s\n", substr($destPath, $len));
//            copy($src, $destPath);
//        }

        return true;
    }
}
