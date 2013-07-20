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

class ProjectConfig
{
    public $templatePath;
    public $packageName;
    public $packageFullName;
    public $packageLastName;
    public $orientation;
    public $vars = array();
    public $ready = false;

    function __construct(array $config)
    {
        // check template
        $templatePath = rtrim($config['templatePath'], "/\\") . DS;
        if (!is_dir($templatePath))
        {
            printf("ERROR: invalid template path \"%s\"\n", $templatePath);
            return;
        }
        if (!file_exists($templatePath . 'TEMPLATE_INFO.json'))
        {
            printf("ERROR: not found TEMPLATE_INFO.json in template path \"%s\"\n", $templatePath);
            return;
        }
        $info = file_get_contents($templatePath . 'TEMPLATE_INFO.json');
        $info = json_decode($info, true);
        if (!is_array($info) || empty($info['name']))
        {
            printf("ERROR: invalid TEMPLATE_INFO.json in template path \"%s\"\n", $templatePath);
            return;
        }

        $this->templatePath = $templatePath;

        // check packageName
        $packageName = str_replace('-', '_', strtolower($config['packageName']));
        $parts = explode('.', $packageName);
        $packageName = array();
        for ($i = 0; $i < count($parts); $i++)
        {
            $parts[$i] = preg_replace('/[^a-z0-9_]/', '', $parts[$i]);
            if (!empty($parts[$i])) $packageName[] = $parts[$i];
        }
        if (count($packageName) < 2)
        {
            printf("ERROR: invalid package name \"%s\"\n", implode('.', $packageName));
            return;
        }

        $lastname = $packageName[count($packageName) - 1];
        array_pop($packageName);
        $packageName = implode('.', $packageName);
        $this->packageName = $packageName;
        $this->packageLastName = $lastname;
        $this->packageFullName = $packageName . '.' . $lastname;

        // check more options
        $orientation = strtolower($config['orientation']);
        if ($orientation != 'landscape' && $orientation != 'portrait')
        {
            printf("ERROR: invalid screen orientation \"%s\"\n", $orientation);
            return;
        }
        $this->orientation = $orientation;

        // prepare contents
        $this->vars['__TEMPLATE_PATH__'] = $this->templatePath;
        $this->vars['__PROJECT_PACKAGE_NAME__'] = $this->packageName;
        $this->vars['__PROJECT_PACKAGE_NAME_L__'] = strtolower($this->packageName);
        $this->vars['__PROJECT_PACKAGE_FULL_NAME__'] = $this->packageFullName;
        $this->vars['__PROJECT_PACKAGE_FULL_NAME_L__'] = strtolower($this->packageFullName);
        $this->vars['__PROJECT_PACKAGE_LAST_NAME__'] = $this->packageLastName;
        $this->vars['__PROJECT_PACKAGE_LAST_NAME_L__'] = strtolower($this->packageLastName);
        $this->vars['__PROJECT_PACKAGE_LAST_NAME_UF__'] = ucfirst(strtolower($this->packageLastName));
        $this->vars['__SCREEN_ORIENTATION__'] = $this->orientation;
        $this->vars['__SCREEN_ORIENTATION_L__'] = strtolower($this->orientation);
        $this->vars['__SCREEN_ORIENTATION_UF__'] = ucfirst(strtolower($this->orientation));
        if ($this->orientation == 'landscape')
        {
            $this->vars['__SCREEN_WIDTH__'] = '960';
            $this->vars['__SCREEN_HEIGHT__'] = '640';
            $this->vars['__SCREEN_ORIENTATION_QUICK__'] = 'FIXED_HEIGHT';
        }
        else
        {
            $this->vars['__SCREEN_WIDTH__'] = '640';
            $this->vars['__SCREEN_HEIGHT__'] = '960';
            $this->vars['__SCREEN_ORIENTATION_QUICK__'] = 'FIXED_WIDTH';
        }

        if ($this->orientation == 'landscape')
        {
            $this->vars['__SCREEN_ORIENTATION_IOS__'] = "<string>UIInterfaceOrientationLandscapeRight</string>\n<string>UIInterfaceOrientationLandscapeLeft</string>";
        }
        else
        {
            $this->vars['__SCREEN_ORIENTATION_IOS__'] = '<string>UIInterfaceOrientationPortrait</string>';
        }

        $this->ready = true;
    }
}

// ----------------------------------------

class ProjectCreator
{
    private $projectConfig;
    private $projectPath;
    private $noproj;

    function __construct(array $config)
    {
        $this->projectConfig = new ProjectConfig($config);

        $force = $config['force'];
        $this->noproj = $config['noproj'];

        // check projectName
        if (isset($this->projectConfig->vars['__PROJECT_PACKAGE_LAST_NAME_L__']))
        {
            $this->projectPath = rtrim(getcwd(), '/\\') . DS . $this->projectConfig->vars['__PROJECT_PACKAGE_LAST_NAME_L__'] . DS;
        }

        if (!$force && (is_dir($this->projectPath) || file_exists($this->projectPath)))
        {
            printf("ERROR: project path \"%s\" exists\n", $this->projectPath);
            return;
        }


    }

    function run()
    {
        if (!$this->projectConfig->ready) return false;

        echo <<<EOT

template            : {$this->projectConfig->templatePath}

package name        : {$this->projectConfig->packageFullName}
project path        : {$this->projectPath}
screen orientation  : {$this->projectConfig->orientation}


EOT;

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
            if ($this->noproj && substr($sourceFilename, 0, 5) == 'proj.') continue;
            if ($this->noproj && substr($sourceFilename, 0, 8) == 'sources/') continue;
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

        printf("create file \"%s\" ... ", $destinationFilename);
        $dirname = pathinfo($destinationFilename, PATHINFO_DIRNAME);
        $destinationDir = $this->projectPath . $dirname;

        if (!is_dir($destinationDir))
        {
            mkdir($destinationDir, 0777, true);
        }
        if (!is_dir($destinationDir))
        {
            printf("ERROR: mkdir failure\n");
            return false;
        }

        $destinationPath = $this->projectPath . $destinationFilename;
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

        printf("OK\n");
        return true;
    }
}

// ----------------------------------------

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
