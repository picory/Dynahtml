<?php
/**
 * Class Builder
 * @package Picory\Dynahtml
 * Author: picory <websniper@gmail.com>
 * Since: 2018-12-23
 * Version: 0.1
 */

namespace Picory\Dynahtml;

class Builder extends Controller
{
  /**
   * @var string 호출할 HTML 파일명
   */
  public $path = '';

  /**
   * @param string $path
   * @return Builder
   */
  static function make($path = '')
  {
    $builder = new Builder($path);

    return $builder->html();
  }

  public function __construct($path = '')
  {
    $this->path = $this->indexFile($path);
  }

  /**
   * design file로 html source 변환
   * @return false|mixed|string
   */
  public function html()
  {
    $cacheFile = $this->cacheFilename($this->path);
    $configFile = $cacheFile . '.config';

    // 업데이트 항목이 있으면 html 업데이트
    if ($this->checkUpdate($configFile) || $this->rebuild()) {
      $html = Parser::set($this, $this->path);

      // 원본 html 파일을 저장
      CacheFiles::save($cacheFile, $html);

      $config = CacheFiles::get();
      $content = '<?php
return %s;
';
      // 설정 파일을 저장
      $config = 'array(' . PHP_EOL . $this->printArray($config) . ')';
      $content = sprintf($content, $config);

      CacheFiles::save($configFile, $content);
    }

    return $cacheFile;
  }

  /**
   * 디자인 파일이 업데이트 되었는지 확인
   * @param $checkFile
   * @return bool
   */
  private function checkUpdate($checkFile)
  {
    // 설정 파일이 없으면 무조건 갱신 처리
    if (is_file($checkFile) === false) return true;

    // config ㅍㅏ일에 포함된 파일 목록을 검사
    $originFiles = include $checkFile;

    foreach ($originFiles['include'] as $file) {
      $cacheFile = $this->cacheFilename($file);

      if ($this->newOriginFile($file, $cacheFile)) return true;
    }

    // layout file 확인
    $origin = $this->path;
    $cacheFile = $this->cacheFilename($origin);
    if ($this->newOriginFile($origin, $cacheFile)) return true;

    return false;
  }

  /**
   *  html source가 업데이트 되었는지 확인
   * @param $origin
   * @param $cache
   * @return bool
   */
  private function newOriginFile($origin, $cache)
  {
    if (is_file($origin) === false) return true;
    if (is_file($cache) === false) return true;

    $origMtime = filemtime($origin);
    $cacheMtime = filemtime($cache);

    if ($origMtime > $cacheMtime) {
//      echo $origin . PHP_EOL;
//      echo $origMtime . PHP_EOL;
//      echo $cacheMtime . PHP_EOL;
      return true;
    }

    return false;
  }

  /**
   * Array Pretty print
   * @param $data
   * @param int $depth
   * @return string
   */
  private function printArray($data, $depth = 0)
  {
    $prefix = '  ';

    $array = '';
    if (is_array($data)) {
      $addPrefix = '';

      for ($i = 0; $i <= $depth; $i++) {
        $addPrefix .= $prefix;
      }

      foreach ($data as $item => $value) {
        if (is_array($value)) {
          $add = $addPrefix . '\'%s\' => array(' . PHP_EOL . $this->printArray($value, $depth++) . $addPrefix. '),' . PHP_EOL;
          $array .= sprintf($add, $item);
        } else {
          $add = $addPrefix . '\'%s\' => \'%s\',' . PHP_EOL;
          $array .= sprintf($add, $item, $value);
        }
      }
    }

    return $array;
  }


  /**
   * main.html 파일로 치환
   * @param string $path
   * @return string
   */
  private function indexFile($path = '')
  {
    if (ends_with($path, 'html') === false) {
      $path .= (ends_with($path, '/') ? '' : '/') . 'index.html';
    }
    if (is_file($path)) return $path;

    abort(404);
  }

  private function rebuild()
  {
    return isset($_GET['rebuild']) || isset($_POST['rebuild']);
  }
}