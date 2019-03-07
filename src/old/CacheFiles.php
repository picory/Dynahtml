<?php
/**
 * Class CacheFiles
 * @package Picory\View
 * Author: picory <websniper@gmail.com>
 * Since: 2018-12-23
 * Version: 0.1
 */
namespace Picory\Dynahtml;

class CacheFiles
{
  private static $instance;
  private static $files;

  public static function instances()
  {
    if (!isset(CacheFiles::$instance)) CacheFiles::$instance = new CacheFiles();

    return CacheFiles::$instance;
  }

  public static function set($file, $type)
  {
    if (isset(self::$files[$type]) === false) {
      self::$files[$type] = [];
    }

    if (in_array($file, self::$files[$type]) === false) {
      self::$files[$type][] = $file;
    }
  }

  public static function get()
  {
    return self::$files;
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