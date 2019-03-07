<?php
/**
 * Class Module
 * @package Picory\View
 * Author: picory <websniper@gmail.com>
 * Since: 2018-12-23
 * Version: 0.1
 */

namespace Picory\Dynahtml;

class Module
{
  public $source = array();
  public $headers = array();

  public static function set($html, $type = 'list')
  {
    $htmls = new Module;

    return $htmls->make($html, $type);
  }

  public function make($html, $type = 'list')
  {
    $tag = $html->getTag();

    call_user_func(array($this, $tag), $html);

    return $this->headers;
  }

  /**
   * param='{json}' 데이터 파싱
   * @param $html
   * @param $params
   * @return array
   */
  private function addParams($html, $params)
  {
    // 추가로 전달할 params 확인
    if (isset($html->attributes['params'])) {
      $addParams = $html->attributes['params'];
      $addParams = json_decode($addParams, true);
      $params = array_merge($params, $addParams);
    }

    return $params;
  }

  /**
   * module 영역을 PHP 코드로 변환
   * @param $html
   * @param string $mode
   */
  private function makePHPScript($html, $mode = 'list')
  {
    $startLoop = $mode === 'list' ? 0 : 1;

    // 하위 Loop가 존재하는지 확인
    $this->loops($html, $startLoop);

    list($class, $function) = $this->methods($html->module);

    $rand = sprintf('%05d', rand(0, 1000));
    $rows = 'DYN' . md5($html->module) . '_' . $rand;

    $phpScript = $mode === 'list' ? $this->listScript() : $this->noListScript();

  if ($mode === 'nolists') {
    dd($html->getOuterText());
  }

    // TODO. 중복된 header['use'] 보완 작업
    $this->headers['use'] = sprintf('use App\Http\Controllers\%s\%s;', ucfirst($class), ucfirst($function));

    $fields = Values::get($html, 'row');

    // 기본 전달할 파람을 확인
    $params['fields'] = $fields;
    if (isset($html->attributes['limit'])) {
      $params['limit'] = $html->attributes['limit'];
    }

    $params = $this->addParams($html, $params);

    // 컨트롤러 호출용 파람 + request 파람 추가
    $paramText = '$model_params = unserialize(urldecode(\'' . urlencode(serialize($params)) . '\'));' . PHP_EOL;
    $paramText .= '$model_params = $params ? array_merge($params, $model_params) : $model_params;' . PHP_EOL;
    $rs = '$%s = %s::get($model_params);' . PHP_EOL;
    $this->headers['rs'] = $paramText . sprintf($rs, $rows, ucfirst($function));

    $innerText = $html->getInnerText();

    $content = sprintf($phpScript, $rows, $rows, $rows, $rows, $rows, $innerText);

    // <PHP_CODE> 정리
    $content = str_replace('<PHP_CODE>', '\';', $content);
    $content = str_replace('</PHP_CODE>', '$content .= \'', $content);

    $this->filter($html);
    $html->setInnerText($content);
  }

  /**
   * HTML list인 스크립트 정리
   * @param $html
   */
  private function listScript()
  {
    return '<?php
if (is_array($%s) && count($%s) > 0) {
  $content = \'\';
  $rows = isset($%s[\'data\']) ? $%s[\'data\'] : $%s;
  foreach ($rows as $key => $row) {
    $content .= \'%s\';
  }
  echo $content;
}
?>
    ';
  }

  /**
   * HTML list 가 아닌 스크립트 정리
   * @return string
   */
  private function noListScript()
  {
    return '<?php
if (is_array($%s) && count($%s) > 0) {
  $content = \'\';
  $row = isset($%s[\'data\']) ? $%s[\'data\'] : $%s;
  $content .= \'%s\';
  echo $content;
}
?>
    ';
  }

  /**
   * List 목록 정리
   * @param $html
   * @param $tag
   */
  private function lists($html, $tag)
  {
    // attributes를 통해서 list인지 확인
    switch ($tag) {
      case 'div':
        if (isset($html->attributes['limit'])) {
          $this->makePHPScript($html);
          return true;
        } else {
          return false;
        }
        break;
    }

  }

  /**
   * div 영역 정리
   * @param $html
   */
  public function div($html)
  {
    $isList = false;

    // div는 특수 상황. module이 있으면 list 여부를 확인
    if (isset($html->attributes['module'])) {
      $isList = $this->lists($html, __FUNCTION__);
    }

    if ($isList) return;

    $this->makePHPScript($html, 'nolist');
  }

  /**
   * tr 영역 정리
   * @param $html
   */
  private function tbody($html)
  {
    $this->makePHPScript($html);
  }

  /**
   * li 영역 정리
   * @param $html
   */
  private function ul($html)
  {
    $this->makePHPScript($html);
  }

  private function ol($html)
  {
    $this->makePHPScript($html);
  }

  /**
   * 목록의 sublist가 있는 지 확인
   * @param $html
   */
  private function loops($html, $startLoop = 0)
  {
    foreach ($html('[loops]') as $key => $elem) {
      $loops = $this->makeLoops($elem, $startLoop);
//      echo $loops;exit;
      $elem->setOuterText($loops);
    }
 }

  private function makeLoops($html, $startLoop = 0)
  {
    $valueName = $html->loops;

    $this->filter($html);
    $headers = $this->headers($html, $valueName);
    $headerText = count($headers) ? sprintf('$content .= \'%s\';', implode(PHP_EOL, $headers)) : '';

    $content = '\';
if (isset($row[\'%s\'])) {
  $%s = $row[\'%s\'];
  %s
  if (count($%s) > 0) {
    $loops = isset($%s[\'data\']) ? $%s[\'data\'] : $%s;
    for ($' . 'i = ' . $startLoop . '; $' . 'i < count($%s); $' . 'i++) {
      $loop = $%s[$i];
      $content .= \'%s\';
    }
  }
}
$content .= \'';

    Values::get($html, 'loop');
    $lists = sprintf($content, $valueName, $valueName, $valueName, $headerText,
      $valueName, $valueName, $valueName, $valueName, $valueName, $valueName, $html->getInnerText());
    $html->setInnerText($lists);

    $content = '<PHP_CODE>
$content .= \'%s\';
</PHP_CODE>';
    return sprintf($content, $html->getOuterText());
  }

  /**
   * list 내용 중에서 상단 고정 값 = header
   * @param $html
   * @param $valName
   * @return array
   */
  private function headers($html, $valName)
  {
    // 고정 헤더 값이 있는지 확인
    $headers = [];
    foreach ($html('[header]') as $key => $elem) {
      Values::get($elem, $valName);
      $headers[] = $elem->getOuterText();
      $elem->setOuterText('');
    }

    return $headers;
  }


  /**
   * 불필요한 태그 attributes는 정리
   * @param $html
   */
  private function filter($html)
  {
    $filters = array('module', 'limit', 'loops', 'header', 'params');

    foreach ($html->attributes as $item => $value) {
      if (in_array($item, $filters)) {
        $html->attributes[$item] = null;

        unset($html->attributes[$item]);
      }
    }
  }

  /**
   * Methods 형식 : class1/class2/class2 > class1.class2::class3
   * @param string $module
   * @return array
   */
  private function methods($module = '')
  {
    if (stripos($module, '::')) {
      list($path, $class) = explode('::', $module);

      $path = $this->getClassPath($path);

      return array($path, $class);
    }

    return array('', '');
  }

  private function getClassPath($path)
  {
    if (stripos($path, '.')) {
      return str_replace('.', '\\', $path);
    }

    return $path;
  }

}