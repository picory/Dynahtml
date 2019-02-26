<?php
/**
 * Class DesignController
 * @package App\Http\Controllers
 * Author: picory <websniper@gmail.com>
 * Since: 2018-12-23
 * Version: 0.1
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Picory\Dynahtml\Builder;
use Picory\Dynahtml\CacheFiles;

class DesignController extends Controller
{
  public $htmlPath = '';

  private $timerStart   = 0;
  private $timerEnd     = 0;

  /**
   * article/id 를 변환
   * @param array $params
   * @return string
   */
  static function article($params = [])
  {
    $targetFile = public_path() . $params['make'];

    $design = new DesignController();
    if (is_file($targetFile) === true) {
      ob_start();
      include $targetFile;
      $content = ob_get_contents();
      ob_end_clean();
    } else {
      $urlPath = $design->htmlPath . '/' . $params['mapping'];

      $content = $design->makeHtml($params, $urlPath);
      CacheFiles::save($targetFile, $content);
    }

    echo $design->appendix($content);
  }

  public function __construct()
  {
    $skin = config('dynahtml.skin');

    $this->htmlPath = resource_path('html/' . $skin);

    $this->timerStart = $this->microtime();
  }

  /**
   * html 파일을 찾아 builder로 전송
   * @param Request $request
   * @return Builder
   */
  public function html(Request $request)
  {
    // 기본 페이지용 파람 정리
    $params = $request->all();

    $url = $request->path();
    $urlPath = $this->htmlPath . '/' . $url;

    $content = $this->makeHtml($params, $urlPath);

    return $this->appendix($content);
  }

  public function makeHtml($params = [], $urlPath = '')
  {
    // 임사파일명을 확인
    $cacheFile = Builder::make($urlPath);

    $this->x('title', 'test page');

    ob_start();
    include $cacheFile;
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
  }

  private function val($item, $row = array())
  {
    // 입력한 데이터가 있다면
    if (count($row) > 0) {
      if (isset($row[$item])) {
        return $row[$item];
      }
    }

    if (empty($row)) {
      return $this->x($item);
    }

    return '';
  }

  /**
   * html에 실행시간을 추가
   * @param $html
   * @return string
   */
  public function appendix($html)
  {
    $this->timerEnd = $this->microtime();

    $tag = '

<!-- Used Memory : ' . $this->kb(memory_get_usage()) . ' -->
<!-- Peak Memory : ' . $this->kb(memory_get_peak_usage()) . ' -->
<!-- Ellapsed Time : '.($this->timerEnd - $this->timerStart).' sec -->';

    $html .= PHP_EOL . $tag;

    return $html;
  }


  private function microtime()
  {
    return array_sum(explode(' ', microtime()));
  }
}

/**
 * Class Data
 * @package App\Http\Controllers
 */
class Data
{
  static $data = [];

  static function get($key)
  {
    return isset(self::$data[$key]) ? self::$data[$key] : null;
  }

  static function set($key, $val)
  {
    self::$data[$key] = $val;
  }

  static function isEmpty($key)
  {
    return empty(self::$data[$key]);
  }

  static function all()
  {
    return self::$data;
  }
}