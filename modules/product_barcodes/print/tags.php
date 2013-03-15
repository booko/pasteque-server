<?php
//    Pastèque Web back office, Product barcodes module
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

namespace ProductBarcodes;

const V_MARGIN = 10;
const H_MARGIN = 15;
const COL_SIZE = 50;
const ROW_SIZE = 30;
const COL_NUM = 3;
const ROW_NUM = 7;
const V_PADDING = 3;
const H_PADDING = 5;
const BARCODE_WIDTH = 50;
const BARCODE_HEIGHT = 15;

require_once(\Pasteque\ABSPATH . "/lib/barcode-master/php-barcode.php");
$font = "./lib/barcode-master/NOTTB___.TTF";

$pdf = new \FPDF("P", "mm", "A4");
$pdf->setMargins(V_MARGIN, H_MARGIN, V_MARGIN);
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);

function pdf_barcode($pdf, $product_id, $col, $row) {
    $product = \Pasteque\ProductsService::get($product_id);
    $x = V_MARGIN + $col * COL_SIZE + $col * V_PADDING;
    $y = H_MARGIN + $row * ROW_SIZE + $row * H_PADDING;
    $pdf->SetXY($x, $y);
    $pdf->cell(COL_SIZE, 5, $product->label, 0, 1, "C");
    $pdf->SetXY($x, $y + 5);
    $data = \Barcode::fpdf($pdf, "000000",
            $pdf->GetX() + BARCODE_WIDTH / 2, $pdf->GetY() + BARCODE_HEIGHT / 2,
            0, "ean13", array('code'=>$product->barcode),
            BARCODE_WIDTH / (15 * 7), BARCODE_HEIGHT);
    $pdf->SetXY($x, $y + BARCODE_HEIGHT + 5);
    $pdf->Cell(COL_SIZE, 5, $product->barcode, 0, 1, "C");
    
}

$col = 0;
$row = 0;
$skip = $_POST['start_from'] - 1;
$col += $skip;
$row = intVal(floor($col / COL_NUM));
$col %= COL_NUM;

foreach ($_POST as $key => $value) {
    if (substr($key, 0, 4) == "qty-") {
        $product_id = substr($key, 4);
        $qty = $value;
        for ($i = 0; $i < $qty; $i++) {
            pdf_barcode($pdf, $product_id, $col, $row);
            $col++;
            if ($col == COL_NUM) {
                $row++;
                if ($row == ROW_NUM) {
                    $pdf->addPage();
                    $row = 0;
                }
                $col = 0;
            }
        }
    }
}

$pdf->Output();

?>