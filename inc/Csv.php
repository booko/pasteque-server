<?php

namespace Pasteque;

class Csv {

    protected $header;
    /** filter for empty string */
    public $filter;
    /** containt keys must be in header */
    protected $key;
    protected $path;
    protected $sep;
    /** null if file not open */
    protected $fd;
    protected $currentLineNumber;
    protected $optionKey;
    protected $errors;

    public function __construct($path, $key, $optionKey = array()) {
        $this->header = null;
        $this->path = $path;
        $this->key = $key;
        $this->optionKey = $optionKey;
        $this->error = array();
    }

    /** open file and init attributs if file is correctly open 
     * is problem occurred return false */
    public function open() {
        $this->fd = fopen($this->path, "r");
        if (!$this->fd) {
            $this->errors[] = "Cannot open file";
            return false;
        }
        $this->currentLineNumber = 0;
        return $this->init();
    }

    public function isOpen() {
        return $this->fd !== NULL;
    }

    public function close() {
        $this->currentLineNumber = -1;
        fclose($this->fd);
        $this->fd = null;
    }

    /** add a filter to the field for empty string */
    public function addFilter($field, $value) {
        if (in_array($field, $this->header) !== false) {
            $this->filter[$field] = $value;
            return true;
        }
        return false;
    }

    /** set attribut sep return false if file empty
     * or if separator not defined' */
    protected function getSep() {
        //get the first line 
        $this->line = fgets($this->fd, 4048);
        if (!$this->line) {
            $this->errors[] = "File empty";
            return false;
        }
        $this->currentLineNumber++;
        $this->sep = substr($this->line, -2, 1);
        if (!$this->sep || $this->sep === " ") {
            $this->errors[] = \i18n("Separator not defined");
            return false;
        }
        return true;
    }

    public function readLine () {
        //file closed
        if ($this->fd == NULL) {
            $this->errors[] = \i18n("Technical error: file not open");
            return false;
        }
        $this->line = fgetcsv($this->fd, 4048, $this->sep);
        if (!$this->line) {
            return false;
        }
        $this->currentLineNumber++;

        $tab = array_fill_keys($this->header, NULL);
        $cmp = 0;

        foreach ($this->header as $field){
            $tab[$field] = $this->line[$cmp];
            $cmp++;
        }

        // manage content
        $tab = $this->utf8_encode_array($tab);
        $tab = $this->filter($tab);
        return $tab;
    }

    /** read line from csv and init header
     * if faillure return false */
    protected function setHeader() {
        if ($this->fd === NULL) {
            $this->errors[] = i18n("Technical error: file not open");
            return false;
        }
        $this->header = fgetcsv($this->fd, 4048, $this->sep);
        if (!$this->header) {
            $this->errors[] = i18n("Header default");
            return false;
        }

        $this->currentLineNumber++;
        $b_error = true;
        // test if all values of key are in header
        for ($cmp = 0; $cmp < count($this->key); $cmp++) {
            if (!in_array($this->key[$cmp], $this->header)) {
                $b_error = false;
                $this->errors[] = i18n("Header doesn't contain : %s", NULL, $this->key[$cmp]);
            }
        }
        return $b_error;
    }

    // initialize attributs
    protected function init() {
        //return value
        $sepOk = $this->getSep();

        if($sepOk) {
            $headerOk = $this->setHeader();
        }
        if (!$sepOk || !$headerOk) {
            $this->close();
        }

        return $sepOk && $headerOk;
    }

    public function getHeader() {
        return $this->header;
    }

    public function getKey() {
        return $this->key;
    }

    public function getOptionalKey() {
        return $this->optionKey;
    }

    public function getCurrentLineNumber() {
        return $this->currentLineNumber;
    }

    protected function utf8_encode_array($array){
        foreach ($array as $field => $value) {
            if (!mb_check_encoding($value, "UTF-8")) {
                $array[$field] = mb_convert_encoding($value, "UTF-8");
            }
        }
        return $array;
    }

    /** modifie value of array by the value of filter associated
     * if the value is an empty string
     * if field is not set on filter: do nothing */
    public function filter($tab) {
        foreach ($tab as $key => $value ) {
            if ($tab[$key] === "" && isset($this->filter[$key])) {
                $tab[$key] = $this->filter[$key];
            }
        }
        return $tab;
    }

    /** return array error */
    public function getErrors() {
        return $this->errors;
    }
}
?>

