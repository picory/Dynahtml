<?php
/**
 * Class Parser
 * @package Picory\Dynahtml
 * Author: picory <websniper@gmail.com>
 * Since: 2018-12-23
 * Version: 0.1
 */
namespace Picory\Dynahtml;

include_once base_path('/vendor/ressio/pharse/pharse.php');
use Pharse;

class Parser extends Controller
{
  /**
   * @var string Builder Object
   */
  private $builder = '';

  /**
   * @var string
   */
  private $file = '';

  /**
   * @var array
   */
  private $headers = array();

  static function set(Builder $builder, $file)
  {
    $parser = new Parser($builder);

    // html object로 변환
    $html = $parser->html($file);

    return $html;
  }

  public function __construct(Builder $builder)
  {
    $this->builder = $builder;

    parent::__construct();
  }

  /**
   * @param $source
   */
  public function html($file)
  {
    // file 인 경우에는 정리 필요
    if (is_file($file)) {
      CacheFiles::set($file, 'include');

      $this->file = $file;
      $source = file_get_contents($file);

      // urlencode로 깨지는 문자열 정리
      $source = $this->protectUrlEncode($source);
    }

    // include, script 파일을 먼저 정리
    $source = $this->comments($source);
    $source = $this->makeHtml($source);

    // TODO. Error 처리 필요
    $headerScript = '';
    $rsScript = '';
    if (isset($this->headers['use'])) {
      $this->headers['use'] = $this->reArrange($this->headers['use']);
      $headerScript = implode(PHP_EOL, $this->headers['use']);
    }
    if (isset($this->headers['rs'])) {
      $this->headers['rs'] = $this->reArrange($this->headers['rs']);
      $rsScript = implode(PHP_EOL, $this->headers['rs']);
    }

    $head = '<?php
%s
%s?>';
    $header = sprintf($head, $headerScript, $rsScript);

    if (isset($this->headers['php'])) {
      $header = $this->headers['php'] . $header;
    }

    $source = $header . $source;
    $source = $this->protectUrlEncode($source, 'decode');

    // css, js 파일을 head, body 영역에 복사
    $source = $this->updateAssets($source);

    return $source;
  }

  /**
   * Array 값에서 빈값은 제거
   * @param array $source
   * @return array
   */
  public function reArrange($source = [])
  {
    foreach ($source as $key => $value) {
      if (empty($value) || $value === null) {
        unset($source[$key]);
      }
    }

    return $source;
  }


  /**
   * HTML object로 변경 후 html 항목을 추적
   * @param $source
   * @return string
   */
  private function makeHtml($source)
  {
    // html source를 object로 바꾸고 본격적인 작업 진행
    $html = Pharse::str_get_dom($source);
    $this->modules($html);

    Values::get($html);

    return $html->getInnerText();
  }

  private function updateAssets($source)
  {
    $config = CacheFiles::get();

    $documentRoot = $this->documentRoot();
    $designRoot = $this->designRoot();


    $type = 'css';

    $addScript = '';
    $jsScript = '';
    if (isset($config[$type])) {
      foreach ($config[$type] as $file) {
        $script = str_replace($designRoot, '', $file);
        $target = $documentRoot . $script;

        copy($file, $target);

        switch ($type) {
          case 'css':
            $addScript .= sprintf('  <link rel="stylesheet" href="%s" />' . PHP_EOL, $script);
            break;
          case 'js':
            $addScript .= sprintf('  <link rel="preload" href="%s" as="script" />' . PHP_EOL, $script);
            $jsScript .= sprintf('  <script src="%s"></script>' . PHP_EOL, $script);
            break;
        }
      }

      switch ($type) {
        case 'js':
          $source = str_replace('</body>', $jsScript . '</body>', $source);
        case 'css':
          $source = str_replace('</head>', $addScript . '</head>', $source);
          break;
      }
    }

    return $source;
  }

  /**
   * <tag module= 찾기
   * @param $html
   * @return mixed
   */
  private function modules($html)
  {
    foreach ($html('[module]') as $key => $elem) {
      $header = Module::set($elem);

      $this->headers['use'][] = isset($header['use']) ? $header['use'] : array();
      $this->headers['rs'][] = isset($header['rs']) ? $header['rs'] : array();
      $this->headers['js'][] = isset($header['js']) ? $header['js'] : array();
      $this->headers['css'][] = isset($header['css']) ? $header['css'] : array();
    }

    if (isset($this->headers['use'])) {
      $this->headers['use'] = $this->uniqueArray($this->headers['use']);
    }
  }

  /**
   * headers의 중복 함수들은 정리함
   * @param array $source
   * @return array
   */
  private function uniqueArray($source = [])
  {
    if (is_array($source) === false) return array();

    $target = [];
    foreach ($source as $key => $value) {
      if (in_array($value, $target)) continue;

      $target[] = $value;

    }

    return $target;
  }

  /**
   * HTML에서 <!-- @ 내용 찾기
   * @param $source
   * @return false|mixed|string
   */
  private function comments($source)
  {
    // <!-- @ 내용을 정리
    $comments = Regex::comments($source);

    // 하위 접근 내용이 없으면 종료
    if (count($comments[0]) < 1) return $source;

    foreach ($comments[1] as $key => $item) {
      list($type, $file) = $this->commentsInfo($item);

      // 호출된 파일 목록 저장
      CacheFiles::set($file, $type);

      switch (trim($type)) {
        case 'layout':
          // layout 상단 php 스크립트는 header로 정리
          $this->layoutScript($source, $comments[0][$key]);

          // layout 파일 정리
          $source = str_replace($comments[0][$key], '', $source);
          $source = $this->layout($file, $source);

          break;
        case 'include':
          // include 내용을 별도로 저장
          $cacheFile = $this->cacheFilename($file);
          $includes = sprintf('<?php include "%s"; ?>', $cacheFile);
          $source = str_replace($comments[0][$key], $includes, $source);

          $this->includes($file, $cacheFile);
          break;
        case 'css':
        case 'js':
          $source = str_replace($comments[0][$key], '', $source);

          CacheFiles::set($file, $type);

          break;
        default:  // 위에 것을 제외하고 머가 있을까?
          // 일단 주석은 모두 삭제
          $source = str_replace($comments[0][$key], '', $source);

          break;
      }
    }

    return $source;
  }

  /**
   * layout을 기준으로 html을 재작성
   * @param $source
   * @param $file
   * @return false|mixed|string
   */
  private function layout($file, $source)
  {
    $content = file_get_contents($file);

    if (isset($this->headers['head'])) {
      $content = $this->headers['head'] . $content;
    }

    $cache = $this->cacheFilename($file);
    CacheFiles::save($cache, $content);

    $content = str_replace('{$contents}', trim($source), $content);

    // layout과 본문은 통합해서 재처리
    $content = $this->comments($content);

    return $content;
  }

  /**
   * @include 파일은 별도 저장
   * @param $file
   * @param $cache
   */
  private function includes($file, $cache)
  {
    $source = file_get_contents($file);
    $source = $this->comments($source);
//    $source = $this->makeHtml($source);

    CacheFiles::save($cache, $source);
  }

  /**
   * <!-- @layout 상단 php 스크립트는 별도 관리
   * @param $source
   * @param $target
   */
  private function layoutScript($source, $target)
  {
    $layoutPos = strpos($source, $target);
    $headerScript = substr($source, 0, $layoutPos);

    $this->headers['head'] = trim($headerScript) . PHP_EOL;
  }
}