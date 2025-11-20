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

    private function current_page(): int
    {
        $page = (int) ($this->io->get('page') ?? 1);
        return $page > 0 ? $page : 1;
    }

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

    public function Dashboard()
    {
        $totalPatients = (int) ($this->PatientModel->db->table('patients')->select_count('*', 'c')->get()['c'] ?? 0);
        $scheduledAppointments = (int) ($this->PatientModel->db->table('patients')->where_not_null('schedule')->select_count('*', 'c')->get()['c'] ?? 0);
    $since = date('Y-m-d 00:00:00', strtotime('-30 days'));
    $newPatients = (int) ($this->PatientModel->db->table('patients')->where('date_created', '>=', $since)->select_count('*', 'c')->get()['c'] ?? 0);
        $pendingPrescriptions = (int) ($this->PatientModel->db->table('patients')->where_not_null('medicine')->where('medicine', '!=', '')->select_count('*', 'c')->get()['c'] ?? 0);

        $data = [
            'totalPatients' => $totalPatients,
            'scheduledAppointments' => $scheduledAppointments,
            'newPatients' => $newPatients,
            'pendingPrescriptions' => $pendingPrescriptions
        ];

        $this->call->view('Dashboard', $data);
    }
    public function PatientsChart()
    {
        $period = trim(strtolower($this->io->get('period') ?? 'weekly'));
        if (!in_array($period, ['weekly','monthly','yearly'], true)) {
            $period = 'weekly';
        }

        $count = 4;
        if ($period === 'weekly') $count = 7;

        $result = $this->PatientModel->aggregate_counts($period, $count);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    public function PatientsDisease()
    {
        $result = $this->PatientModel->aggregate_by_disease(6);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    public function PatientsPredict()
    {
        $period = trim(strtolower($this->io->get('period') ?? 'weekly'));
        if (!in_array($period, ['weekly','monthly','yearly'], true)) {
            $period = 'weekly';
        }
        $result = $this->PatientModel->predict_next($period);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    public function Login()
    {
        if ($this->io->method(true) === 'POST') {
            $email = trim($this->io->post('email') ?? '');
            $email = normalize_email($email);
            $password = (string) ($this->io->post('password') ?? '');

            if ($email === '' || $password === '') {
                set_flash_alert('danger', 'Email and password are required.');
                return redirect('/auth/login');
            }

            $user = $this->UserModel->find_by_email($email);
            try {
                $verified = $user ? (password_verify($password, $user['password']) ? 'yes' : 'no') : 'no_user';
                $debug = [
                    'email' => $email,
                    'found'  => $user ? 'yes' : 'no',
                    'pw_length' => isset($user['password']) ? strlen($user['password']) : 0,
                    'verify' => $verified
                ];
                @file_put_contents(APP_DIR . 'logs/auth_debug.log', date('c') . ' ' . json_encode($debug) . PHP_EOL, FILE_APPEND);
            } catch (\Exception $e) {
            }
            if ($user && password_verify($password, $user['password'])) {
                // instead of logging in immediately, send a 6-digit code to the user's email and require verification
                // if a pending login code already exists, don't create another to avoid duplicates
                if ($this->VerificationModel->pending_exists($email, 'login')) {
                    set_flash_alert('info', 'A verification code was already sent to your email. Please check your inbox.');
                    return redirect('/auth/verify?purpose=login');
                }

                $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $this->VerificationModel->create_code($email, $user['id'], 'login', $code, new DateTime('+5 minutes'));

                // store pending login data in session
                $this->session->set_userdata(['pending_login' => ['user_id' => $user['id'], 'email' => $email, 'first_name' => $user['first_name']]]);

                // send email with PHPMailer
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
                    $m->Subject = 'Your HealthSync login verification code';
                    $m->Body = 
                        '<div style="font-family: Arial, sans-serif; background-color:#F0E68C; padding:20px;">
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

                        </div ';                   

                    $m->send();
                } catch (\Exception $e) {
                }

                set_flash_alert('success', 'A verification code was sent to your email. Enter the code to complete login.');
                return redirect('/auth/verify?purpose=login');
            }

            set_flash_alert('danger', 'Invalid email or password.');
            return redirect('/auth/login');
        }

        $this->call->view('user/Login');
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
            if (!in_array($role, ['user', 'admin'], true)) {
                $role = 'user';
            }

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
            } catch (\Exception $e) {
            }

            set_flash_alert('success', 'A verification code has been sent to your email. Please enter it to complete signup.');
            return redirect('/auth/verify?purpose=signup');
        }

        $this->call->view('user/Signup');
    }

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

        $result = $this->PatientModel->get_paginated($q, $perPage, $page);
        $this->configure_pagination($result['total_rows'], $perPage, $page, '/patients?q=' . urlencode($q));

        $data = [
            'patients'    => $result['records'],
            'pagination'  => $this->pagination->paginate(),
            'search_term' => $q
        ];

        $this->call->view('patient', $data);
    }

    public function Appointments()
    {
        $page    = $this->current_page();
        $q       = trim($this->io->get('q') ?? '');
        $perPage = 10;

        $result = $this->PatientModel->get_paginated($q, $perPage, $page, [
            'has_schedule' => true
        ]);

        $this->configure_pagination($result['total_rows'], $perPage, $page, '/appointments?q=' . urlencode($q));

        $data = [
            'appointments' => $result['records'],
            'pagination'   => $this->pagination->paginate(),
            'search_term'  => $q
        ];

        $this->call->view('appointments', $data);
    }

    public function Medications()
    {
        $page    = $this->current_page();
        $q       = trim($this->io->get('q') ?? '');
        $perPage = 10;

        $result = $this->PatientModel->get_paginated($q, $perPage, $page, [
            'has_medicine' => true
        ]);

        $this->configure_pagination($result['total_rows'], $perPage, $page, '/medications?q=' . urlencode($q));

        $data = [
            'medications' => $result['records'],
            'pagination'  => $this->pagination->paginate(),
            'search_term' => $q
        ];

        $this->call->view('medications', $data);
    }

    public function Records()
    {
        $page    = $this->current_page();
        $q       = trim($this->io->get('q') ?? '');
        $perPage = 10;

        $result = $this->PatientModel->get_paginated($q, $perPage, $page);
        $this->configure_pagination($result['total_rows'], $perPage, $page, '/records?q=' . urlencode($q));

        $data = [
            'records'     => $result['records'],
            'pagination'  => $this->pagination->paginate(),
            'search_term' => $q
        ];

        $this->call->view('records', $data);
    }

    public function Inventory()
    {
        $page    = $this->current_page();
        $q       = trim($this->io->get('q') ?? '');
        $perPage = 10;

        $result = $this->BatchModel->paginate_with_items($q, $perPage, $page);
        $this->configure_pagination($result['total_rows'], $perPage, $page, '/inventory?q=' . urlencode($q));

        $data = [
            'batches'      => $result['records'],
            'pagination'   => $this->pagination->paginate(),
            'search_term'  => $q,
            'total_main'   => $this->BatchModel->total_by_location('main'),
            'total_reserve'=> $this->BatchModel->total_by_location('reserve'),
            'summary'      => $this->BatchModel->inventory_summary()
        ];

        $this->call->view('Inventory', $data);
    }
}