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

define("PT_THUMBNAIL_DEFAULT_WIDTH", 128);
define("PT_THUMBNAIL_DEFAULT_HEIGHT", 128);

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
        if (isset($config['thumb_width']) && isset($config['thumb_height'])) {
            $width = $config['thumb_width'];
            $height = $config['thumb_height'];
        } else {
            $width = PT_THUMBNAIL_DEFAULT_WIDTH;
            $height = PT_THUMBNAIL_DEFAULT_HEIGHT;
        }
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
    case IMAGETYPE_JPEG:
        $src = imagecreatefromjpeg($fileName);
        break;
    case IMG_PNG:
    case IMAGETYPE_PNG:
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
    if ($imgType == IMG_PNG || $imgType == IMAGETYPE_PNG) {
        // Handle png transparency
        imagealphablending($dst, true);
        imagesavealpha($dst, true);
        $color = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefill($dst, 0, 0, $color);
    }
    // Copy image
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $destWidth, $destHeight,
            $imgWidth, $imgHeight);
    // Handle jpg rotation
    if ($imgType == IMG_JPG || $imgType == IMAGETYPE_JPG) {
        $exif = exif_read_data($fileName);
        if ($exif !== false && isset($exif['Orientation'])) {
            $orientation = $exif['Orientation'];
            switch($orientation) {
            case 3:
                $dst = imagerotate($dst, 180, 0);
                break;
            case 6:
                $dst = imagerotate($dst, -90, 0);
                break;
            case 8:
                $dst = imagerotate($dst, 90, 0);
                break;
            }
        }
    }
    // Write output
    switch ($imgType) {
    case IMG_PNG:
    case IMAGETYPE_PNG:
        return imagepng($dst, $outputFileName);
    case IMG_JPG:
    case IMG_JPEG:
    default:
        return imagejpeg($dst, $outputFileName);
        break;
    }
}