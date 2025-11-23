<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Controller: Profile
 * 
 * Handles user profile management - viewing, editing, and updating user information.
 */
class Profile extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('UserModel');
    }

    /**
     * Ensure user is authenticated
     */
    private function ensure_authenticated()
    {
        if (!$this->session->userdata('logged_in')) {
            set_flash_alert('danger', 'Please log in to access your profile.');
            redirect('/auth/login');
            exit;
        }
    }

    /**
     * Display user profile
     */
    public function index()
    {
        $this->ensure_authenticated();

        $user_id = $this->session->userdata('user_id');
        $user = $this->UserModel->find_by_id($user_id);

        if (!$user) {
            set_flash_alert('danger', 'User not found.');
            redirect('/dashboard');
            exit;
        }

        $data = [
            'user' => $user
        ];

        $this->call->view('user/view_profile', $data);
    }

    /**
     * Display user profile (for admins viewing other users)
     */
    public function viewUser($id)
    {
        $this->ensure_authenticated();

        // Check if user is admin
        $role = $this->session->userdata('role') ?? '';
        if ($role !== 'admin') {
            set_flash_alert('danger', 'You do not have permission to view other user profiles.');
            redirect('/dashboard');
            exit;
        }

        $user_id = (int) $id;
        $user = $this->UserModel->find_by_id($user_id);

        if (!$user) {
            set_flash_alert('danger', 'User not found.');
            redirect('/admin/users');
            exit;
        }

        $data = [
            'user' => $user,
            'is_admin_viewing' => true
        ];

        $this->call->view('user/view_profile', $data);
    }

    /**
     * Display edit profile form
     */
    public function edit()
    {
        $this->ensure_authenticated();

        $user_id = $this->session->userdata('user_id');
        $user = $this->UserModel->find_by_id($user_id);

        if (!$user) {
            set_flash_alert('danger', 'User not found.');
            redirect('/dashboard');
            exit;
        }

        $data = [
            'user' => $user
        ];

        $this->call->view('user/edit_profile', $data);
    }

    /**
     * Update user profile information
     */
    public function update()
    {
        if ($this->io->method(true) !== 'POST') {
            return redirect('/profile');
        }

        $this->ensure_authenticated();

        $user_id = $this->session->userdata('user_id');
        $first_name = trim($this->io->post('first_name') ?? '');
        $last_name = trim($this->io->post('last_name') ?? '');
        $age = $this->io->post('age') ?? 0;
        $email = trim($this->io->post('email') ?? '');
        $address = trim($this->io->post('address') ?? '');
        $password = trim($this->io->post('password') ?? '');

        // Validation
        if (empty($first_name) || empty($last_name) || empty($email)) {
            set_flash_alert('danger', 'First name, last name, and email are required.');
            return redirect('/profile/edit');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash_alert('danger', 'Please provide a valid email address.');
            return redirect('/profile/edit');
        }

        $age = (int) $age;
        if ($age < 0 || $age > 150) {
            set_flash_alert('danger', 'Please provide a valid age.');
            return redirect('/profile/edit');
        }

        // Check if email is already used by another user
        $existing_user = $this->UserModel->find_by_email($email);
        if ($existing_user && $existing_user['id'] != $user_id) {
            set_flash_alert('danger', 'This email is already registered.');
            return redirect('/profile/edit');
        }

        // Prepare update data
        $update_data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'age' => $age,
            'email' => $email,
            'address' => $address,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Update password only if provided
        if (!empty($password)) {
            if (strlen($password) < 6) {
                set_flash_alert('danger', 'Password must be at least 6 characters long.');
                return redirect('/profile/edit');
            }
            $update_data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Update user in database
        if ($this->UserModel->update_user($user_id, $update_data)) {
            // Update session data
            $this->session->set_userdata([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email
            ]);

            set_flash_alert('success', 'Your profile has been updated successfully.');
            redirect('/profile');
        } else {
            set_flash_alert('danger', 'Failed to update profile. Please try again.');
            redirect('/profile/edit');
        }
    }

    /**
     * Delete user account
     */
    public function deleteAccount()
    {
        if ($this->io->method(true) !== 'POST') {
            return redirect('/profile');
        }

        $this->ensure_authenticated();

        $user_id = $this->session->userdata('user_id');
        $confirmation = trim($this->io->post('confirmation') ?? '');

        // Check confirmation
        if ($confirmation !== 'DELETE') {
            set_flash_alert('danger', 'Invalid confirmation. Please type DELETE to confirm account deletion.');
            return redirect('/profile');
        }

        // Delete user account
        if ($this->UserModel->delete_user($user_id)) {
            $this->session->sess_destroy();
            set_flash_alert('success', 'Your account has been deleted successfully.');
            redirect('/');
        } else {
            set_flash_alert('danger', 'Failed to delete account. Please try again.');
            redirect('/profile');
        }
    }
}
