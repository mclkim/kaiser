<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-04-22
 * Time: 오후 3:34
 */

namespace Kaiser;


class Appp extends Controller
{
    public function run()
    {
//        phpinfo();
        $this->start();
//        $func = $this->container->get('settings');
        var_dump($this->container);
//        phpinfo();
        $this->end();
    }

    protected function start()
    {
        /**
         * 시작을 로그파일에 기록한다.
         */
        $this->info(sprintf('<<START>>The Class "%s" Initialized ', get_class($this)));
        /**
         * 타임스템프를 기록..
         */
        $this->timestamp = new \Kaiser\Timer ();
    }

    protected function end()
    {
        /**
         * 타임스템프를 기록한 시간 차이를 계산하여 기록한다.
         * 사용한 메모리를 기록한다.
         */
        $this->info(sprintf('<<END>>The Class "%s" total execution time: ', get_class($this)) . $this->timestamp->fetch() . ", Memory used: " . bytesize(memory_get_peak_usage()));
    }
}