<?php

namespace Codeages\Plumber;

use Swoole\Process;

class PidFile
{
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function read()
    {
        if (!file_exists($this->path)) {
            return false;
        }

        return intval(file_get_contents($this->path));
    }

    public function write($id)
    {
        if ($this->isRunning()) {
            return false;
        }
        $id = intval($id);
        file_put_contents($this->path, $id);

        return true;
    }

    public function destroy()
    {
        if (!file_exists($this->path)) {
            return;
        }

        unlink($this->path);
        clearstatcache();
    }

    public function isRunning()
    {
        if (!file_exists($this->path)) {
            return false;
        }

        $id = intval(file_get_contents($this->path));
        if (!Process::kill($id, 0)) {
            return false;
        }

        return true;
    }
}
