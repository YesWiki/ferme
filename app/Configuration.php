<?php
namespace Ferme;

class Configuration
{
    /**
     *
     * @var array
     */
    private $config;

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        include $file;
        if (isset($wakkaConfig)) {
            $this->config = $wakkaConfig;
        }
    }

    /**
     * @param $parameter_name
     */
    public function getParameter($parameter_name)
    {
        if ($this->isExist($parameter_name)) {
            return $this->config[$parameter_name];
        }
        throw new \Exception(
            'Le paramètre ' . $parameter_name . ' n\'est pas défini.',
            1
        );

    }

    /**
     * @param $parameter_name
     */
    public function isExist($parameter_name)
    {
        if (isset($this->config[$parameter_name])) {
            return true;
        } else {
            return false;
        }
    }
}
