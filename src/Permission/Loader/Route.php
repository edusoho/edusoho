<?php

namespace Permission\Loader;

use Symfony\Component\Routing\Route as BaseRoute;

class Route extends BaseRoute
{
    private $permissions = array();

    public function __construct($path, array $defaults = array(), array $requirements = array(), array $options = array(), $host = '', $schemes = array(), $methods = array(), $permissions = array())
    {
        $this->setPath($path);
        $this->setDefaults($defaults);
        $this->setRequirements($requirements);
        $this->setOptions($options);
        $this->setHost($host);
        // The conditions make sure that an initial empty $schemes/$methods does not override the corresponding requirement.
        // They can be removed when the BC layer is removed.
        $this->setPermissions($permissions);

        if ($schemes) {
            $this->setSchemes($schemes);
        }

        if ($methods) {
            $this->setMethods($methods);
        }
    }

    public function serialize()
    {
        return serialize(array(
            'path'         => $this->path,
            'host'         => $this->host,
            'defaults'     => $this->defaults,
            'requirements' => $this->requirements,
            'options'      => $this->options,
            'schemes'      => $this->schemes,
            'methods'      => $this->methods,
            'compiled'     => $this->compiled,
            'permissions'  => $this->permissions
        ));
    }

    public function unserialize($serialized)
    {
        $data               = unserialize($serialized);
        $this->path         = $data['path'];
        $this->host         = $data['host'];
        $this->defaults     = $data['defaults'];
        $this->requirements = $data['requirements'];
        $this->options      = $data['options'];
        $this->schemes      = $data['schemes'];
        $this->methods      = $data['methods'];
        $this->permissions  = $data['permissions'];

        if (isset($data['compiled'])) {
            $this->compiled = $data['compiled'];
        }
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
        return $permissions;
    }
}
