<?php
class Print_Class
{
    private $total = 0;
    private $msg = '';

    private $start_time = 0;
    private $last_time = 0;

    public function __construct($total, $msg = '')
    {
        $this->total = $total;
        $this->msg = $msg;
        $this->last_time = 0;
        $this->start_time = time();
        $this->render();
    }
    public function finish()
    {
        $this->render();
        echo "\n";
    }

    public function render($msg = null, $progress = 0)
    {
        $msg = $msg ? "{$msg}" : $this->msg;
        $_c = chr(27);
        $_rb = "{$_c}[41m";      // red background
        $_gb = "{$_c}[42m";      // green background
        $_yb = "{$_c}[43m";      // yellow background
        $_bb = "{$_c}[44m";      // blue background
        $_df = "{$_c}[30m";      // dark foreground
        $_rf = "{$_c}[31m";      // red foreground
        $_gf = "{$_c}[32m";      // green foreground
        $_yf = "{$_c}[33m";      // yellow foreground
        $_bf = "{$_c}[34m";      // blue foreground
        $_mf = "{$_c}[35m";      // magenta foreground
        $_cf = "{$_c}[36m";      // cyan foreground
        $_wf = "{$_c}[37m";      // white foreground
        $_r = "{$_c}[0m";        // color reset
        $t = strlen($this->total);
        $time_used = time() - $this->start_time;
        echo sprintf(
            "\r {$_mf} %s {$_rf}%{$t}d/%d{$_wf}  {$_gf}%2dd, %02d:%02d:%02d{$_r} ",
            $msg,
            $progress,
            $this->total,
            $time_used / 86400,
            $time_used / 3600 % 24,
            $time_used / 60 % 60,
            $time_used % 60,

        );
        $this->last_time = microtime(true);
    }
}
