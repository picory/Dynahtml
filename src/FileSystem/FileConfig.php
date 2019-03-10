<?php
/**
 * Created by PhpStorm.
 * User: gzonemacpro
 * Date: 2019-03-09
 * Time: 00:58
 */

namespace Picory\Dynahtml\FileSystem;


class FileConfig
{
    public $file = '';
    public $pathSkin = '';
    public $pathDesign = '';
    public $pathCache = '';

    public function __construct($file)
    {
        $this->file = $file;
    }


    public function pathDesign()
    {
        return config('dynahtml.skin');
    }

}