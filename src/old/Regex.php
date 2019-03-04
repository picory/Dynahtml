<?php
/**
 * Class Regex
 * @package Picory\Dynahtml
 * Author: picory <websniper@gmail.com>
 * Since: 2018-12-23
 * Version: 0.1
 */
namespace Picory\Dynahtml;

class Regex
{
  public static function images($source)
  {
    return static::getMatches("/<img[^>]*src=[\"\']?([^>\"\']+)[\"\']?[^>]*>/i", $source);
  }

  public static function scripts($source)
  {
    return static::getMatches("/<script[^>]*src=[\"\']?([^>\"\']+)[\"\']?[^>]*>/i", $source);
  }

  public static function css($source)
  {
    return static::getMatches("/<link[^>]*href=[\"\']?([^>\"\']+)[\"\']?[^>]*>/i", $source);
  }

  public static function links($source)
  {
    return self::getMatches("/<a[^>]*href=[\"\']?([^>\"\']+)[\"\']?[^>]*>/i", $source);
  }

  public static function contents($source)
  {
    return self::getMatches("/<p>(.*)<\/p>/i", $source);
  }

  public static function comments($source)
  {
    return self::getMatches("/<!--[^>]*@(.*)-->/i", $source);
  }

  public static function phpscripts($source)
  {
    return self::getMatches("/<\?php[^>]*(.*)[^>]*\?>/i", $source);
  }

  public static function values($source)
  {
    //return self::getMatches('/(\{\$)[\xEA-\xED\x80-\xBF0-9a-zA-Z\s\=\_\-\|\:\<\>\@\^\"\'\.\$\(\)\/]+\}/', $source);
    return self::getMatches('/\{\$(.*?)\}/i', $source);
  }

  public static function tagHeader($source, $tag = 'ul')
  {
    return self::getMatches('/<' . $tag . '[^>](.*?)>/i', $source);
  }

  public static function get_numbers($source)
  {
    return preg_replace("/[^0-9]*/s", "", $source);
  }

  public static function replace_escape($source)
  {
    return preg_replace("/[^\\]'/", '\'', $source);
  }

  public static function replace_quot($source)
  {
    return preg_replace('[\'|"]', '', $source);
  }

  private static function getMatches($pattern, $source)
  {
    preg_match_all($pattern, $source, $matches);
    return $matches;
  }

  public static function replace_value($source)
  {
    return preg_replace('/\{\$(.*?)\}/i', '<?php echo x(\'$1\'); ?>', $source);
  }

  public static function includeToTag($source)
  {
    return preg_replace('/<\?php[^>]include=*(.*)[^>]*\?>/i', '<include src=$1/>', $source);
  }

  public static function includeToPhp($source)
  {
    return preg_replace('/<include[^>]src=*(.*)[^>]*><\/include>/i', '<?php include $1 ?>', $source);
  }
}