<?php
/**
 * Class Values
 * @package Picory\Dynahtml
 * Author: picory <websniper@gmail.com>
 * Since: 2018-12-23
 * Version: 0.1
 */
namespace Picory\Dynahtml;

class Values
{
  /**
   * 변수 변환 호출 함수
   * @param $html
   * @param string $type
   * @return array
   */
  static function get($html, $type = '')
  {
    $value = new Values;

    return $value->fields($html, $type);
  }

  /**
   * 확인된 변수는 모두 fields 정보로 정리
   * @param $html
   * @param string $type
   */
  public function fields($html, $type = 'row')
  {
    $source = $html->getInnerText();
    $values = Regex::values($source);

    $fields = array();

    foreach ($values[1] as $key => $value) {
      list($value, $modifier) = Modifier::set($value, $type);

      $fields[] = $value;
      $source = str_replace($values[0][$key], $modifier, $source);
    }

    $html->setInnerText($source);

    if (count($fields) > 0) {
      $fields = array_unique($fields);
    }

    return $fields;
  }
}