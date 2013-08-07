<?php

require_once(__DIR__ . '/ProjectConfig.php');


class ProjectBuilderConfig extends ProjectConfig
{
    public $projectPath;
    public $templatesPath;
    public $channel;
    public $channelPath;

    function __construct(array $config)
    {
        parent::__construct($config);
        $ready = $this->ready;
        $this->ready = false;

        // check project path
        $projectPath = $config['projectPath'];
        if (!is_dir($projectPath))
        {
            printf("ERROR: invalid project path \"%s\"\n", $projectPath);
            return;
        }
        $this->projectPath = $projectPath;

        // check template
        $templatesPath = trim($config['templatesPath']);
        if (empty($templatesPath))
        {
            $templatesPath = realpath(dirname(dirname(dirname(__DIR__))));
            $templatesPath = rtrim($templatesPath, "/\\") . DS . 'template';
        }

        $templatesPath = rtrim($templatesPath, "/\\") . DS;
        if (!is_dir($templatesPath))
        {
            printf("ERROR: invalid template path \"%s\"\n", $templatesPath);
            return;
        }
        $this->templatesPath = $templatesPath;

        // check channel
        $channel = trim($config['channel']);
        if (empty($channel))
        {
            printf("ERROR: not specify channel\n");
            return;
        }

        $channelPath = $this->templatesPath . 'build.' . $channel;
        if (!is_dir($channelPath))
        {
            printf("ERROR: invalid channel path \"%s\"\n", $channelPath);
            return;
        }

        $this->channel = $channel;
        $this->channelPath = $channelPath;

        // prepare contents
        $this->vars['__TEMPLATES_PATH__'] = $this->templatesPath;
        $this->vars['__CHANNEL__'] = $this->channel;
        $this->vars['__CHANNEL_PATH__'] = $this->channelPath;

        $this->ready = $ready;
    }
}

