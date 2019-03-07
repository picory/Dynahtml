<?php
/**
 * Created by PhpStorm.
 * User: gzonemacpro
 * Date: 2019-03-08
 * Time: 00:45
 */
namespace Picory\Dynahtml\FileSystem;

use Picory\Dynahtml\FileSystem\Cache;
use League\Flysystem\FileNotFoundException;

class FileSystem
{
    public $source = '';
    public $cache = '';

    public function __construct($file)
    {
        $this->cache = new Cache($file);
        $this->source = $file;
    }

    public function exists($path)
    {
        return file_exists($path);
    }

    public function get()
    {
        dd($this->source);
    }

    public function read($path, $lock = false)
    {
        if ($this->isFile($path)) {
            return $lock ? $this->sharedGet($path) : file_get_contents($path);
        }

        throw new FileNotFoundException("File does not exist at path {$path}");
    }

    public function sharedGet($path)
    {
        $contents = '';

        $handle = fopen($path, 'rb');

        if ($handle) {
            try {
                if (flock($handle, LOCK_SH)) {
                    clearstatcache(true, $path);

                    $contents = fread($handle, $this->size($path) ?: 1);

                    flock($handle, LOCK_UN);
                }
            } finally {
                fclose($handle);
            }
        }

        return $contents;
    }

    public function fullPath()
    {
        dd($this->desginFile);
    }
}

