<?php

namespace Topxia\WebBundle\Util;
use Symfony\Component\Yaml\Yaml;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;

Class Permission
{   
    public function getPermissions($parent, $type) 
    {
        $group = null;
        if (ctype_digit((string) $type)) {
            $group = $type;
            $type = null;
        }

        $permissions = $this->parsePermissions();
        $permissions = $this->addCode($permissions);
        $permissions = $this->sort($permissions);

        $result = array();

        foreach ($permissions as $key => $value) {

            if ($value['parent'] == $parent) {

                if ($type) {

                    if (isset($value['type']) && $value['type'] == $type ) {

                        $result[] = $value;
                        continue;

                    }

                    continue;
                    
                }

                if ($group) {

                    if (isset($value['group']) && $value['group'] == $group || (!isset($value['group']) && $group == 1 )) {

                        $result[] = $value;
                        continue;

                    }

                    continue;
                    
                }

                $result[] = $value;
            }
        }
        
        $result = $this->group($result);
       
        return $result;
    }

    private function group($result)
    {   
        $permissions = array();

        foreach ($result as $key => $value) {
            
            if(!isset($value['group'])) {

                $permissions[1][] = $value;

            }else {

                $permissions[$value['group']][] = $value;
            }
        }
        
        return $permissions;
    }

    private function parsePermissions($permissions=array()) 
    {   
        $kernel = new ServiceKernel();
        $kernel->instance();
       
        $environment = $kernel->getEnvironment();

        $permissionsCacheFile = "../app/cache/".$environment."permissions.yml";

/*
        if (file_exists($permissionsCacheFile)) {


            return Yaml::parse($permissionsCacheFile);

        }else {*/


            foreach (array('Topxia/WebBundle', 'Topxia/AdminBundle', 'Custom/WebBundle', 'Custom/AdminBundle') as $value) {
                
                $dir = "../src/".$value;
       
                if (file_exists($dir."/Resources/config/menus_admin.yml")) {

                    $permissions = $this->loadPermissionYml($permissions, $dir."/Resources/config/menus_admin.yml");
                }
          
            }

            $dir = "../plugins";
            $dh = opendir($dir);

            while ($file = readdir($dh)) {

                if ($file != "." && $file != "..") {

                  $fullpath = $dir."/".$file."/$file"."Bundle"."/Resources/config/menus_admin.yml";

                  $permissions = $this->loadPermissionYml($permissions, $fullpath);
                }
            }
            closedir($dh);

            file_put_contents($permissionsCacheFile, Yaml::dump($permissions));
         
        //}

        return $permissions;
    }

    public function getTitle($code)
    {
        $permissions = $this->parsePermissions();
        $title="";
        $permission = isset($permissions[$code]) ? $permissions[$code] : null;

        if($this->getNameByCode($code, $permissions)) {

            $title .= $this->getNameByCode($code, $permissions);
        }

        while ($permission['parent']) {
            
            $code = $permission['parent'];
            if($this->getNameByCode($code, $permissions)) {

                $title .= " - ";
                $title .= $this->getNameByCode($code, $permissions);
                
            }

            $permission = isset($permissions[$permission['parent']]) ? $permissions[$permission['parent']] : null;
        }

        return $title;
    }

    public function getFullTitle($code)
    {
        return $this->getTitle($code);
    }

    public function getTitle2($code)
    {
        $permissions = $this->parsePermissions();

        return $this->getNameByCode($code, $permissions);
    }

    private function getNameByCode($code, $permissions)
    {       
        if(isset($permissions[$code])) {

            $permission = $permissions[$code];
            
            return $permission['name'];
        }

        return null;

    }

    private function addCode($permissions)
    {
        foreach ($permissions as $key => $value) {
            
            $value['code'] = $key;

            $permissions[$key] = $value;
        }

        return $permissions;
    }

    private function sort($permissions)
    {   
        $i = 1;

        foreach ($permissions as $key => $value) {
            
            $permissions[$key]['weight'] = $i * 100;

            $i++;
        }

        foreach ($permissions as $key => $value) {
            
            if (isset($value['before'])) {

                $weight = $permissions[$value['before']]['weight'];

                $permissions[$key]['weight'] = $weight - 1;
            }

            if (isset($value['after'])) {

                $weight = $permissions[$value['after']]['weight'];

                $permissions[$key]['weight'] = $weight + 1;
            }

        }
        
        $permissions = ArrayToolkit::index($permissions, 'weight');

        ksort($permissions);

        return $permissions;
        
    }

    private function loadPermissionYml($permissions, $fullpath)
    {
        if (file_exists($fullpath)) {

            $permission = Yaml::parse($fullpath);

            if ($permission) {
                
                $permissions = array_merge($permissions, $permission); 
            }
            
        }

        return $permissions;
    }
}