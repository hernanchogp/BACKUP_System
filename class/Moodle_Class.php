<?php

class Moodle_Class
{
    protected array $pathcopy;
    protected string $pathbk;

    public function __construct(array $pathcopy,  string $pathbk)
    {
        $this->pathcopy = $pathcopy;
        $this->pathbk = $pathbk;
    }
    public function copydirMoodle()
    {
        if (!is_array($this->pathcopy)) {
            throw new Exception("It's not a object " . $this->pathcopy);
        } else {
            foreach ($this->pathcopy as $path) {
                if (file_exists($path)) {
                    if (is_dir($path)) {
                        $pathdes = str_replace('\\', '/', trim($path));
                        $this->copydir($path, $this->pathbk . '/' . basename($pathdes));
                    } else {
                        throw new Exception("Not a directory =>" . $path);
                    }
                } else {
                    throw new Exception("The directory does not exist =>" . $path);
                }
            }
        }
    }

    private function copydir($source, $target)
    {
        if (is_dir($source)) {
            @mkdir($target);
            $d = dir($source);
            while (FALSE !== ($entry = $d->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $Entry = $source . '/' . $entry;
                if (is_dir($Entry)) {
                    $this->copydir($Entry, $target . '/' . $entry);
                    continue;
                }
                copy($Entry, $target . '/' . $entry);
            }

            $d->close();
        } else {
            copy($source, $target);
        }
    }
}
