<?php
/**
 * Created by PhpStorm.
 * User: gzonemacpro
 * Date: 2019-03-04
 * Time: 23:39
 */

namespace Picory\Dynahtml;

use Illuminate\Http\Request;

class Dynahtml
{
    public function __contruct()
    {}

    public function make(Request $request)
    {
        dd($request->path());
    }
}