<?php

class ProjectConfig
{
    public $packageName;
    public $packageFullName;
    public $packageLastName;
    public $orientation;
    public $quiet;
    public $vars = array();
    public $ready = false;

    function __construct(array $config)
    {
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
        $this->quiet = isset($config['quiet']) ? (bool)$config['quiet'] : false;

        // prepare contents
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

    function validate()
    {
        return $this->ready;
    }

    function dump()
    {
        print_r($this);
        print("\n");
    }
}
