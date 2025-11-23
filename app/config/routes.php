<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @since Version 1
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
| -------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------
| Here is where you can register web routes for your application.
|
|
*/

// Public routes
$router->get('/', 'Control::Landing');
$router->get('/about', 'Control::About');
$router->get('/contact', 'Control::Contact');
$router->post('/send-message', 'Control::SendMessage');

// Authentication routes
$router->group('/auth', function() use ($router) {
    $router->match('/login', 'Control::Login', ['GET','POST']);
    $router->match('/signup', 'Control::Signup', ['GET','POST']);
    $router->post('/logout', 'Control::Logout');
    // verification and password flows
    $router->match('/verify', 'Control::Verify', ['GET','POST']);
    $router->match('/forgot', 'Control::Forgot', ['GET','POST']);
    $router->match('/reset_password', 'Control::ResetPassword', ['GET','POST']);
});

// Dashboard routes
$router->group('/dashboard', 'Control::Dashboard');

// Chart / API endpoints for dashboard
$router->get('/patients_chart', 'Control::PatientsChart');
$router->get('/patients_disease', 'Control::PatientsDisease');
$router->get('/patients_predict', 'Control::PatientsPredict');

// API routes
$router->group('/api', function() use ($router) {
    $router->get('/check-appointment-availability', 'Api::check_appointment_availability');
    $router->get('/available-slots', 'Api::available_slots');
});

// Patient routes
$router->group('/patients', function() use ($router) {
    $router->get('/', 'Control::Patients');
    $router->get('/create', 'Crud::patientsCreate');
    $router->post('/store', 'Crud::patientsStore');
    $router->get('/edit/{id}', 'Crud::patientsEdit')->where_number('id');
    $router->post('/update/{id}', 'Crud::patientsUpdate')->where_number('id');
    $router->post('/delete/{id}', 'Crud::patientsDelete')->where_number('id');
});

// Appointment routes
$router->group('/appointments', function() use ($router) {
    $router->get('/', 'Control::Appointments');
    $router->get('/create', 'Crud::appointmentsCreate');
    $router->post('/store', 'Crud::appointmentsStore');
    $router->get('/edit/{id}', 'Crud::appointmentsEdit')->where_number('id');
    $router->post('/update/{id}', 'Crud::appointmentsUpdate')->where_number('id');
    $router->post('/delete/{id}', 'Crud::appointmentsDelete')->where_number('id');
});

// Medication routes
$router->group('/medications', function() use ($router) {
    $router->get('/', 'Control::Medications');
    $router->get('/create', 'Crud::medicationsCreate');
    $router->post('/store', 'Crud::medicationsStore');
    $router->get('/edit/{id}', 'Crud::medicationsEdit')->where_number('id');
    $router->post('/update/{id}', 'Crud::medicationsUpdate')->where_number('id');
    $router->post('/delete/{id}', 'Crud::medicationsDelete')->where_number('id');
});

// Records routes
$router->group('/records', function() use ($router) {
    $router->get('/', 'Control::Records');
    $router->get('/create', 'Crud::recordsCreate');
    $router->post('/store', 'Crud::recordsStore');
    $router->get('/edit/{id}', 'Crud::recordsEdit')->where_number('id');
    $router->post('/update/{id}', 'Crud::recordsUpdate')->where_number('id');
    $router->post('/delete/{id}', 'Crud::recordsDelete')->where_number('id');
    $router->get('/view/{id}', 'Control::recordView')->where_number('id');
});

// Records PDF
$router->get('/records/export_pdf', 'RecordsController@export_pdf');

// Single patient record PDF
$router->get('/records/view/{id}/export_pdf', 'RecordsController@export_patient_pdf');

// Medications PDF
$router->get('/medications/export_pdf', 'MedicationsController@export_pdf');

// Inventory routes
$router->group('/inventory', function() use ($router) {
    $router->get('/', 'Control::Inventory');
    $router->get('/create', 'Crud::inventoryCreate');
    $router->post('/store', 'Crud::inventoryStore');
    $router->get('/edit/{id}', 'Crud::inventoryEdit')->where_number('id');
    $router->post('/update/{id}', 'Crud::inventoryUpdate')->where_number('id');
    $router->post('/delete/{id}', 'Crud::inventoryDelete')->where_number('id');
    $router->post('/refill/{item_id}', 'Crud::inventoryRefill')->where_number('item_id');
});

// User Profile routes
$router->group('/profile', function() use ($router) {
    $router->get('/', 'Profile::index');
    $router->get('/edit', 'Profile::edit');
    $router->post('/update', 'Profile::update');
    $router->post('/delete-account', 'Profile::deleteAccount');
    $router->get('/view/{id}', 'Profile::viewUser')->where_number('id');
});

// Admin routes
$router->group('/admin', function() use ($router) {
    $router->get('/users', 'Control::AdminUsers');
    $router->post('/users/delete/{id}', 'Control::AdminDeleteUser')->where_number('id');
});
