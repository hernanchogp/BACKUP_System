<?php

class Mysql_Class
{
    protected  $dbhost;
    protected  $dbname;
    protected  $dbuser;
    protected  $dbpass;
    protected  $pathbk;

    public function __construct($dbhost, $dbname,  $dbuser,  $dbpass, $pathbk)
    {
        $this->dbhost = $dbhost;
        $this->dbname = $dbname;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpass;
        $this->pathbk = $pathbk;
    }
    public function generarDump()
    {
        $filebk = $this->pathbk . '/' . $this->dbname . "-" . date("Y-m-d-H-i-s") . ".sql";
        $resultdump = shell_exec("mysqldump --user={$this->dbuser} --password={$this->dbpass} --host={$this->dbhost} {$this->dbname} --result-file={$filebk} 2>&1");
        //die("<pre>" . print_r($resultdump, true) . "</pre>");
        $error = strpos($resultdump, 'Got error');
        if ($error !== false) {
            unlink($filebk);
            throw new Exception("BACKUP generation error DUMP " . $resultdump);
        }

        return $filebk;
    }
}
