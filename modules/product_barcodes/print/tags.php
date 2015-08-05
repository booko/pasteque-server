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

if(isset($_POST["format"]))
    $formatFile = "modules/product_barcodes/print/templates/".$_POST["format"].".php";

if(isset($formatFile) && file_exists($formatFile)) {
    require_once($formatFile);
}
else {
    // default to agipa 119601, first format implemented
    Define("PAPER_SIZE","A4");
    Define("PAPER_ORIENTATION","P");
    Define("V_MARGIN",6);
    Define("H_MARGIN",13);
    Define("COL_SIZE",22);
    Define("ROW_SIZE",16);
    Define("COL_NUM",9);
    Define("ROW_NUM",17);
    Define("V_PADDING",0);
    Define("H_PADDING",0);
    Define("BARCODE_WIDTH",20);
    Define("BARCODE_HEIGHT",10);
    Define("TEXT_HEIGHT",3);
    Define("TEXT_SIZE",8);
}

require_once(\Pasteque\PT::$ABSPATH . "/lib/barcode-master/php-barcode.php");
$font = "./lib/barcode-master/NOTTB___.TTF";

$pdf = new \FPDF(PAPER_ORIENTATION, "mm", PAPER_SIZE);
$pdf->setMargins(H_MARGIN, V_MARGIN);
$pdf->setAutoPageBreak(false,V_MARGIN);
$pdf->AddPage();
$pdf->SetFont('Arial','B',TEXT_SIZE);

function pdf_barcode($pdf, $productId, $col, $row) {
    $product = \Pasteque\ProductsService::get($productId);
    $x = H_MARGIN + $col * COL_SIZE + $col * H_PADDING;
    $y = V_MARGIN + $row * ROW_SIZE + $row * V_PADDING;
    $pdf->SetXY($x, $y);
    $pdf->Cell(BARCODE_WIDTH, TEXT_HEIGHT, utf8_decode($product->reference), 0, 1, "C");
    $pdf->SetXY($x, $y + TEXT_HEIGHT);
    $data = \Barcode::fpdf($pdf, "000000",
            $pdf->GetX() + BARCODE_WIDTH / 2, $pdf->GetY() + BARCODE_HEIGHT / 2,
            0, "ean13", array('code' => $product->barcode),
            BARCODE_WIDTH / (15 * 7), BARCODE_HEIGHT);
    $pdf->SetXY($x, $y + BARCODE_HEIGHT + TEXT_HEIGHT);
    $pdf->Cell(BARCODE_WIDTH, TEXT_HEIGHT, $product->barcode, 0, 1, "C");
}

$col = 0;
$row = 0;
$skip = $_POST['start_from'] - 1;
$col += $skip;
$row = intVal(floor($col / COL_NUM));
$col %= COL_NUM;
foreach ($_POST as $key => $value) {
    if (substr($key, 0, 4) == "qty-") {
        $productId = substr($key, 4);
        $qty = $value;
        for ($i = 1; $i <= $qty; $i++) {
            pdf_barcode($pdf, $productId, $col, $row);
            $col++;
            if ($col == COL_NUM) {
                $row++;
                if ($row == ROW_NUM && $i < $qty) {
                    $pdf->addPage();
                    $row = 0;
                }
                $col = 0;
            }
        }
    }
}

$pdf->Output();
