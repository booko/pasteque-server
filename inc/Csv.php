<?php
//
//    Copyright (C) 2015 Scil (http://scil.coop)
//    Cédric Houbart, Philippe Pary
//    Class to read CSV files
//
//    This file is part of Pastèque.
//
//    Pastèque is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    Pastèque is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with Pastèque.  If not, see <http://www.gnu.org/licenses/>.

namespace Pasteque;

class Csv {

    /** Array of keys. Index is column number. */
    protected $columnMapping;
    /** filter for empty string */
    public $emptyStringFilters;
    /** containt keys must be in header */
    protected $keys;
    protected $path;
    protected $sep;
    /** null if file not open */
    protected $fd;
    protected $currentLineNumber;
    protected $optionKeys;
    protected $errors;
    /** i18n domain to check for more translations */
    protected $extraDomain;
    protected $sourceEncoding;

    public function __construct($path, $keys, $optionKeys = array(),
            $extraDomain = null) {
        $this->columnMapping = array();
        $this->path = $path;
        $this->keys = $keys;
        if ($optionKeys == null) {
            $this->optionKeys = array();
        } else {
            $this->optionKeys = $optionKeys;
        }
        $this->error = array();
        $this->emptyStringFilters = array();
        $this->extraDomain = $extraDomain;
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
        return $this->fd !== null;
    }
    public function close() {
        $this->currentLineNumber = -1;
        fclose($this->fd);
        $this->fd = null;
    }

    /** Initialize csv reading */
    protected function init() {
        // Read magic first line to detect encoding and separator
        // First line contains only one cell with the word "Pastèque" in it
        // Other cells are empty
        $this->line = fgets($this->fd, 4048);
        if (!$this->line) {
            $this->errors[] = "File empty";
            $this->close();
            return false;
        }
        while (substr($this->line, -1, 1) == "\r"
                || substr($this->line, -1, 1) == "\n") {
            $this->line = substr($this->line, 0, -1);
        }
        $this->currentLineNumber++;
        // Get separator
        $this->sep = substr($this->line, -1, 1);
        if (!$this->sep || $this->sep === " " ) {
            $this->errors[] = \i18n("Separator not defined");
            $this->close();
            return false;
        }
        $finfo = new \finfo(FILEINFO_MIME_ENCODING);
        $info = $finfo->file($this->path);
        $this->sourceEncoding = strtoupper($info);
        // try to convert first line from guessed by finfo() sourceEncoding to UTF-8. Read CSV values. Check if firt value, "Pastèque", can get read with this charset
        $check = mb_convert_encoding($this->line, "UTF-8",$this->sourceEncoding);
        $line = str_getcsv($check,$this->sep);
        $check = $line[0];
        if ($check != "Pastèque") {
            // workaround for not working and poorly implemented in mbstring macintosh charset only available with iconv
            if(substr(iconv("macintosh","UTF-8",$this->line),0,9) == "Pastèque") {
                $this->sourceEncoding = "macintosh";
            }
            else {
                $this->errors[] = \i18n("Unidentified character set");
                $this->close();
                return false;
            }
        }
        // Get headers (second line)
        $headers = fgetcsv($this->fd, 4048, $this->sep);
        $headers = $this->utf8_encode_array($headers);
        if (!$headers) {
            $this->errors[] = i18n("Header default");
            $this->close();
            return false;
        }
        $this->currentLineNumber++;
        $fatalError = false;
        // Parse all headers and check mapping
        for ($i = 0; $i < count($headers); $i++) {
            $title = $headers[$i];
            $found = false;
            // Search in mandatory keys
            foreach ($this->keys as $key) {
                if ($title == $key || $title == i18n($key)
                        || $title == i18n($key, $this->extraDomain)) {
                    $this->columnMapping[$i] = $key;
                    $found = true;
                    break;
                }
            }
            // Search in optionnal keys
            if (!$found) {
                foreach ($this->optionKeys as $key) {
                    if ($title == $key || $title == i18n($key)) {
                        $this->columnMapping[$i] = $key;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    // Unidentified column
                    $this->errors[] = i18n("Unknown header %s", null, $title);
                }
            }
        }
        // Check if all mandatory keys are mapped
        foreach ($this->keys as $key) {
            $found = false;
            foreach ($this->columnMapping as $mapping) {
                if ($key == $mapping) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $i18nKey = $key;
                if (i18n($key) != $key) {
                    $i18nKey = i18n($key);
                }
                if ($this->extraDomain !== null
                        && i18n($key, $this->extraDomain) != $key) {
                    $i18nKey = i18n($key, $this->extraDomain);
                }
                $this->errors[] = i18n("Header doesn't contain : %s",
                        null, $i18nKey);
                $fatalError = true;
            }
        }
        if ($fatalError) {
            $this->close();
            return false;
        }
        return true;
    }

    /** Set the value to use if it is an empty string for the field.*
     * Default is set to null.
     */
    public function setEmptyStringValue($field, $value) {
        $this->emptyStringFilters[$field] = $value;
    }

    public function readLine () {
        if ($this->fd == NULL) {
            $this->errors[] = \i18n("Technical error: file not open");
            return false;
        }
        $this->line = fgetcsv($this->fd, 4048, $this->sep);
        if (!$this->line) {
            return false;
        }
        $this->currentLineNumber++;
        // Map line data
        $ret = array();
        foreach ($this->columnMapping as $i => $key){
            $ret[$key] = $this->line[$i];
        }
        // Apply filters
        $ret = $this->utf8_encode_array($ret);
        $ret = $this->filterEmptyStrings($ret);
        return $ret;
    }

    public function getKeys() {
        return $this->keys;
    }

    public function getOptionalKeys() {
        return $this->optionKeys;
    }

    public function getCurrentLineNumber() {
        return $this->currentLineNumber;
    }

    protected function utf8_encode_array($array){
        if ($this->sourceEncoding == "UTF-8") {
            return $array;
        }
        foreach ($array as $field => $value) {
            if ($this->sourceEncoding !== null && $this->sourceEncoding !== "macintosh") {
                $array[$field] = mb_convert_encoding($value, "UTF-8",
                        $this->sourceEncoding);
            } elseif($this->sourceEncoding === "macintosh") {
                $array[$field] = iconv($this->sourceEncoding,"UTF-8",$value);
            }
            else {
                $array[$field] = mb_convert_encoding($value, "UTF-8");
            }
        }
        return $array;
    }

    /** Apply filters on an array. If no filter is defined and the value is
     * an empty string, it is converted to null.
     */
    protected function filterEmptyStrings($tab) {
        foreach ($tab as $key => $value ) {
            if ($tab[$key] === "") {
                if (!isset($this->emptyStringFilters[$key])) {
                    $tab[$key] = null;
                } else {
                    $tab[$key] = $this->emptyStringFilters[$key];
                }
            }
        }
        return $tab;
    }

    /** return array error */
    public function getErrors() {
        return $this->errors;
    }
}
