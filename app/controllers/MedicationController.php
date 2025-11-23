<?php
require_once ROOT_DIR . PUBLIC_DIR . '/vendor/setasign/fpdf/fpdf.php';

class MedicationsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('PatientModel');
    }

    public function export_pdf()
    {
        $role = $this->session->userdata('role') ?? 'user';

        if ($role !== 'admin') {
            show_error('Unauthorized access.');
            return;
        }

        $result = $this->PatientModel->get_paginated('', null, null, ['has_medicine' => true]);
        $medications = $result['records'] ?? [];

        $pdf = new FPDF('L','mm','A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,10,'Medication Receipt',0,1,'C');
        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',10);
        $pdf->SetTextColor(192,57,43);
        $pdf->Cell(10,8,'ID',1);
        $pdf->Cell(40,8,'Patient',1);
        $pdf->Cell(30,8,'Medicine',1);
        $pdf->Cell(30,8,'Disease',1);
        $pdf->Cell(25,8,'Start Date',1);
        $pdf->Cell(25,8,'End Date',1);
        $pdf->Cell(25,8,'Duration',1);
        $pdf->Cell(20,8,'Status',1);
        $pdf->Ln();

        $pdf->SetFont('Arial','',10);
        $pdf->SetTextColor(0,0,0);

        foreach ($medications as $m) {
            $patientName = trim(($m['first_name'] ?? '').' '.($m['last_name'] ?? ''));
            $start = $m['start_date'] ?? '-';
            $end   = $m['end_date'] ?? '-';
            $duration = $m['duration'] ?? '-';
            $status = $m['status'] ?? 'Unknown';

            $pdf->Cell(10,8,($m['id'] ?? '-'),1);
            $pdf->Cell(40,8,$patientName,1);
            $pdf->Cell(30,8,($m['medicine'] ?? '-'),1);
            $pdf->Cell(30,8,($m['disease'] ?? '-'),1);
            $pdf->Cell(25,8,$start,1);
            $pdf->Cell(25,8,$end,1);
            $pdf->Cell(25,8,$duration,1);
            $pdf->Cell(20,8,$status,1);
            $pdf->Ln();
        }

        $pdf->Output('D','Medication_Receipt_'.date('Y-m-d').'.pdf');
        exit;
    }
}
