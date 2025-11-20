<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class VerificationModel extends Model
{
    protected $table = 'verification_codes';
    protected $primary_key = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    public function create_code(string $email, ?int $user_id, string $purpose, string $code, DateTime $expires_at)
    {
        // remove existing non-expired codes for same email/purpose to avoid duplicates
        $this->db->table($this->table)
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->delete();

        return $this->db->table($this->table)->insert([
            'email' => $email,
            'user_id' => $user_id,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => $expires_at->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Return true if there is a pending (non-expired) code for email/purpose
     */
    public function pending_exists(string $email, string $purpose): bool
    {
        $now = date('Y-m-d H:i:s');
        $email = trim(strtolower($email));
        // if normalize_email helper exists, use it to canonicalize gmail addresses
        if (function_exists('normalize_email')) {
            $email = normalize_email($email);
        }
        $row = $this->db->table($this->table)
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->where('expires_at', '>', $now)
            ->get();
        return !empty($row);
    }

    public function validate_code(string $email, string $code, string $purpose)
    {
        $row = $this->db->table($this->table)
            ->where('email', $email)
            ->where('code', $code)
            ->where('purpose', $purpose)
            ->get();

        if (!$row) return false;

        $now = new DateTimeImmutable('now');
        $expires = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['expires_at']);
        if (!$expires || $expires < $now) {
            // delete expired
            $this->db->table($this->table)->where('id', $row['id'])->delete();
            return false;
        }

        // valid -> consume (delete)
        $this->db->table($this->table)->where('id', $row['id'])->delete();
        return $row;
    }

    public function purge_expired()
    {
        $this->db->table($this->table)->where('expires_at <', date('Y-m-d H:i:s'))->delete();
    }
}
