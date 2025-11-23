<?php
require_once ROOT_DIR . PUBLIC_DIR . '/vendor/setasign/fpdf/fpdf.php';

class RecordsController extends Controller
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

        $result = $this->PatientModel->get_paginated('', null, null);
        $records = $result['records'] ?? [];

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,10,'Patient Records',0,1,'C');
        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',10);
        $pdf->SetTextColor(192,57,43);
        $pdf->Cell(10,8,'ID',1);
        $pdf->Cell(40,8,'Patient',1);
        $pdf->Cell(15,8,'Age',1);
        $pdf->Cell(50,8,'Email',1);
        $pdf->Cell(30,8,'Disease',1);
        $pdf->Cell(20,8,'Type',1);
        $pdf->Cell(35,8,'Medicine',1);
        $pdf->Cell(30,8,'Schedule',1);
        $pdf->Cell(15,8,'Duration',1);
        $pdf->Cell(20,8,'Status',1);
        $pdf->Ln();

        $pdf->SetFont('Arial','',10);
        $pdf->SetTextColor(0,0,0);
        foreach ($records as $r) {
            $pdf->Cell(10,8,($r['id'] ?? '-'),1);
            $pdf->Cell(40,8,trim((($r['first_name'] ?? '').' '.($r['last_name'] ?? ''))),1);
            $pdf->Cell(15,8,($r['age'] ?? '-'),1);
            $pdf->Cell(50,8,($r['email'] ?? '-'),1);
            $pdf->Cell(30,8,($r['disease'] ?? '-'),1);
            $pdf->Cell(20,8,($r['type'] ?? '-'),1);
            $pdf->Cell(35,8,($r['medicine'] ?? '-'),1);
            $pdf->Cell(30,8,($r['schedule'] ?? '-'),1);
            $pdf->Cell(15,8,($r['duration'] ?? '-'),1);
            $pdf->Cell(20,8,($r['status'] ?? '-'),1);
            $pdf->Ln();
        }

        $pdf->Output('D','Patient_Records_'.date('Y-m-d').'.pdf');
        exit;
    }

    public function export_patient_pdf($id)
    {
        $role = $this->session->userdata('role') ?? 'user';
        if ($role !== 'admin') {
            show_error('Unauthorized access.');
            return;
        }

        $patientId = (int) $id;
        $patient   = $this->PatientModel->find_patient($patientId);
        if (!$patient) {
            show_error('Patient not found.');
            return;
        }

        $userId  = (int) ($patient['user_id'] ?? 0);
        $history  = $this->PatientModel->get_past_by_user($userId);
        $upcoming = $this->PatientModel->get_upcoming_by_user($userId);

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();

        $fullName = trim(($patient['first_name'] ?? '').' '.($patient['last_name'] ?? ''));

        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,10,'Patient Full Record',0,1,'C');
        $pdf->Ln(3);

        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,6,'Name: '.$fullName,0,1);
        $pdf->Cell(0,6,'Age: '.($patient['age'] ?? '-'),0,1);
        $pdf->Cell(0,6,'Email: '.($patient['email'] ?? '-'),0,1);
        $pdf->Cell(0,6,'Address: '.($patient['address'] ?? '-'),0,1);
        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,8,'Upcoming Appointments / Medications',0,1);

        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(35,7,'Schedule',1);
        $pdf->Cell(25,7,'Type',1);
        $pdf->Cell(35,7,'Disease',1);
        $pdf->Cell(40,7,'Medicine',1);
        $pdf->Cell(25,7,'Duration',1);
        $pdf->Cell(25,7,'Status',1);
        $pdf->Ln();

        $pdf->SetFont('Arial','',10);
        if (!empty($upcoming)) {
            foreach ($upcoming as $r) {
                $pdf->Cell(35,7,($r['schedule'] ?? '-'),1);
                $pdf->Cell(25,7,($r['type'] ?? '-'),1);
                $pdf->Cell(35,7,($r['disease'] ?? '-'),1);
                $pdf->Cell(40,7,($r['medicine'] ?? '-'),1);
                $pdf->Cell(25,7,($r['duration'] ?? '-'),1);
                $pdf->Cell(25,7,($r['status'] ?? '-'),1);
                $pdf->Ln();
            }
        } else {
            $pdf->Cell(185,7,'No upcoming appointments or medications.',1,1);
        }

        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,8,'History',0,1);

        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(35,7,'Schedule',1);
        $pdf->Cell(25,7,'Type',1);
        $pdf->Cell(35,7,'Disease',1);
        $pdf->Cell(40,7,'Medicine',1);
        $pdf->Cell(25,7,'Duration',1);
        $pdf->Cell(25,7,'Status',1);
        $pdf->Ln();

        $pdf->SetFont('Arial','',10);
        if (!empty($history)) {
            foreach ($history as $r) {
                $pdf->Cell(35,7,($r['schedule'] ?? '-'),1);
                $pdf->Cell(25,7,($r['type'] ?? '-'),1);
                $pdf->Cell(35,7,($r['disease'] ?? '-'),1);
                $pdf->Cell(40,7,($r['medicine'] ?? '-'),1);
                $pdf->Cell(25,7,($r['duration'] ?? '-'),1);
                $pdf->Cell(25,7,($r['status'] ?? '-'),1);
                $pdf->Ln();
            }
        } else {
            $pdf->Cell(185,7,'No past appointments or medications.',1,1);
        }

        $fileName = 'Patient_'.$fullName.'_Record_'.date('Y-m-d').'.pdf';
        $pdf->Output('D', $fileName);
        exit;
    }
}
