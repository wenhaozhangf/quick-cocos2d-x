<?php

require_once(__DIR__ . '/ProjectConfig.php');


class ProjectCreatorConfig extends ProjectConfig
{
    public $templatePath;

    function __construct(array $config)
    {
        parent::__construct($config);
        $ready = $this->ready;
        $this->ready = false;

        // check template
        $templatePath = trim($config['templatePath']);
        if (empty($templatePath))
        {
            $templatePath = realpath(dirname(dirname(dirname(__DIR__))));
            $templatePath = rtrim($templatePath, "/\\") . DS . 'template' . DS . 'newproject.general';
        }

        $templatePath = rtrim($templatePath, "/\\") . DS;
        if (!is_dir($templatePath))
        {
            printf("ERROR: invalid template path \"%s\"\n", $templatePath);
            return;
        }
        $this->templatePath = $templatePath;

        // prepare contents
        $this->vars['__TEMPLATE_PATH__'] = $this->templatePath;

        $this->ready = $ready;
    }
}

