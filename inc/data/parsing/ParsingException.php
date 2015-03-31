<?php

//    Pastèque Web back office
//
//    Copyright (C) 2015 Scil (http://scil.coop)
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

namespace Pasteque\Parsing;

class ParsingException extends \Exception 
{
    /**
     * The invalid value
     *
     * @var mixed
     */
    public $invalidValue;
    
    /**
     * a message to show in the UI
     *
     * @var string
     */
    private $i18nMessage;
    
    /**
     * 
     * @param string $message message show to developer
     * @param mixed $invalidValue
     * @param string $i18nMessage a message to show on UI
     */
    public function __construct($message, $invalidValue, $i18nMessage)
    {
        $this->invalidValue = $invalidValue;
        $this->i18nMessage = $i18nMessage;
        parent::__construct($message);
    }
    
    public function getI18nMessage()
    {
        return \i18n($this->i18nMessage, NULL, $this->invalidValue);
    }
}