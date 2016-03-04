<?php
namespace Topxia\Service\Common;

use Topxia\Service\Common\BaseService;
use Symfony\Component\Filesystem\Filesystem;

class KeywordFilter extends BaseService
{
    protected $tree = array();

    public function initTree()
    {
        if (!empty($this->tree)) {
            return;
        }

        $file       = $this->getKernel()->getParameter('kernel.root_dir').'/data/keywords.php';
        $filesystem = new Filesystem();

        if ($filesystem->exists($file)) {
            $this->tree = include_once $file;
        }
    }

    public function addKeywords(array $keywords)
    {
        foreach ($keywords as $keyword) {
            $keyword = trim($keyword);
            $this->insert($keyword);
        }

        $file       = $this->getKernel()->getParameter('kernel.root_dir').'/data/keywords.php';
        $filesystem = new Filesystem();

        if ($filesystem->exists($file)) {
            $filesystem->remove($file);
        }

        $fileContent = "<?php \nreturn ".var_export($this->tree, true).";";
        file_put_contents($file, $fileContent);
    }

    public function insert($utf8Str)
    {
        $this->initTree();

        $chars   = $this->getChars($utf8Str);
        $chars[] = null; //串结尾字符
        $count   = count($chars);
        $tree    = &$this->tree;

        for ($i = 0; $i < $count; $i++) {
            $c = $chars[$i];

            if (!array_key_exists($c, $tree)) {
                $tree[$c] = array(); //插入新字符，关联数组
            }

            $tree = &$tree[$c];
        }
    }

    public function filter($utf8Str)
    {
        $chars  = $this->getChars($utf8Str);
        $count  = count($chars);
        $tree   = &$this->tree;
        $indexs = array();

        for ($i = 0; $i < $count; $i++) {
            $result = $this->iterate($chars, $i, $tree, $i);

            if (is_array($result)) {
                list($start, $end, $i) = $result;
                $indexs[]              = array($start, $end);
            } else {
                $i = $result;
            }
        }

        foreach ($indexs as $value) {
            $start = $value[0];
            $end   = $value[1];

            for ($i = $start; $i <= $end; $i++) {
                $chars[$i] = '*';
            }
        }

        $str = implode('', $chars);
        return $str;
    }

    public function iterate($chars, $i, $tree, $start)
    {
        $c = $chars[$i];

        if (array_key_exists($c, $tree)) {
            $tree = &$tree[$c];

            if (array_key_exists("", $tree)) {
                $end = $i;
                return array($start, $end, $i);
            } else {
                $i++;

                if ($i >= count($chars)) {
                    return $i;
                }

                $result = $this->iterate($chars, $i, $tree, $start);

                if (is_array($result)) {
                    return $result;
                } else {
                    return $i;
                }
            }
        } else {
            return $i;
        }
    }

    public function remove($utf8Str)
    {
        $chars   = $this->getChars($utf8Str);
        $chars[] = null;

        if ($this->_find($chars)) {
            //先保证此串在树中
            $chars[] = null;
            $count   = count($chars);
            $tree    = &$this->tree;

            for ($i = 0; $i < $count; $i++) {
                $c = $chars[$i];

                if (count($tree[$c]) == 1) {
                    //表明仅有此串
                    unset($tree[$c]);
                    return;
                }

                $tree = &$tree[$c];
            }
        }

        $file       = $this->getKernel()->getParameter('kernel.root_dir').'/data/keywords.php';
        $filesystem = new Filesystem();

        if ($filesystem->exists($file)) {
            $filesystem->remove($file);
        }

        $fileContent = "<?php \nreturn ".var_export($this->tree, true).";";
        file_put_contents($file, $fileContent);
    }

    private function _find(&$chars)
    {
        $count = count($chars);
        $tree  = &$this->tree;

        for ($i = 0; $i < $count; $i++) {
            $c = $chars[$i];

            if (!array_key_exists($c, $tree)) {
                return false;
            }

            $tree = &$tree[$c];
        }

        return true;
    }

    public function find($utf8Str)
    {
        $chars   = $this->getChars($utf8Str);
        $chars[] = null;
        return $this->_find($chars);
    }

    public function contain($utf8Str, $doCount = 0)
    {
        $chars   = $this->getChars($utf8Str);
        $chars[] = null;
        $len     = count($chars);
        $tree    = &$this->tree;
        $count   = 0;

        for ($i = 0; $i < $len; $i++) {
            $c = $chars[$i];

            if (array_key_exists($c, $tree)) {
                //起始字符匹配
                $subTree = &$tree[$c];

                for ($j = $i + 1; $j < $len; $j++) {
                    $c = $chars[$j];

                    if (array_key_exists(null, $subTree)) {
                        if ($doCount) {
                            $count++;
                        } else {
                            return true;
                        }
                    }

                    if (!array_key_exists($c, $subTree)) {
                        break;
                    }

                    $subTree = &$subTree[$c];
                }
            }
        }

        if ($doCount) {
            return $count;
        } else {
            return false;
        }
    }

    public function containAll($strArray)
    {
        foreach ($str_array as $str) {
            if ($this->contain($str)) {
                return true;
            }
        }

        return false;
    }

    public function export()
    {
        return serialize($this->tree);
    }

    public function import($str)
    {
        $this->tree = unserialize($str);
    }

    public function getChars($utf8Str)
    {
        $s   = $utf8Str;
        $len = strlen($s);

        if ($len == 0) {
            return array();
        }

        $chars = array();

        for ($i = 0; $i < $len; $i++) {
            $c = $s[$i];
            $n = ord($c);

            if (($n >> 7) == 0) {
                //0xxx xxxx, asci, single
                $chars[] = $c;
            } else
            if (($n >> 4) == 15) {
                //1111 xxxx, first in four char

                if ($i < $len - 3) {
                    $chars[] = $c.$s[$i + 1].$s[$i + 2].$s[$i + 3];
                    $i += 3;
                }
            } else
            if (($n >> 5) == 7) {
                //111x xxxx, first in three char

                if ($i < $len - 2) {
                    $chars[] = $c.$s[$i + 1].$s[$i + 2];
                    $i += 2;
                }
            } else
            if (($n >> 6) == 3) {
                //11xx xxxx, first in two char

                if ($i < $len - 1) {
                    $chars[] = $c.$s[$i + 1];
                    $i++;
                }
            }
        }

        return $chars;
    }
}
