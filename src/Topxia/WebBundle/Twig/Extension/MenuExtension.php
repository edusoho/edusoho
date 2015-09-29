<?php
namespace Topxia\WebBundle\Twig\Extension;

use Symfony\Component\Yaml\Yaml;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\MenuBuilder;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class MenuExtension extends \Twig_Extension
{
    protected $container;

    protected $menuUtil = null;

    protected $builders = array();


    public function __construct ($container)
    {
        $this->container = $container;
    }

    public function getFilters ()
    {
        return array(
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('menu_children', array($this, 'getMenuChildren')),
            new \Twig_SimpleFunction('menu_breadcrumb', array($this, 'getMenuBreadcrumb')),
            new \Twig_SimpleFunction('menu_path', array($this, 'getMenuPath'), array('needs_context' => true, 'needs_environment' => true)),
            new \Twig_SimpleFunction('in_menu_blacklist', array($this, 'inMenuBlacklist')),
            new \Twig_SimpleFunction('hiddenMenus', array($this, 'hiddenMenu')),
        );
    }

    public function hiddenMenu()
    {
        $result = CloudAPIFactory::create('leaf')->get('/me');
        if(isset($result['thirdCopyright']) and $result['thirdCopyright'] == '1'){
        $this->addMenuCodeToBlackList(array('admin_app'));
        }
        if(isset($result['thirdCopyright']) and $result['thirdCopyright'] == '0'){
        $this->removeMenuCodeToBlackList(array('admin_app'));
        }  
    }

    public function getMenuPath($env, $context, $menu)
    {
        $route = empty($menu['router_name']) ? $menu['code'] : $menu['router_name'];
        $params = empty($menu['router_params']) ? array() : $menu['router_params'];

        if (!empty($menu['router_params_context'])) {
            foreach ($params as $key => $value) {
                $value = explode('.', $value, 2);
                $params[$key] = $context['_context'][$value[0]][$value[1]];

            }
        }

        return $this->container->get('router')->generate($route, $params);
    }

    public function inMenuBlacklist($code = '')
    {
        if(empty($code)){
            return false;
        }
        $filename = $this->container->getParameter('kernel.root_dir') . '/../app/config/menu_blacklist.yml';
        if(!file_exists($filename)){
            return false;
        }
        $yaml = new Yaml();
        $blackList = $yaml->parse(file_get_contents($filename));
        if(empty($blackList)){
            $blackList = array();
        }
        return in_array($code, $blackList);
    }

    public function getMenuChildren($position, $code, $group = null)
    {
        return $this->createMenuBuilder($position)->getMenuChildren($code, $group);
    }

    public function getMenuBreadcrumb($position, $code)
    {
        return $this->createMenuBuilder($position)->getMenuBreadcrumb($code);
    }

    private function addMenuCodeToBlackList($codes = array())
    {
        $yaml = new Yaml();
        if(!is_array($codes)){
            return;
        }
        $filename = $this->container->getParameter('kernel.root_dir') . '/../app/config/menu_blacklist.yml';
        if(!file_exists($filename)){
            $content = $yaml->dump($codes);
            $file = fopen($filename,"w");
            fwrite($file,$content);
            fclose($file);
        }else{
            $blackList = $yaml->parse(file_get_contents($filename));
            if(empty($blackList)){
                $blackList = array();
            }
            $addCodes = array_diff($codes, $blackList);
            if(!empty($addCodes)){
                foreach ($addCodes as $addCode) {
                    array_push($blackList, $addCode);    
                }
                $content = $yaml->dump($blackList);
                $file = fopen($filename,"w");
                fwrite($file,$content);
                fclose($file);
            }
        }
    }

    public function removeMenuCodeToBlackList($codes =array())
    {
        $yaml = new Yaml();
        if(!is_array($codes)){
            return;
        }
        $filename = $this->container->getParameter('kernel.root_dir') . '/../app/config/menu_blacklist.yml';
        if(!file_exists($filename)){
            return;
        }
        else{
            $blackList = $yaml->parse(file_get_contents($filename));
            if(empty($blackList)){
                $blackList = array();
            }
            $diffCodes = array_diff($blackList,$codes);

            $array = array();
            if(!empty($diffCodes)){
                foreach ($diffCodes as $diffcode) {
                    array_push($array,$diffcode);
                }
                $content = $yaml->dump($array);
                $file = fopen($filename,"w");
                fputs($file,$content);
                fclose($file);
            }else{
                $file = fopen($filename,"w");
                fputs($file,null);
                fclose($file);
            }
        }



    }

    private function createMenuBuilder($position)
    {
        if (!isset($this->builders[$position])) {
            $this->builders[$position] = new MenuBuilder($position);
        }
        return $this->builders[$position];
    }

    public function getName ()
    {
        return 'topxia_menu_twig';
    }

}


