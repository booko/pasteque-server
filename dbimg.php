<?php
//    Pastèque Web back office, Products module
//
//    Copyright (C) 2013 Scil (http://scil.coop)
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

function generate_barcode($type, $data) {
    require_once(PT::$ABSPATH . "/lib/barcode-master/php-barcode.php");
    $font = "./lib/barcode-master/NOTTB___.TTF";
    $fontSize = 10;   // GD1 in px ; GD2 in point
    $marge    = 2;   // between barcode and hri in pixel
    $y        = 25;  // barcode center
    $height   = 50;   // barcode height in 1D ; module size in 2D
    $width    = 2;    // barcode height in 1D ; not use in 2D
    $angle    = 0;   // rotation in degrees : nb : non horizontable barcode might not be usable because of pixelisation
  
    $code     = $data;
    $type     = $type;

    // Precompute digits to set image width
    switch($type){
        case 'std25':
        case 'int25':
            $digit = \BarcodeI25::getDigit($code, $crc, $type);
            break;
        case 'ean8':
        case 'ean13':
            $digit = \BarcodeEAN::getDigit($code, $type);
            break;
        case 'upc':
            $digit = \BarcodeUPC::getDigit($code);
            break;
        case 'code11':
            $digit = \Barcode11::getDigit($code);
            break;
        case 'code39':
            $digit = \Barcode39::getDigit($code);
            break;
        case 'code93':
            $digit = \Barcode93::getDigit($code, $crc);
            break;
        case 'code128':
            $digit = \Barcode128::getDigit($code);
            break;
        case 'codabar':
            $digit = \BarcodeCodabar::getDigit($code);
            break;
        case 'msi':
            $digit = \BarcodeMSI::getDigit($code, $crc);
            break;
        case 'datamatrix':
            $digit = \BarcodeDatamatrix::getDigit($code, $rect);
            break;
    }
    $imgWidth = strlen($digit) * $width;
    $x = $imgWidth / 2;

    $im     = imagecreatetruecolor($imgWidth, 62);
    $black  = ImageColorAllocate($im,0x00,0x00,0x00);
    $white  = ImageColorAllocate($im,0xff,0xff,0xff);
    $red    = ImageColorAllocate($im,0xff,0x00,0x00);
    $blue   = ImageColorAllocate($im,0x00,0x00,0xff);
    imagefilledrectangle($im, 0, 0, $imgWidth, 62, $white);
    $data = \Barcode::gd($im, $black, $x, $y, $angle, $type,
            array('code'=>$code), $width, $height);
    if (isset($font)) {
        $box = imagettfbbox($fontSize, 0, $font, $data['hri']);
        $len = $box[2] - $box[0];
        \Barcode::rotate(-$len / 2, ($data['height'] / 2) + $fontSize + $marge, $angle, $xt, $yt);
        imagettftext($im, $fontSize, $angle, $x + $xt, $y + $yt, $black, $font, $data['hri']);
    }

    header('Content-type: image/gif');
    imagegif($im);
    imagedestroy($im);
}

switch($_GET['w']) {
case 'product':
    if (isset($_GET['id'])) {
        $prd = ProductsService::get($_GET['id']);
        if ($prd !== null && $prd->hasImage !== false) {
            echo ProductsService::getImage($prd->id);
            break;
        }
    }
    echo file_get_contents(PT::$ABSPATH . "/templates/" . $config['template'] . "/img/default_product.png");
    break;
case 'provider':
    if (isset($_GET['id'])) {
        $prov = ProvidersService::get($_GET['id']);
        if ($prov !== null && $prov->hasImage !== false) {
            $img = ProvidersService::getImage($prov->id);
            break;
        }
    }
    echo file_get_contents(PT::$ABSPATH . "/templates/" . $config['template'] . "/img/default_provider.png");
    break;
case 'category':
    if (isset($_GET['id'])) {
        $cat = CategoriesService::get($_GET['id']);
        if ($cat->hasImage !== false) {
            echo CategoriesService::getImage($cat->id);
            break;
        }
    }
    echo file_get_contents(PT::$ABSPATH . "/templates/" . $config['template'] . "/img/default_category.png");
    break;
case 'resource':
    $res = ResourcesService::get($_GET['id']);
    if ($res->type == Resource::TYPE_IMAGE) {
        echo $res->content;
    }
    break;
case 'barcode':
    generate_barcode("ean13", $_GET['code']);
    break;
case 'custcard':
    generate_barcode("code128", $_GET['code']);
    break;
case 'js':
    $file = $_GET['id'];
    $file = str_replace("..", ".", $file);
    require_once(PT::$ABSPATH  . "/templates/" . $config['template'] . "/" . $file);
    break;
}
?>
