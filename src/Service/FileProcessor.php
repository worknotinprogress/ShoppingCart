<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Class FileProcessor
 * @package App\Service
 */
class FileProcessor
{
    /**
     * @var Filesystem
     */
    private $_fileSystem;

    /**
     * @var array
     */
    private $_errors = [];

    /**
     * @var array
     */
    protected $fileInformation = [];

    /**
     * FileProcessor constructor.
     * @param Filesystem $fileSystem
     */
    public function __construct(Filesystem $fileSystem)
    {
        $this->_fileSystem = $fileSystem;
    }

    /**
     * @param String $filePath
     */
    public function processFile(String $filePath)
    {
        if ($this->_validateFile($filePath)){
            $this->fileInformation =  $this->_readFile($filePath);
        }
    }

    /**
     * @return array
     */
    public function getFileInformation()
    {
        return $this->fileInformation;
    }

    /**
     * Checks if errors exists
     *
     * @return bool
     */
    public function hasErrors()
    {
        if (!empty($this->_errors)){
            return true;
        }

        return false;
    }

    /**
     * Returns array of errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @param String $filePath
     * @return bool
     */
    private function _validateFile(String $filePath)
    {
        if(!$this->_fileSystem->exists($filePath)){
            $this->_errors[] = 'File doesn\'t exist';
            return false;
        }

        if(is_dir($filePath)){
            $this->_errors[] = 'Specified path is a directory';
            return false;
        }

        return true;
    }

    /**
     * @param $filePath
     * @return array
     */
    private function _readFile($filePath)
    {
        $data = [];
        $file = fopen($filePath,"r");
        while(! feof($file)) {
            $tempData = fgetcsv($file, null, ';');
            if ($tempData) {
                $data[] = $tempData;
            }
        }
        fclose($file);

        return $data;
    }

}