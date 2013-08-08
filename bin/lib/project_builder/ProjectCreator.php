<?php

require_once(__DIR__ . '/functions.php');
require_once(__DIR__ . '/ProjectCreatorConfig.php');


class ProjectCreator
{
    public $projectConfig;
    public $projectPath;

    function __construct(array $config)
    {
        $this->projectConfig = new ProjectCreatorConfig($config);

        $force = $config['force'];

        // check projectName
        $projectParentDir = trim($config['projectParentDir']);
        if (empty($projectParentDir))
        {
            $projectParentDir = getcwd();
        }
        $projectParentDir = rtrim($projectParentDir, '/\\');

        $projectDirectoryName = trim($config['projectDirectoryName']);
        if (empty($projectDirectoryName))
        {
            $projectDirectoryName = $this->projectConfig->vars['__PROJECT_PACKAGE_LAST_NAME_L__'];
        }

        $this->projectPath = $projectParentDir . DS . $projectDirectoryName;

        if (!$force && (is_dir($this->projectPath) || file_exists($this->projectPath)))
        {
            $this->projectConfig->ready = false;
            printf("ERROR: project path \"%s\" exists\n", $this->projectPath);
            return;
        }
    }

    function run()
    {
        if (!$this->projectConfig->ready) return false;

        if (!$this->projectConfig->quiet)
        {
            echo <<<EOT

template            : {$this->projectConfig->templatePath}

package name        : {$this->projectConfig->packageFullName}
project path        : {$this->projectPath}
screen orientation  : {$this->projectConfig->orientation}


EOT;
        }

        // create project dir
        if (!is_dir($this->projectPath)) mkdir($this->projectPath);
        if (!is_dir($this->projectPath))
        {
            printf("ERROR: create project dir \"%s\" failure\n", $this->projectPath);
            return;
        }

        // copy files
        $paths = getPaths($this->projectConfig->templatePath);
        foreach ($paths as $sourcePath)
        {
            $sourceFilename = substr($sourcePath, strlen($this->projectConfig->templatePath));
            if ($sourceFilename == 'TEMPLATE_INFO.json') continue;
            if (!$this->copyFile($sourcePath, $sourceFilename)) return false;
        }

        return true;
    }

    private function copyFile($sourcePath, $sourceFilename)
    {
        // check filename
        $sourceFilename = substr($sourcePath, strlen($this->projectConfig->templatePath));
        $destinationFilename = $sourceFilename;

        foreach ($this->projectConfig->vars as $key => $value)
        {
            $value = str_replace('.', DS, $value);
            $destinationFilename = str_replace($key, $value, $destinationFilename);
        }

        if (!$this->projectConfig->quiet)
        {
            printf("create file \"%s\" ... ", $destinationFilename);
        }
        $dirname = pathinfo($destinationFilename, PATHINFO_DIRNAME);
        $destinationDir = $this->projectPath . DS . $dirname;

        if (!is_dir($destinationDir))
        {
            mkdir($destinationDir, 0777, true);
        }
        if (!is_dir($destinationDir))
        {
            printf("ERROR: mkdir failure\n");
            return false;
        }

        $destinationPath = $this->projectPath . DS . $destinationFilename;
        $contents = file_get_contents($sourcePath);
        if ($contents == false)
        {
            printf("ERROR: file_get_contents failure\n");
            return false;
        }
        $stat = stat($sourcePath);

        foreach ($this->projectConfig->vars as $key => $value)
        {
            $contents = str_replace($key, $value, $contents);
        }

        if (file_put_contents($destinationPath, $contents) == false)
        {
            printf("ERROR: file_put_contents failure\n");
            return false;
        }
        chmod($destinationPath, $stat['mode']);

        if (!$this->projectConfig->quiet)
        {
            printf("OK\n");
        }
        return true;
    }
}
