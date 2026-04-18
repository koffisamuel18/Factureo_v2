<?php
require_once '../vendor/fpdf/fpdf.php';
require_once '../includes/functions.php';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(16,185,129); // vert
$facture_id = 2; // exemple
$numero_formate = get_numero_facture_formate($facture_id);
$pdf->Cell(0, 18, utf8_decode('Facture ' . $numero_formate), 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0, 10, 'Bravo, FPDF fonctionne !', 0, 1, 'C');
$pdf->Output('I', 'test_factureo.pdf');
exit; 