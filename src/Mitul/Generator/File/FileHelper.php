<?php

namespace Mitul\Generator\File;

class FileHelper
{
    public function writeFile($file, $contents)
    {
        $file = $this->platformSlashes($file);
        file_put_contents($file, $contents);
    }

    public function getFileContents($file)
    {
        $file = $this->platformSlashes($file);
        return file_get_contents($file);
    }

    function platformSlashes($path) {
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $path = str_replace('/', '\\', $path);
        }
        return $path;
    }

}
