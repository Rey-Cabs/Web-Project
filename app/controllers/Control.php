<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Controller: Control
 * 
 * Automatically generated via CLI.
 */
class Control extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('UserModel');
        $this->call->model('PatientModel');
        $this->call->model('BatchModel');
        $this->call->model('ItemModel');
        $this->call->model('VerificationModel');

        // Ensure PHPMailer classes are available for sending verification and contact emails
        $phpmailerPath = APP_DIR . 'libraries/phpmailer/src/';
        if (file_exists($phpmailerPath . 'PHPMailer.php')) {
            require_once $phpmailerPath . 'PHPMailer.php';
        }
        if (file_exists($phpmailerPath . 'SMTP.php')) {
            require_once $phpmailerPath . 'SMTP.php';
        }
        if (file_exists($phpmailerPath . 'Exception.php')) {
            require_once $phpmailerPath . 'Exception.php';
        }
    }

        private function get_session()
        {
            if (!isset($this->properties['session']) || $this->properties['session'] === null) {
                $this->properties['session'] = $this->call->library('session');
            }
            return $this->properties['session'];
        }
    // Admin-only access helper
    private function only_admin() {
        $role = $this->session->userdata('role') ?? '';
        if ($role !== 'admin') {
            set_flash_alert('danger', 'You do not have permission to access this page.');
            redirect('/');
            exit;
        }
    }

    // Pagination helper
    private function configure_pagination(int $total_rows, int $records_per_page, int $page, string $base_path)
    {
        $this->pagination->set_options([
            'first_link'     => '⏮ First',
            'last_link'      => 'Last ⏭',
            'next_link'      => 'Next →',
            'prev_link'      => '← Prev',
            'page_delimiter' => '&page='
        ]);
        $this->pagination->set_theme('custom');
        $this->pagination->initialize($total_rows, $records_per_page, $page, $base_path);
    }

    // Current page helper
    private function current_page(): int
    {
        $page = (int) ($this->io->get('page') ?? 1);
        return $page > 0 ? $page : 1;
    }

    /*** Public Pages ***/
    public function Landing()
    {
        $this->call->view('Home');
    }

    public function About()
    {
        $this->call->view('about');
    }

    public function Contact()
    {
        $this->call->view('contact');
    }

    /*** Dashboard ***/
   public function Dashboard()
{
    if (!$this->session->userdata('logged_in')) {
        set_flash_alert('danger', 'Please log in to access the dashboard.');
        redirect('/auth/login');
        exit;
    }

    $userId = $this->session->userdata('user_id');
    $role   = $this->session->userdata('role');

    // Default counts
    $totalPatients = $scheduledAppointments = $newPatients = $pendingPrescriptions = 0;
    $upcomingAppointments = $pastAppointments = [];
    $activeMedications = $completedMedications = [];

    if ($role === 'admin') {
        // Admin sees totals and charts
        $totalPatients = (int) ($this->PatientModel->db->table('patients')->select_count('*', 'c')->get()['c'] ?? 0);
        $scheduledAppointments = (int) ($this->PatientModel->db->table('patients')->where_not_null('schedule')->select_count('*', 'c')->get()['c'] ?? 0);
        $since = date('Y-m-d 00:00:00', strtotime('-30 days'));
        $newPatients = (int) ($this->PatientModel->db->table('patients')->where('date_created', '>=', $since)->select_count('*', 'c')->get()['c'] ?? 0);
        $pendingPrescriptions = (int) ($this->PatientModel->db->table('patients')->where_not_null('medicine')->where('medicine', '!=', '')->select_count('*', 'c')->get()['c'] ?? 0);
    } else {
        // User sees only their own data
        $upcomingAppointments = $this->PatientModel->get_upcoming_by_user($userId);
        $pastAppointments     = $this->PatientModel->get_past_by_user($userId);
        $activeMedications    = $this->MedicationModel->get_active_by_user($userId);
        $completedMedications = $this->MedicationModel->get_completed_by_user($userId);
    }

    $data = [
        'user'                   => $this->session->userdata(),
        'role'                   => $role,
        'totalPatients'          => $totalPatients,
        'scheduledAppointments'  => $scheduledAppointments,
        'newPatients'            => $newPatients,
        'pendingPrescriptions'   => $pendingPrescriptions,
        'upcomingAppointments'   => $upcomingAppointments,
        'pastAppointments'       => $pastAppointments,
        'activeMedications'      => $activeMedications,
        'completedMedications'   => $completedMedications
    ];

    $this->call->view('Dashboard', $data);
}

