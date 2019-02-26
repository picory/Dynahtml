<?php
/**
 * Created by PhpStorm.
 * Users: gzonemacpro
 * Date: 2019-01-02
 * Time: 23:03
 */

namespace App\Http\Controllers;


class Controller
{

  /**
   * GraphSQL 약식 표현
   *  queries=class:function,class:function
   * @param array $params
   * @return bool|mixed
   */
  public function queries($params = [])
  {
    if (isset($params['queries']) === false) return false;

    $queries = $params['queries'];
    $params = explode(',', $queries);

    return $this->targets($params);
  }

  /**
   * GraphSQL 형태를 실행하기 위한 곳
   * @param array $params
   * @return mixed
   */
  public function targets($params = [])
  {
    foreach ($params as $target) {
      $targets = explode(':', $target);

      switch (count($targets)) {
        case 1:
          $rows[$target] = call_user_func(array($this, $targets[0]));
          break;
        case 2:
          list($class, $function) = $targets;
          $localFunction = sprintf('%s_%s', $class, $function);

          // local 함수가 존재하면 실행
          if (method_exists($this, $localFunction)) {
            $rows[$target] = call_user_func(array($this, $localFunction));
          } else {
            // 없으면 전역 함수를 실행. 아직은 사용하면 안됨
            $rows[$target] = call_user_func(array($class, $function));
          }
          break;
      }
    }

    return $rows;
  }

  /**
   * 전역 변수 선언
   * @param $item
   * @param string $value
   * @return mixed|null
   */
  public function x($item, $value = '')
  {
    if (empty($value)) {
      return Data::get($item);
    }

    Data::set($item, $value);
  }

  /**
   * number > kbytes
   * @param $source
   * @return string
   */
  public function kb($source)
  {
    $source = intval($source / 100) / 10;

    return sprintf("%0.1f KBytes", $source);
  }

  /*
   * Static Functions
   */

  /**
   * @param $func
   * @param $request
   * @return mixed
   */
  static function get($params = array(), $type = 'array')
  {
    $className = get_called_class();

    $object = new $className();
    $results = call_user_func(array($object, 'action'), $params);

    switch ($type) {
      case 'json':
        $rows = json_encode($results);
        break;
      default:
        $rows = $results;
    }

    return $rows;
  }
}