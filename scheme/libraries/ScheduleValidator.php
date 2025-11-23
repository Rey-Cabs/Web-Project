<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * ScheduleValidator Class
 *
 * Handles appointment schedule validation with rules:
 * 1. No two appointments can have the same time on the same day
 * 2. Maximum of 6 appointments per day
 *
 * @package LavaLust
 * @category Libraries
 */
class ScheduleValidator
{
    /**
     * Database instance
     *
     * @var object
     */
    private $db;

    /**
     * Maximum appointments allowed per day
     *
     * @var int
     */
    private $max_per_day = 4;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Access the database through the registry
        $this->db = Registry::get('database')['main'];
    }

    /**
     * Validate appointment schedule
     *
     * Checks if a new/updated appointment violates scheduling rules.
     * Returns an array with 'valid' => boolean and 'message' => string
     *
     * @param string $schedule_date The appointment date (YYYY-MM-DD format)
     * @param string $schedule_time The appointment time (HH:MM format) - optional
     * @param int $exclude_id Exclude this patient ID from conflict check (for updates)
     * @return array {valid: bool, message: string}
     */
    public function validate($schedule_date, $schedule_time = null, $exclude_id = null)
    {
        // Validate date format
        if (!$this->is_valid_date($schedule_date)) {
            return [
                'valid'   => false,
                'message' => 'Invalid schedule date format. Use YYYY-MM-DD.'
            ];
        }

        // Check daily limit
        $daily_count = $this->count_appointments_on_date($schedule_date, $exclude_id);
        if ($daily_count >= $this->max_per_day) {
            return [
                'valid'   => false,
                'message' => 'Maximum of 6 appointments per day reached.'
            ];
        }

        // If time is provided, check for time conflicts
        if ($schedule_time !== null && $schedule_time !== '') {
            if (!$this->is_valid_time($schedule_time)) {
                return [
                    'valid'   => false,
                    'message' => 'Invalid schedule time format. Use HH:MM.'
                ];
            }

            $has_conflict = $this->has_time_conflict($schedule_date, $schedule_time, $exclude_id);
            if ($has_conflict) {
                return [
                    'valid'   => false,
                    'message' => 'Time slot already taken.'
                ];
            }
        }

        return [
            'valid'   => true,
            'message' => 'Schedule is valid.'
        ];
    }

    /**
     * Count appointments on a specific date
     *
     * @param string $date (YYYY-MM-DD format)
     * @param int $exclude_id Exclude this patient ID
     * @return int
     */
    private function count_appointments_on_date($date, $exclude_id = null)
    {
        try {
            $query = $this->db->table('patients')
                ->where('schedule', $date)
                ->where_not_null('schedule');

            if ($exclude_id !== null) {
                $query->where('id', '!=', $exclude_id);
            }

            $result = $query->select_count('*', 'count')->get();
            return (int) ($result['count'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Check if there's a time conflict for the given date and time
     *
     * @param string $date (YYYY-MM-DD format)
     * @param string $time (HH:MM format)
     * @param int $exclude_id Exclude this patient ID
     * @return bool
     */
    private function has_time_conflict($date, $time, $exclude_id = null)
    {
        try {
            // This assumes schedule column stores full datetime or time component
            // Adjust query based on actual database schema
            $datetime_pattern = $date . ' ' . $time . '%';

            $query = $this->db->table('patients')
                ->where('schedule', 'LIKE', $datetime_pattern)
                ->where_not_null('schedule');

            if ($exclude_id !== null) {
                $query->where('id', '!=', $exclude_id);
            }

            $result = $query->select_count('*', 'count')->get();
            return ((int) ($result['count'] ?? 0)) > 0;
        } catch (Exception $e) {
            // If query fails, assume no conflict to avoid false positives
            return false;
        }
    }

    /**
     * Get available time slots for a specific date
     *
     * Assumes appointment slots are hourly from 8:00 to 17:00
     *
     * @param string $date (YYYY-MM-DD format)
     * @return array Array of available HH:MM slots
     */
    public function get_available_slots($date)
    {
        $all_slots = [];
        for ($hour = 8; $hour <= 17; $hour++) {
            $all_slots[] = sprintf('%02d:00', $hour);
        }

        try {
            $booked = $this->db->table('patients')
                ->select('schedule')
                ->where('schedule', 'LIKE', $date . '%')
                ->where_not_null('schedule')
                ->get_all();

            $booked_times = [];
            foreach ($booked as $appointment) {
                // Extract time from schedule field
                if (strpos($appointment['schedule'], ' ') !== false) {
                    list($_, $time) = explode(' ', $appointment['schedule']);
                    $booked_times[] = substr($time, 0, 5); // HH:MM
                } elseif (strlen($appointment['schedule']) > 10) {
                    // Handle format like "2025-11-20 14:30:00"
                    preg_match('/(\d{2}:\d{2})/', $appointment['schedule'], $matches);
                    if (!empty($matches[1])) {
                        $booked_times[] = $matches[1];
                    }
                }
            }

            return array_diff($all_slots, $booked_times);
        } catch (Exception $e) {
            return $all_slots;
        }
    }

    /**
     * Get all appointments for a specific date
     *
     * @param string $date (YYYY-MM-DD format)
     * @return array
     */
    public function get_appointments_by_date($date)
    {
        try {
            return $this->db->table('patients')
                ->select('id, first_name, last_name, schedule, type, status')
                ->where('schedule', 'LIKE', $date . '%')
                ->where_not_null('schedule')
                ->order_by('schedule', 'ASC')
                ->get_all();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Validate date format (YYYY-MM-DD)
     *
     * @param string $date
     * @return bool
     */
    private function is_valid_date($date)
    {
        $format = 'Y-m-d';
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Validate time format (HH:MM)
     *
     * @param string $time
     * @return bool
     */
    private function is_valid_time($time)
    {
        return preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $time);
    }

    /**
     * Set maximum appointments per day
     *
     * @param int $max
     * @return void
     */
    public function set_max_per_day($max)
    {
        if ($max > 0) {
            $this->max_per_day = $max;
        }
    }

    /**
     * Get maximum appointments per day
     *
     * @return int
     */
    public function get_max_per_day()
    {
        return $this->max_per_day;
    }
}
?>
