<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Picory\Dynahtml\View;

class DesignController extends Controller
{
    /**
     * HTML 파일 만들기
     * @param Request $request
     * @return string
     */
    public function view(Request $request)
    {
        $factory = new View();

        $content = $factory->make($request);

        return $this->appendix($content);
    }

    /**
     * html 하단에 실행시간, 메모리 사용량 추가
     * @param $html
     * @return string
     */
    public function appendix($html)
    {
        $this->timerEnd = $this->microtime();

        $tag = '

<!-- Used Memory : ' . $this->kb(memory_get_usage()) . ' -->
<!-- Peak Memory : ' . $this->kb(memory_get_peak_usage()) . ' -->
<!-- Ellapsed Time : ' . ($this->timerEnd - $this->timerStart) . ' sec -->';

        $html .= PHP_EOL . $tag;

        return $html;
    }

    /**
     * check Current Data time
     * @return float|int
     */
    private function microtime()
    {
        return array_sum(explode(' ', microtime()));
    }
}