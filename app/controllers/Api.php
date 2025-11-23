<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Api extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('PatientModel');
    }

    /**
     * Check if a time slot is available
     * GET /api/check-appointment-availability?date=YYYY-MM-DD&time=HH:MM&exclude_id={id}
     */
    public function check_appointment_availability()
    {
        if ($this->io->method(true) !== 'GET') {
            return json_response(['error' => 'Method not allowed'], 405);
        }

        $date = trim($this->io->get('date') ?? '');
        $time = trim($this->io->get('time') ?? '');
        $exclude_id = (int) ($this->io->get('exclude_id') ?? 0);

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return json_response(['error' => 'Invalid date format'], 400);
        }

        // Validate time format
        if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
            return json_response(['error' => 'Invalid time format'], 400);
        }

        // Check if date is today or in the future
        $today = date('Y-m-d');
        if ($date < $today) {
            return json_response([
                'available' => false,
                'date' => $date,
                'time' => $time,
                'message' => 'Cannot book appointments for past dates. Please select today or a future date.'
            ]);
        }

        // Check if time is within working hours (8 AM to 5 PM)
        $hour = (int) substr($time, 0, 2);
        if ($hour < 8 || $hour >= 17) {
            return json_response([
                'available' => false,
                'date' => $date,
                'time' => $time,
                'message' => 'Working hours are 8:00 AM to 5:00 PM. Please select a time within this range.'
            ]);
        }

        $exclude_id = $exclude_id > 0 ? $exclude_id : null;

        // Check for daily limit (5 max per day)
        if ($this->PatientModel->exceeds_daily_limit($date, 5, $exclude_id)) {
            return json_response([
                'available' => false,
                'date' => $date,
                'time' => $time,
                'message' => 'Maximum of 5 appointments per day reached.'
            ]);
        }

        // Check for time conflict
        $has_conflict = $this->PatientModel->has_time_conflict($date, $time, $exclude_id);

        return json_response([
            'available' => !$has_conflict,
            'date' => $date,
            'time' => $time,
            'message' => $has_conflict ? 'Time slot is not available. There must be at least 30 minutes between appointments.' : 'Time slot is available.'
        ]);
    }

    /**
     * Get available time slots for a date
     * GET /api/available-slots?date=YYYY-MM-DD
     */
    public function available_slots()
    {
        if ($this->io->method(true) !== 'GET') {
            return json_response(['error' => 'Method not allowed'], 405);
        }

        $date = trim($this->io->get('date') ?? '');

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return json_response(['error' => 'Invalid date format'], 400);
        }

        $slots = $this->PatientModel->get_available_slots($date);

        return json_response([
            'date' => $date,
            'available_slots' => $slots,
            'total_available' => count($slots)
        ]);
    }
}

/**
 * Helper function to return JSON response
 */
if (!function_exists('json_response')) {
    function json_response($data, $status_code = 200)
    {
        http_response_code($status_code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
?>
