<?php
//    Pastèque Web back office
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

/** Convert an image to a thumbnail. If target width and height
 * are not specified they are read from config file.
 * @param $fileName Path to file to resize
 * @param $outputFileName Path to output file that will hold the thumbnail
 * @param $width Target width, if not specified this is read from config
 * @param $height Target height, if not specified this is read form config
 */
function img_thumbnail($fileName, $outputFileName,
        $width = null, $height = null) {
    global $config;
    if ($width === null) {
        $width = $config['thumb_width'];
        $height = $config['thumb_height'];
    }
    $imgData = getimagesize($fileName);
    $imgWidth = $imgData[0];
    $imgHeight = $imgData[1];
    $imgType = $imgData[2]; // A gd constant
    $widthRatio = $width / $imgWidth;
    $heightRatio = $height / $imgHeight;
    // Use the smallest ratio to resize without cropping
    $ratio = min($widthRatio, $heightRatio);
    $destWidth = round($imgWidth * $ratio);
    $destHeight = round($imgHeight * $ratio);
    // Read input
    switch ($imgType) {
    case IMG_JPG:
    case IMG_JPEG:
        $src = imagecreatefromjpeg($fileName);
        break;
    case IMG_PNG:
        $src = imagecreatefrompng($fileName);
        break;
    case IMG_GIF:
        $src = imagecreatefromgif($fileName);
        break;
    case IMG_WBMP:
        $src = imagecreatefromwbmp($fileName);
        break;
    case IMG_XPM:
        $src = imagecreatefromxpm($fileName);
        break;
    }
    // Create thumbnail
    $dst = imagecreatetruecolor($destWidth, $destHeight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $destWidth, $destHeight,
            $imgWidth, $imgHeight);
    // Write output
    switch ($imgType) {
    case IMG_PNG:
        return imagepng($dst, $outputFileName);
    case IMG_JPG:
    case IMG_JPEG:
    default:
        return imagejpeg($dst, $outputFileName);
        break;
    }
}