/*** Charts ***/
public function PatientsChart()
{
    if (!$this->session->userdata('logged_in')) {
        http_response_code(403);
        exit('Unauthorized');
    }

    $period = strtolower(trim($this->io->get('period') ?? 'weekly'));
    $period = in_array($period, ['weekly','monthly','yearly'], true) ? $period : 'weekly';
    $count = $period === 'weekly' ? 7 : ($period === 'monthly' ? 30 : 12);

    $result = $this->PatientModel->aggregate_counts($period, $count);

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

public function PatientsDisease()
{
    if (!$this->session->userdata('logged_in')) {
        http_response_code(403);
        exit('Unauthorized');
    }

    $topN = 6; // configurable number of top diseases
    $agg = $this->PatientModel->aggregate_by_disease($topN);

    $payload = [];
    $labels  = $agg['labels'] ?? [];
    $data    = $agg['data'] ?? [];

    $total = array_sum($data) ?: 1;
    foreach ($labels as $idx => $label) {
        $count = (int)($data[$idx] ?? 0);
        $payload[] = [
            'disease'     => $label,
            'count'       => $count,
            'percentage'  => round(($count / $total) * 100, 2),
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

public function PatientsPredict()
{
    if (!$this->session->userdata('logged_in')) {
        http_response_code(403);
        exit('Unauthorized');
    }

    $period = strtolower(trim($this->io->get('period') ?? 'weekly'));
    $period = in_array($period, ['weekly','monthly','yearly'], true) ? $period : 'weekly';

    $count = $period === 'weekly' ? 7 : ($period === 'monthly' ? 30 : 12);

    // Get aggregate counts (labels + data series)
    $agg = $this->PatientModel->aggregate_counts($period, $count);
    $labels = $agg['labels'] ?? [];
    $values = $agg['data'] ?? [];

    // Build historical payload expected by JS: array of {label, count}
    $historicalPayload = [];
    foreach ($labels as $idx => $label) {
        $historicalPayload[] = [
            'label' => $label,
            'count' => (int)($values[$idx] ?? 0),
        ];
    }

    // Rolling-average prediction over the values series
    $predictedData = [];
    $rollingWindow = 3;
    $valueCount = count($values);
    for ($i = 0; $i < $valueCount; $i++) {
        $window = array_slice($values, max(0, $i - $rollingWindow + 1), $rollingWindow);
        $denom  = count($window) ?: 1;
        $predictedData[] = (int)round(array_sum($window) / $denom);
    }

    header('Content-Type: application/json');
    echo json_encode([
        'historical' => $historicalPayload,
        'predicted'  => $predictedData
    ]);
    exit;
}


    /*** Authentication ***/
public function login() {
    if ($this->form_validation->submitted()) {
        $identifier = trim($this->io->post('email'));
        $password   = $this->io->post('password');

        $user_id = $this->lauth->login_by_identifier($identifier, $password);

        if ($user_id) {
            $user = $this->UserModel->find($user_id);

            if (!$user) {
                set_flash_alert('danger', 'User not found.');
                redirect('auth/login');
                exit;
            }

            $role = $this->lauth->get_user_role($user_id);

            $this->session->sess_regenerate(TRUE);
            $this->session->set_userdata([
                'user_id'    => $user['id'],
                'user_email' => $user['email'],
                'user_name'  => $user['first_name'] . ' ' . $user['last_name'],
                'role'       => $role ?? 'user',
                'first_name' => $user['first_name'],
                'logged_in'  => TRUE
            ]);

            $this->lauth->set_logged_in($user_id);
            redirect('/dashboard');
            exit;
        } else {
            // Credentials invalid: reload login page with flash
            set_flash_alert('danger', 'Invalid email or password.');
            redirect('auth/login');
            exit;
        }
    } else {
        // Show login page
        $this->call->view('user/login');
    }
}


    public function Signup()
    {
        if ($this->io->method(true) === 'POST') {
            $first_name = trim($this->io->post('first_name') ?? '');
            $last_name  = trim($this->io->post('last_name') ?? '');
            $age        = (int) ($this->io->post('age') ?? 0);
            $email      = trim($this->io->post('email') ?? '');
            $email = normalize_email($email);
            $address    = trim($this->io->post('address') ?? '');
            $password   = (string) ($this->io->post('password') ?? '');
            $confirm    = (string) ($this->io->post('confirm_password') ?? '');
            $role = trim($this->io->post('role') ?? 'user');
            if (!in_array($role, ['user', 'admin'], true)) $role = 'user';

            if ($first_name === '' || $last_name === '' || $age <= 0 || $email === '' || $password === '') {
                set_flash_alert('danger', 'Please complete all required fields.');
                return redirect('/auth/signup');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                set_flash_alert('danger', 'Please provide a valid email address.');
                return redirect('/auth/signup');
            }

            if ($password !== $confirm) {
                set_flash_alert('danger', 'Passwords do not match.');
                return redirect('/auth/signup');
            }

            if ($this->UserModel->find_by_email($email)) {
                set_flash_alert('danger', 'Email address is already registered.');
                return redirect('/auth/signup');
            }

            if ($this->VerificationModel->pending_exists($email, 'signup')) {
                set_flash_alert('info', 'A verification code has already been sent to this email. Please check your inbox to complete signup.');
                return redirect('/auth/verify?purpose=signup');
            }

            $pending = [
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'age'        => $age,
                'email'      => $email,
                'address'    => $address,
                'password'   => password_hash($password, PASSWORD_DEFAULT),
                'role'       => $role
            ];
            $this->session->set_userdata(['pending_signup' => $pending]);

            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $this->VerificationModel->create_code($email, null, 'signup', $code, new DateTime('+5 minutes'));

            try {
                $m = new PHPMailer\PHPMailer\PHPMailer(true);
                $m->isSMTP();
                $m->Host       = 'smtp.gmail.com';
                $m->SMTPAuth   = true;
                $m->Username   = 'jhonreycabral@gmail.com';
                $m->Password   = 'psazwvwetovmftmo';
                $m->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $m->Port       = 587;
                $m->setFrom('jhonreycabral@gmail.com', 'HealthSync');
                $m->addAddress($email, $first_name);
                $m->isHTML(true);
                $m->Subject = 'Your HealthSync signup verification code';

                // **Your original styled HTML for signup email**
                $m->Body = '
                    <div style="font-family: Arial, sans-serif; background-color:#F0E68C; padding:20px;">
                        <div style="max-width:600px; margin:auto; background:white; border-radius:8px; overflow:hidden;
                                    border:3px solid #B00020;">
                            <div style="background:#B00020; padding:15px; text-align:center;">
                                <h1 style="color:white; margin:0; font-size:24px;">HealthSync Verification</h1>
                            </div>
                            <div style="padding:25px; color:#333;">
                                <p style="font-size:16px;">Hello <strong>' . htmlspecialchars($first_name) . '</strong>,</p>
                                <p style="font-size:16px;">
                                    Thank you for signing up with <strong>HealthSync</strong>.<br>
                                    Use the verification code below to complete your registration:
                                </p>
                                <div style="
                                    margin:25px 0;
                                    padding:20px;
                                    background:#F0E68C;
                                    border:2px dashed #B00020;
                                    text-align:center;
                                    border-radius:6px;
                                ">
                                    <span style="font-size:32px; font-weight:bold; color:#B00020;">' . $code . '</span>
                                </div>
                                <p style="font-size:15px;">
                                    This code is valid for <strong>5 minutes</strong>.  
                                    If you did not request this, please ignore this email.
                                </p>
                            </div>
                            <div style="background:#B00020; text-align:center; padding:10px;">
                                <p style="color:white; margin:0; font-size:14px;">
                                    © ' . date("Y") . ' HealthSync. All rights reserved.
                                </p>
                            </div>
                        </div>
                    </div>';
                $m->send();
            } catch (\Exception $e) { /* ignore */ }

            set_flash_alert('success', 'A verification code has been sent to your email. Please enter it to complete signup.');
            return redirect('/auth/verify?purpose=signup');
        }

        $this->call->view('user/Signup');
    }

    /*** Logout ***/
    public function Logout()
    {
        $this->session->sess_destroy();
        set_flash_alert('success', 'You have been logged out.');
        redirect('/auth/login');
        exit;
    }

    public function SendMessage()
    {
        if ($this->io->method(true) !== 'POST') {
            return redirect('/contact');
        }

        $name    = trim($this->io->post('name') ?? '');
        $email   = trim($this->io->post('email') ?? '');
        $message = trim($this->io->post('message') ?? '');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
            set_flash_alert('danger', 'Please fill out the form completely.');
            return redirect('/contact');
        }

        require_once APP_DIR . 'libraries/phpmailer/src/PHPMailer.php';
        require_once APP_DIR . 'libraries/phpmailer/src/SMTP.php';
        require_once APP_DIR . 'libraries/phpmailer/src/Exception.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'jhonreycabral@gmail.com';
            $mail->Password   = 'psazwvwetovmftmo';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom($email, $name);
            $mail->addReplyTo($email, $name);
            $mail->addAddress('jhonreycabral@gmail.com', 'Healthcare Admin');

            $mail->isHTML(true);
            $mail->Subject = 'HealthSync Contact Form Message';
            $mail->Body = '
                    <div style="font-family: Arial, sans-serif; background-color:#F0E68C; padding:20px;">
                        <div style="max-width:600px; margin:auto; background:#fff; border-radius:8px; 
                                    border:3px solid #B00020; overflow:hidden;">

                            <div style="background:#B00020; padding:15px; text-align:center;">
                                <h2 style="color:white; margin:0;">New Contact Form Message</h2>
                            </div>

                            <div style="padding:20px; color:#333;">
                                <p style="font-size:16px;"><strong>Sender Name:</strong> ' . html_escape($name) . '</p>
                                <p style="font-size:16px;"><strong>Sender Email:</strong> ' . html_escape($email) . '</p>

                                <div style="margin-top:20px; padding:15px; 
                                            background:#F0E68C; border-left:4px solid #B00020; 
                                            border-radius:5px;">
                                    <p style="white-space:pre-wrap; font-size:15px;">' . nl2br(html_escape($message)) . '</p>
                                </div>
                            </div>

                            <div style="background:#B00020; padding:10px; text-align:center;">
                                <p style="color:white; margin:0; font-size:13px;">
                                    HealthSync — Contact Inquiry Notification
                                </p>
                            </div>

                        </div>
                    </div>';

            $mail->AltBody = "Sender: {$name} ({$email})\n\n{$message}";

            $mail->send();
            set_flash_alert('success', 'Thank you for reaching out. We will get back to you shortly.');
            // Send confirmation email back to sender
            try {
                $confirm = new PHPMailer\PHPMailer\PHPMailer(true);
                $confirm->isSMTP();
                $confirm->Host       = 'smtp.gmail.com';
                $confirm->SMTPAuth   = true;
                $confirm->Username   = 'jhonreycabral@gmail.com';
                $confirm->Password   = 'psazwvwetovmftmo';
                $confirm->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $confirm->Port       = 587;
                $confirm->setFrom('jhonreycabral@gmail.com', 'HealthSync');
                $confirm->addAddress($email, $name);
                $confirm->isHTML(true);
                $confirm->Subject = 'We received your message';
                $confirm->Body = '
                    <div style="font-family: Arial, sans-serif; background-color:#F0E68C; padding:20px;">
                        <div style="max-width:600px; margin:auto; background:#fff; border-radius:8px;
                                    overflow:hidden; border:3px solid #B00020;">

                            <div style="background:#B00020; padding:15px; text-align:center;">
                                <h2 style="color:white; margin:0;">We Received Your Message</h2>
                            </div>

                            <div style="padding:25px; color:#333;">
                                <p style="font-size:16px;">Hi <strong>' . html_escape($name) . '</strong>,</p>

                                <p style="font-size:16px;">
                                    Thank you for reaching out to <strong>HealthSync</strong>.<br>
                                    We have successfully received your message and our team will contact you shortly.
                                </p>

                                <div style="margin-top:25px; padding:15px; 
                                            background:#F0E68C; border-left:4px solid #B00020;
                                            border-radius:5px;">
                                    <p style="font-size:15px;">Your Message:</p>
                                    <p style="white-space:pre-wrap; font-size:15px; margin:0;">' . nl2br(html_escape($message)) . '</p>
                                </div>

                                <p style="font-size:15px; margin-top:20px;">
                                    Warm regards,<br>
                                    <strong>HealthSync Team</strong>
                                </p>
                            </div>

                            <div style="background:#B00020; padding:10px; text-align:center;">
                                <p style="color:white; margin:0; font-size:13px;">
                                    © ' . date("Y") . ' HealthSync. All rights reserved.
                                </p>
                            </div>

                        </div>
                    </div>';

                $confirm->send();
            } catch (\Exception $e) {
                // ignore confirmation send errors
            }
        } catch (PHPMailer\PHPMailer\Exception $e) {
            set_flash_alert('danger', 'Unable to send message: ' . $mail->ErrorInfo);
        } catch (\Exception $e) {
            set_flash_alert('danger', 'Unexpected error while sending message.');
        }

        redirect('/contact');
        exit;
    }

    public function Verify()
    {
        $purpose = trim($this->io->get('purpose') ?? $this->io->post('purpose') ?? '');
        if ($this->io->method(true) === 'POST') {
            $code = trim($this->io->post('code') ?? '');
            if ($code === '') {
                set_flash_alert('danger', 'Please enter the verification code.');
                return redirect('/auth/verify?purpose=' . urlencode($purpose));
            }

            // resolve email based on purpose and session
            if ($purpose === 'signup') {
                $pending = $this->session->userdata('pending_signup');
                if (empty($pending) || empty($pending['email'])) {
                    set_flash_alert('danger', 'No pending signup found. Please fill the signup form again.');
                    return redirect('/auth/signup');
                }
                $email = $pending['email'];
                $row = $this->VerificationModel->validate_code($email, $code, 'signup');
                if (!$row) {
                    set_flash_alert('danger', 'Invalid or expired verification code. Please try again.');
                    return redirect('/auth/verify?purpose=signup');
                }
                // create user now
                $user_id = $this->UserModel->insert([
                    'first_name' => $pending['first_name'],
                    'last_name'  => $pending['last_name'],
                    'age'        => $pending['age'],
                    'email'      => $pending['email'],
                    'address'    => $pending['address'],
                    'password'   => $pending['password'],
                    'role'       => $pending['role'] ?? 'user'
                ]);
                $this->session->unset_userdata('pending_signup');
                if ($user_id) {
                    set_flash_alert('success', 'Account created successfully. You can now log in.');
                    return redirect('/auth/login');
                }
                set_flash_alert('danger', 'Unable to create account. Please try again.');
                return redirect('/auth/signup');
            }

            if ($purpose === 'login') {
                $pending = $this->session->userdata('pending_login');
                if (empty($pending) || empty($pending['email'])) {
                    set_flash_alert('danger', 'No pending login found. Please sign in again.');
                    return redirect('/auth/login');
                }
                $email = $pending['email'];
                $row = $this->VerificationModel->validate_code($email, $code, 'login');
                if (!$row) {
                    set_flash_alert('danger', 'Invalid or expired verification code.');
                    return redirect('/auth/verify?purpose=login');
                }
                // finalize login
                $user = $this->UserModel->find_by_email($email);
                if (!$user) {
                    set_flash_alert('danger', 'User not found.');
                    return redirect('/auth/login');
                }
                $this->session->sess_regenerate(TRUE);
                $this->session->set_userdata([
                    'user_id'    => $user['id'],
                    'user_email' => $user['email'],
                    'user_name'  => $user['first_name'] . ' ' . $user['last_name'],
                    'role'       => $user['role'] ?? 'user',
                    'logged_in'  => TRUE
                ]);
                $this->session->unset_userdata('pending_login');
                set_flash_alert('success', 'Welcome back, ' . html_escape($user['first_name']) . '!');
                return redirect('/dashboard');
            }

            if ($purpose === 'password_reset') {
                $email = trim($this->session->userdata('password_reset_email') ?? '');
                if ($email === '') {
                    set_flash_alert('danger', 'No password reset request found.');
                    return redirect('/auth/forgot');
                }
                $row = $this->VerificationModel->validate_code($email, $code, 'password_reset');
                if (!$row) {
                    set_flash_alert('danger', 'Invalid or expired verification code.');
                    return redirect('/auth/verify?purpose=password_reset');
                }
                // mark allowed to reset
                $this->session->set_userdata(['password_reset_allowed' => $email]);
                return redirect('/auth/reset_password');
            }

            set_flash_alert('danger', 'Unknown verification purpose.');
            return redirect('/auth/login');
        }

        // show form
        $purpose = $this->io->get('purpose') ?? 'signup';
        $this->call->view('user/Verify', ['purpose' => $purpose]);
    }

    public function Forgot()
    {
        if ($this->io->method(true) !== 'POST') {
            return $this->call->view('user/Forgot');
        }

        $email = trim($this->io->post('email') ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash_alert('danger', 'Please provide a valid email.');
            return redirect('/auth/forgot');
        }
        $user = $this->UserModel->find_by_email($email);
        if (!$user) {
            set_flash_alert('danger', 'No account found with that email.');
            return redirect('/auth/forgot');
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->VerificationModel->create_code($email, $user['id'], 'password_reset', $code, new DateTime('+5 minutes'));

        // store email in session to track reset
        $this->session->set_userdata(['password_reset_email' => $email]);

        // send email
        try {
            $m = new PHPMailer\PHPMailer\PHPMailer(true);
            $m->isSMTP();
            $m->Host       = 'smtp.gmail.com';
            $m->SMTPAuth   = true;
            $m->Username   = 'jhonreycabral@gmail.com';
            $m->Password   = 'psazwvwetovmftmo';
            $m->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $m->Port       = 587;
            $m->setFrom('jhonreycabral@gmail.com', 'HealthSync');
            $m->addAddress($email, $user['first_name']);
            $m->isHTML(true);
            $m->Subject = 'Password reset verification code';
            $m->Body = 'Your password reset code is <strong>' . $code . '</strong>. It is valid for 5 minutes.';
            $m->send();
        } catch (\Exception $e) {
        }

        set_flash_alert('success', 'A verification code has been sent to your email.');
        return redirect('/auth/verify?purpose=password_reset');
    }

    public function ResetPassword()
    {
        if ($this->io->method(true) !== 'POST') {
            return $this->call->view('user/ResetPassword');
        }
        $email = $this->session->userdata('password_reset_allowed') ?? '';
        if (empty($email)) {
            set_flash_alert('danger', 'Password reset session expired or invalid.');
            return redirect('/auth/forgot');
        }
        $password = (string) ($this->io->post('password') ?? '');
        $confirm = (string) ($this->io->post('confirm_password') ?? '');
        if ($password === '' || $password !== $confirm) {
            set_flash_alert('danger', 'Passwords are required and must match.');
            return redirect('/auth/reset_password');
        }
    // update password directly via DB (UserModel doesn't expose update_by_email helper)
    $this->UserModel->db->table('users')->where('email', $email)->update(['password' => password_hash($password, PASSWORD_DEFAULT)]);
        $this->session->unset_userdata('password_reset_allowed');
        $this->session->unset_userdata('password_reset_email');
        set_flash_alert('success', 'Password updated. You may now log in.');
        return redirect('/auth/login');
    }

   public function Patients()
{
    $page    = $this->current_page();
    $q       = trim($this->io->get('q') ?? '');
    $perPage = 10;

    // Get user role and user_id from session
    $role    = $this->session->userdata('role') ?? 'user';
    $user_id = $this->session->userdata('user_id') ?? null;

    // Initialize variables
    $patients     = [];
    $patientInfo  = null;
    $show_register_prompt = false;
    $isPatient = false; // <- fix for undefined variable

    // Fetch patients based on role
    if ($role === 'admin') {
        // Admin sees all patients
        $result = $this->PatientModel->get_paginated($q, $perPage, $page);
    } else {
        // Regular user: fetch their own patient info
        $userPatients = $this->PatientModel->get_by_role('user', $user_id, $q);
        $total = count($userPatients);

        // Pagination (for consistency)
        $offset = ($page - 1) * $perPage;
        $patients = array_slice($userPatients, $offset, $perPage);

        // Check if user has a patient record
        if (!empty($patients)) {
            $patientInfo = $patients[0];
            $isPatient = true; // user has a patient record
        } else {
            $show_register_prompt = true;
            $isPatient = false; // user is not yet a patient
        }

        $result = [
            'records' => $patients,
            'total_rows' => $total
        ];
    }

    // Configure pagination
    $this->configure_pagination($result['total_rows'], $perPage, $page, '/patients?q=' . urlencode($q));

    // Prepare data for view
    $data = [
        'patients'             => $result['records'],
        'pagination'           => $this->pagination->paginate(),
        'search_term'          => $q,
        'role'                 => $role,
        'patientInfo'          => $patientInfo,
        'show_register_prompt' => $show_register_prompt,
        'isPatient'            => $isPatient // pass variable to view
    ];

    // Load patient view
    $this->call->view('patient', $data);
}



    public function Appointments()
{
    $page    = $this->current_page();
    $q       = trim($this->io->get('q') ?? '');
    $perPage = 10;

    // Get user role and user_id from session
    $role    = $this->session->userdata('role') ?? 'user';
    $user_id = $this->session->userdata('user_id') ?? null;

    if ($role === 'admin') {
        // Admin sees all appointments
        $result = $this->PatientModel->get_paginated($q, $perPage, $page, [
            'has_schedule' => true
        ]);
        $isPatient = true; // Admin can see all
    } else {
        // Regular user: fetch only their patient record
        $patients = $this->PatientModel->get_by_role('user', $user_id);

        if (!empty($patients)) {
            $appointments_all = [];
            foreach ($patients as $p) {
                if (!empty($p['schedule'])) {
                    $appointments_all[] = $p;
                }
            }

            $total = count($appointments_all);
            $offset = ($page - 1) * $perPage;
            $result = [
                'records' => array_slice($appointments_all, $offset, $perPage),
                'total_rows' => $total
            ];
            $isPatient = true;
        } else {
            $result = [
                'records' => [],
                'total_rows' => 0
            ];
            $isPatient = false;
        }
    }

    // Configure pagination
    $this->configure_pagination($result['total_rows'], $perPage, $page, '/appointments?q=' . urlencode($q));

    $data = [
        'appointments'        => $result['records'],
        'pagination'          => $this->pagination->paginate(),
        'search_term'         => $q,
        'role'                => $role,
        'isPatient'           => $isPatient,
        'show_register_prompt' => !$isPatient && $role !== 'admin'
    ];

    $this->call->view('appointments', $data);
}
public function Medications()
{
    $page    = $this->current_page();
    $q       = trim($this->io->get('q') ?? '');
    $perPage = 10;

    $session = $this->get_session();
    $userId  = $session->userdata('user_id'); // logged-in user
    $role    = $session->userdata('role') ?? 'user'; // get role, default to 'user'

    // Fetch paginated medications for the current user only
    $result = $this->PatientModel->get_paginated($q, $perPage, $page, [
        'has_medicine' => true,
        'user_id'      => $userId,
    ]);

    $today = new DateTime();

    foreach ($result['records'] as &$medication) {

        // Skip medications not belonging to the current user
        if (!isset($medication['user_id']) || $medication['user_id'] !== $userId) continue;

        // Ensure we have start_date and end_date
        if (empty($medication['start_date']) || empty($medication['end_date'])) {
            if (!empty($medication['given_date']) && !empty($medication['duration'])) {
                $dates = $this->calculateMedicationDates($medication['given_date'], $medication['duration']);
                $medication['start_date'] = $dates['start_date'];
                $medication['end_date']   = $dates['end_date'];
            } else {
                $medication['status'] = 'Unknown';
                continue;
            }
        }

        // Calculate status
        $start = new DateTime($medication['start_date']);
        $end   = new DateTime($medication['end_date']);

        if ($today < $start) {
            $medication['status'] = 'Upcoming';
        } elseif ($today > $end) {
            $medication['status'] = 'Completed';
        } else {
            $medication['status'] = 'Ongoing';
        }
    }
    unset($medication); // break reference

    $this->configure_pagination($result['total_rows'], $perPage, $page, '/medications?q=' . urlencode($q));

    $data = [
        'medications' => $result['records'],
        'pagination'  => $this->pagination->paginate(),
        'search_term' => $q,
        'userId'      => $userId,
        'role'        => $role, // pass role to the view
    ];

    $this->call->view('medications', $data);
}


/**
 * Calculate start and end dates based on given date and duration.
 * Duration can be in days, weeks, or months, e.g., '7 days', '1 week', '1 month'
 */
private function calculateMedicationDates($givenDate, $duration)
{
    $start = new DateTime($givenDate);
    $end   = clone $start;

    // Convert duration into a string suitable for DateTime modify
    $durationStr = trim($duration);
    if (is_numeric($durationStr)) {
        $durationStr .= ' days'; // default to days if numeric
    }
    $end->modify("+$durationStr");

    return [
        'start_date' => $start->format('Y-m-d'),
        'end_date'   => $end->format('Y-m-d')
    ];
}
public function Records()
{
    $page    = $this->current_page();
    $q       = trim($this->io->get('q') ?? '');
    $perPage = 10;

    $userId = $this->session->userdata('user_id');
    $role   = $this->session->userdata('role'); // 'admin' or 'user'

    if ($role === 'admin') {
        // Admin: fetch all patients with pagination
        $result = $this->PatientModel->get_paginated($q, $perPage, $page);
    } else {
        // Normal user: fetch only their own record
        $result = $this->PatientModel->get_paginated($q, $perPage, $page, [
            'patient_id' => $userId
        ]);
    }

    $this->configure_pagination($result['total_rows'], $perPage, $page, '/records?q=' . urlencode($q));

    $records = [];
    foreach ($result['records'] as $patient) {
        $records[] = [
            'id'         => $patient['id'],
            'patient_id' => $patient['id'],
            'first_name' => $patient['first_name'],
            'last_name'  => $patient['last_name'],
            'age'        => $patient['age'],
            'email'      => $patient['email'],
            'disease'    => $patient['disease'] ?? '-',
            'type'       => $patient['type'] ?? '-',
            'medicine'   => $patient['medicine'] ?? '-',
            'schedule'   => $patient['schedule'] ?? '-',
            'duration'   => $patient['duration'] ?? '-',
            'status'     => $patient['status'] ?? '-',
        ];
    }

    $data = [
        'records'     => $records,
        'pagination'  => $this->pagination->paginate(),
        'search_term' => $q,
        'role'        => $role
    ];

    $this->call->view('records', $data);
}

public function Inventory()
{
    $page    = $this->current_page();
    $q       = trim($this->io->get('q') ?? '');
    $perPage = 10;

    $category = trim($this->io->get('category') ?? '');
    $allowedCategories = ['Medicine','Equipment','Supply','Other'];
    if (!in_array($category, $allowedCategories, true)) {
        $category = '';
    }

    $result  = $this->BatchModel->paginate_with_items($q, $perPage, $page, $category);
    $summary = $this->BatchModel->inventory_summary();

    // Totals for top cards: always reflect all stock in main/reserve
    $totalMain    = $this->BatchModel->total_by_location('main');
    $totalReserve = $this->BatchModel->total_by_location('reserve');

    // Summary for table/chart: filtered by category and search term (if any)
    $filteredSummary = [];
    $searchTerm = strtolower($q);
    foreach ($summary as $row) {
        // Filter by category
        if ($category !== '' && ($row['category'] ?? '') !== $category) {
            continue;
        }

        // Filter by search term on item name or category
        if ($searchTerm !== '') {
            $name = strtolower($row['item_name'] ?? '');
            $cat  = strtolower($row['category'] ?? '');
            if (strpos($name, $searchTerm) === false && strpos($cat, $searchTerm) === false) {
                continue;
            }
        }

        // Normalize critical level for display/logic (fixed at 10 for all items)
        $row['critical_level'] = 10;

        $filteredSummary[] = $row;
    }

    $this->configure_pagination(
        $result['total_rows'],
        $perPage,
        $page,
        '/inventory?q=' . urlencode($q) . '&category=' . urlencode($category)
    );

    $data = [
        'batches'       => $result['records'],
        'pagination'    => $this->pagination->paginate(),
        'search_term'   => $q,
        'category'      => $category,
        'total_main'    => $totalMain,
        'total_reserve' => $totalReserve,
        'summary'       => $filteredSummary
    ];

    $this->call->view('Inventory', $data);
}

public function recordView($id)
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
    $history = $this->PatientModel->get_past_by_user($userId);
    $upcoming = $this->PatientModel->get_upcoming_by_user($userId);

    $data = [
        'patient'  => $patient,
        'history'  => $history,
        'upcoming' => $upcoming,
        'role'     => $role
    ];

    $this->call->view('record_view', $data);
}
}