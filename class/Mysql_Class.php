<?php

class Mysql_Class
{
    protected string $dbhost;
    protected string $dbname;
    protected string $dbuser;
    protected string $dbpass;
    protected string $pathbk;

    public function __construct(string $dbhost, string $dbname, string $dbuser, string $dbpass, string $pathbk)
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

        if ($resultdump != '') {
            unlink($filebk);
            throw new Exception("BACKUP generation error DUMP " . $resultdump);
        }

        return $filebk;
    }
}
