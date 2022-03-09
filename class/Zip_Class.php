<?php

class Zip_Class  extends ZipArchive
{
    protected $pathSource;
    protected  $pathDestination;

    public function __construct($pathSource, $pathDestination)
    {
        $this->pathSource = $pathSource;
        $this->pathDestination = $pathDestination;
    }

    public function addDir($location, $name)
    {
        if (is_dir($location)) {
            $this->addEmptyDir($name);
            $this->addDirFull($location, $name);
        } else {           
            $this->addFile($location, $name);
        }
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

    private function validatePathSource()
    {
        foreach ($this->pathSource as $path) {
            if (!is_dir($path) && !file_exists($path)) {
                throw new Exception("The directory or file does not exist =>" . $path);
            }
        }
    }

    public function generateZip()
    {
        if (!extension_loaded('zip')) {
            throw new Exception("The ZIP extension is not loaded");
        }
        $this->validatePathSource();
        $zipName =  $this->pathDestination . '/' . "BK-" . date("Y-m-d") . ".zip";
        $res = $this->open($zipName, ZipArchive::CREATE);
        if ($res === TRUE) {
            foreach ($this->pathSource as $path) {

                $this->addDir($path, basename($path));
            }
            $this->close();
        } else {
            throw new Exception("Unable to create ZIP file " . $zipName);
        }
        return $zipName;
    }
}
