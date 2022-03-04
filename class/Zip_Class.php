<?php

class Zip_Class  extends ZipArchive
{
    protected string $pathSource;
    protected string $pathDestination;

    public function __construct(string $pathSource,  string $pathDestination)
    {
        $this->pathSource = $pathSource;
        $this->pathDestination = $pathDestination;
    }

    public function addDir($location, $name)
    {
        $this->addEmptyDir($name);
        $this->addDirFull($location, $name);
    }
    private function addDirFull($location, $name)
    {
        $name .= '/';
        $location .= '/';
        $dir = opendir($location);
        while ($file = readdir($dir)) {
            if ($file == '.' || $file == '..') continue;
            $do = (filetype($location . $file) == 'dir') ? 'addDir' : 'addFile';
            $this->$do($location . $file, $name . $file);
        }
    }
    function generateZip()
    {
        if (!extension_loaded('zip')) {
            throw new Exception("The ZIP extension is not loaded");
        }
        if (!file_exists($this->pathSource)) {
            throw new Exception("Directory does not exist " . $this->pathSource);
        }
        $zipName =  $this->pathDestination . '/' . "BK-" . date("Y-m-d") . ".zip";
        $res = $this->open($zipName, ZipArchive::CREATE);
        if ($res === TRUE) {
            $this->addDir($this->pathSource, basename($this->pathSource));
            $this->close();
        } else {
            throw new Exception("Unable to create ZIP file ".$zipName);
        }
        return $zipName;
    }
}
