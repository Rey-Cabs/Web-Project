<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Controller: Auth
 *
 * Thin proxy that exposes /auth/* routes and delegates to the primary
 * authentication methods implemented on the Control controller. This keeps
 * friendly URLs like /auth/login working while reusing the existing logic.
 */
class Auth extends Control
{
    public function __construct()
    {
        // Control constructor sets up models and helpers
        parent::__construct();
    }

    // URI: /auth/login
    public function login()
    {
        return $this->Login();
    }

    // URI: /auth/signup
    public function signup()
    {
        return $this->Signup();
    }

    // URI: /auth/verify
    public function verify()
    {
        return $this->Verify();
    }

    // URI: /auth/logout
    public function logout()
    {
        return $this->Logout();
    }

    // URI: /auth/forgot
    public function forgot()
    {
        return $this->Forgot();
    }

    // URI: /auth/reset_password
    public function reset_password()
    {
        return $this->ResetPassword();
    }
}
