<?php
/**
 * Class Controller
 * @package Picory\DynaView
 * Author: picory <websniper@gmail.com>
 * Since: 2018-12-23
 * Version: 0.1
 */
namespace Picory\Dynahtml;

class Controller
{
  public $resourcePath = '';

  public function __construct()
  {
    $skin = request()->server('SKIN');
    $documentRoot = request()->server('DOCUMENT_ROOT');

    $this->resourcePath = sprintf('%s/dynahtml/%s', $documentRoot, $skin);
  }

  /**
   * magic method get
   * @param $property
   * @return string
   */
  public function __get($property)
  {
    if (property_exists($this, $property) === true) return $this->$property;

    return '';
  }

  /**
   * magic method set
   * @param $property
   * @param $value
   * @return $this
   */
  public function __set($property, $value)
  {
    if (property_exists($this, $property)) $this->$property = $value;

    return $this;
  }

  public function protectUrlEncode($source, $type = 'encode')
  {
    switch ($type) {
      case 'encode':
        return str_replace('+', '[xx]', $source);
      case 'decode':
        return str_replace('[xx]', '+', $source);
    }
  }

  /**
   * include|layout|css|js file 변환
   * @param $source
   * @return array
   */
  public function commentsInfo($source)
  {
    $info = explode(' ', $source);

    // type, file 형태가 아니면 종료
    if (count($info) < 2) return array('', '');

    $type = $info[0];
    $file = $info[1];
    $file = Regex::replace_quot($file);

    $filePath = $this->resourcePath . (starts_with($file, '/') ? '' : '/') . $file;


    return array($type, trim($filePath));
  }

  /**
   * 디자인 캐시 파일 위치 확인
   * @param $file
   * @return mixed
   */
  public function cacheFilename($file)
  {
    $cacheFile = str_replace(base_path(), storage_path('temp/compile'), $file);
    $cacheFile = str_replace('/dynahtml/', '/', $cacheFile);

    return $cacheFile;
  }


  /**
   * documentRoot 위치 확인
   * @return string
   */
  public function documentRoot()
  {
    return request()->server('DOCUMENT_ROOT');
  }

  /**
   * Design root 위치 확인
   * @return string
   */
  public function designRoot()
  {
    return $this->resourcePath;
  }
}