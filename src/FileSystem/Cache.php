<?php
/**
 * Created by PhpStorm.
 * User: gzonemacpro
 * Date: 2019-03-08
 * Time: 00:51
 */

namespace Picory\Dynahtml\FileSystem;

class Cache
{
    public $skin = '';

    public $designRootPath = '';


    public function __construct($file)
    {
        dd($file);
        $this->skin = config('dynahtml.skin');
        $this->designRootPath = resource_path('html/' . $this->skin);
    }

    public function set($file, $type)
    {
        if (isset(self::$files[$type]) === false) {
            self::$files[$type] = [];
        }

        if (in_array($file, self::$files[$type]) === false) {
            self::$files[$type][] = $file;
        }
    }

    public function get()
    {
        return self::$files;
    }


    public function filename()
    {

    }
    /**
     * 임시 파일 저장
     * @param $file
     * @param $contents
     * @return bool
     */
    public static function save($file, $content)
    {
        if (self::make($file) === false) return false;

        file_put_contents($file, $content);

        if (is_file($file)) {
            chmod($file, 0777);
        }

        return true;
    }

    /**
     * 디렉토리 생성
     * @param $file
     * @return bool
     */
    public static function make($file)
    {
        if (is_file($file)) return true;

        $info = explode('/', $file);
        $last = count($info) - 1;

        $info[$last] = null;
        unset($info[$last]);

        $dir = implode('/', $info);

        if (is_dir($dir)) return true;

        return mkdir($dir, 0777, true);
    }
}