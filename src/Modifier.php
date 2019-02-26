<?php
/**
 * Class Modifier
 * @package Picory\Dynahtml
 * Author: picory <websniper@gmail.com>
 * Since: 2018-12-23
 * Version: 0.1
 */
namespace Picory\Dynahtml;

class Modifier
{
  static function set($value, $type = '')
  {
    $modi = new Modifier;

    return $modi->make($value, $type);
  }

  public function make($value, $type = '')
  {
    $info = explode('|', $value);
    $field = $info[0];

    if (count($info) === 1) {
      $modifier = $this->getValueText($this->makeValue($value, $type), $type);

      return array($field, $modifier);
    }

    $modifier = $info[1];

    $modifier = $this->makeModifier($modifier);
    if ($modifier === false) return '';

    $value = str_replace('[VALUE]', '%s', $modifier);
    $value = sprintf($value, $this->makeValue($info[0], $type));

    $modifier = $this->getValueText($value, $type);

    return array($field, $modifier);
  }

  private function getValueText($value, $type)
  {
    switch ($type) {
      case '':
        return sprintf('<?php echo %s; ?>', $value);
        break;
      default:
        return sprintf('\' . %s . \'', $value);
    }
  }

  private function makeModifier($modifier)
  {
    $info = explode('^', $modifier);
    $len = count($info);

    return call_user_func_array(array($this, 'modi' . $len), array($info));
  }

  private function modi1($info = array())
  {
    $command = $info[0];

    switch ($command) {
      case 'exec':    // 시스템 실행 명령어는 제외
      case 'system':
      case 'echo':
      case 'print_r':
      case 'sprintf':
        return false;
      case '':
        return $command;
      default:
        return sprintf('%s([VALUE])', $command);
    }
  }

  private function modi2($info = array())
  {
    list($command, $opt1) = $info;

    switch ($command) {
      case 'left':
        if (config('app.locale') === 'ko') {
          return sprintf('mb_strcut([VALUE], 0, %s)', $opt1);
        }
        else {
          return sprintf('substr([VALUE], 0, %s)', $opt1);
        }
        break;
      case 'right':
        return sprintf('substr([VALUE], strlen([VALUE]) - %s, %s)', $opt1, $opt1);
        break;
    }

    return sprintf('%s([VALUE], %s)', $command, $opt1);
  }

  private function modi3($info = array())
  {
    list($command, $opt1, $opt2) = $info;

//    switch ($command) {
//      case
//    }

    return sprintf('%s([VALUE], %s, %s)', $command, $opt1, $opt2);
  }

  private function makeValue($value, $type = '')
  {
    switch ($type) {
      case '':
        $valText = '$this->val(\'' . $value . '\')';
        break;
      default:
        $valText = '$this->val(\'' . $value . '\', $' . $type . ')';
    }

    return $valText;
  }
}