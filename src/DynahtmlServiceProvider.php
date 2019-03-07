<?php
/**
 * Class DynahtmlServiceProvider
 * @package Picory\View
 * Author: picory <websniper@gmail.com>
 * Since: 2018-12-23
 * Version: 0.1
 */

namespace Picory\Dynahtml;

use Illuminate\Support\ServiceProvider;

class DynahtmlServiceProvider extends ServiceProvider
{
  public function boot()
  {
    $this->publishes([
      __DIR__ . '/../samples/config/config.php' => config_path('dynahtml.php'),
      __DIR__ . '/../samples/Controllers/DesignController.php' => app_path('Http/Controllers/DesignController.php'),
    ]);
  }

  public function register()
  {

  }
}