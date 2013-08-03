<?php

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
