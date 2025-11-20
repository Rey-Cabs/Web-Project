<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Crud extends Controller
{
    private $contexts = [
        'patients' => [
            'redirect'  => '/patients',
            'store'     => 'patients/store',
            'update'    => 'patients/update/{id}',
            'titles'    => ['create' => 'Add Patient', 'edit' => 'Edit Patient'],
            'messages'  => [
                'created' => 'Patient record added successfully.',
                'updated' => 'Patient record updated successfully.',
                'deleted' => 'Patient record removed.'
            ],
            'defaults'  => ['type' => 'Check-up', 'status' => 'Pending']
        ],
        'appointments' => [
            'redirect'  => '/appointments',
            'store'     => 'appointments/store',
            'update'    => 'appointments/update/{id}',
            'titles'    => ['create' => 'Schedule Appointment', 'edit' => 'Update Appointment'],
            'messages'  => [
                'created' => 'Appointment scheduled successfully.',
                'updated' => 'Appointment updated successfully.',
                'deleted' => 'Appointment removed.'
            ],
            'defaults'     => ['status' => 'Pending'],
            'requirements' => ['schedule' => true]
        ],
        'medications' => [
            'redirect'  => '/medications',
            'store'     => 'medications/store',
            'update'    => 'medications/update/{id}',
            'titles'    => ['create' => 'Add Medication Plan', 'edit' => 'Update Medication Plan'],
            'messages'  => [
                'created' => 'Medication plan added successfully.',
                'updated' => 'Medication plan updated successfully.',
                'deleted' => 'Medication plan removed.'
            ],
            'defaults'     => ['type' => 'Prescription', 'status' => 'Ongoing'],
            'requirements' => ['medicine' => true]
        ],
        'records' => [
            'redirect'  => '/records',
            'store'     => 'records/store',
            'update'    => 'records/update/{id}',
            'titles'    => ['create' => 'Add Health Record', 'edit' => 'Update Health Record'],
            'messages'  => [
                'created' => 'Record added successfully.',
                'updated' => 'Record updated successfully.',
                'deleted' => 'Record removed.'
            ],
            'defaults'  => ['status' => 'Pending']
        ]
    ];

    private $types = ['Check-up','Home Visit','Prescription','Follow-up'];
    private $statuses = ['Ongoing','Pending','Ended','Cancelled'];

    public function __construct()
    {
        parent::__construct();
        $this->call->model('PatientModel');
        $this->call->model('UserModel');
        $this->call->model('BatchModel');
        $this->call->model('ItemModel');
    }

    private function context(string $key): array
    {
        if (!isset($this->contexts[$key])) {
            show_404('404 Not Found', 'Unknown context requested.');
        }
        return $this->contexts[$key];
    }

    private function ensure_post()
    {
        if ($this->io->method(true) !== 'POST') {
            show_404();
        }
    }

    private function render_patient_form(string $context, ?int $id = null)
    {
        $config   = $this->context($context);
        $patient  = [];
        $mode     = is_null($id) ? 'create' : 'edit';

        if (!is_null($id)) {
            $patient = $this->PatientModel->find_patient($id);
            if (!$patient) {
                show_404('404 Not Found', 'Record not found.');
            }
        }

        $action = $mode === 'create'
            ? site_url($config['store'])
            : site_url(str_replace('{id}', $id, $config['update']));

        $allUsers = $this->UserModel->all_users();
        $filteredUsers = [];
        foreach ($allUsers as $u) {
            if (isset($u['role']) && $u['role'] === 'admin') continue;
            $filteredUsers[] = $u;
        }

        $data = [
            'title'    => $config['titles'][$mode],
            'action'   => $action,
            'context'  => $context,
            'patient'  => $patient,
            'users'    => $filteredUsers,
            'types'    => $this->types,
            'statuses' => $this->statuses,
            'mode'     => $mode
        ];

        $this->call->view('management/patient_form', $data);
    }

    private function collect_patient_payload(string $context, string $redirect_path): array
    {
        $config = $this->context($context);

        $user_id    = (int) ($this->io->post('user_id') ?? 0);
        $first_name = trim($this->io->post('first_name') ?? '');
        $last_name  = trim($this->io->post('last_name') ?? '');
        $age        = (int) ($this->io->post('age') ?? 0);
        $email      = trim($this->io->post('email') ?? '');
        $address    = trim($this->io->post('address') ?? '');
        $disease    = trim($this->io->post('disease') ?? '');
        $type       = trim($this->io->post('type') ?? '');
        $medicine   = trim($this->io->post('medicine') ?? '');
        $duration   = trim($this->io->post('duration') ?? '');
        $status     = trim($this->io->post('status') ?? '');
        $schedule   = trim($this->io->post('schedule') ?? '');

        if ($user_id <= 0 || !$this->UserModel->find($user_id)) {
            set_flash_alert('danger', 'Please select a valid user.');
            redirect($redirect_path);
            exit;
        }

        if ($first_name === '' || $last_name === '' || $age <= 0) {
            set_flash_alert('danger', 'First name, last name, and age are required.');
            redirect($redirect_path);
            exit;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash_alert('danger', 'Please provide a valid email address.');
            redirect($redirect_path);
            exit;
        }

        $data = [
            'user_id'   => $user_id,
            'first_name'=> $first_name,
            'last_name' => $last_name,
            'age'       => $age,
            'email'     => $email !== '' ? $email : null,
            'address'   => $address !== '' ? $address : null,
            'disease'   => $disease !== '' ? $disease : null,
            'medicine'  => $medicine !== '' ? $medicine : null,
            'duration'  => $duration !== '' ? $duration : null,
        ];

        $scheduleDate = null;
        if ($schedule !== '') {
            $parsed = date_create($schedule);
            if (!$parsed) {
                set_flash_alert('danger', 'Please provide a valid schedule date.');
                redirect($redirect_path);
                exit;
            }
            $scheduleDate = $parsed->format('Y-m-d');
        }
        $data['schedule'] = $scheduleDate;

        if (!in_array($type, $this->types, true)) {
            $type = $config['defaults']['type'] ?? 'Check-up';
        }
        $data['type'] = $type;

        if (!in_array($status, $this->statuses, true)) {
            $status = $config['defaults']['status'] ?? 'Pending';
        }
        $data['status'] = $status;

        $requirements = $config['requirements'] ?? [];
        if (!empty($requirements['schedule']) && empty($data['schedule'])) {
            set_flash_alert('danger', 'Schedule is required for appointments.');
            redirect($redirect_path);
            exit;
        }
        if (!empty($requirements['medicine']) && $data['medicine'] === null) {
            set_flash_alert('danger', 'Medicine details are required for this form.');
            redirect($redirect_path);
            exit;
        }

        return $data;
    }

    private function save_patient(string $context, ?int $id = null)
    {
        $config        = $this->context($context);
        $redirect_form = $config['redirect'] . (is_null($id) ? '/create' : '/edit/' . $id);
        $payload       = $this->collect_patient_payload($context, $redirect_form);

        if (is_null($id)) {
            $this->PatientModel->insert($payload);
            set_flash_alert('success', $config['messages']['created']);
        } else {
            $existing = $this->PatientModel->find_patient($id);
            if (!$existing) {
                show_404('404 Not Found', 'Record not found.');
            }
            $this->PatientModel->update($id, $payload);
            set_flash_alert('success', $config['messages']['updated']);
        }

        redirect($config['redirect']);
        exit;
    }

    private function delete_patient(string $context, int $id)
    {
        $config = $this->context($context);
        $record = $this->PatientModel->find_patient($id);
        if (!$record) {
            set_flash_alert('danger', 'Record not found.');
            redirect($config['redirect']);
            exit;
        }

        $this->PatientModel->delete($id);
        set_flash_alert('success', $config['messages']['deleted']);
        redirect($config['redirect']);
        exit;
    }

    public function patientsCreate()
    {
        $this->render_patient_form('patients');
    }

    public function patientsStore()
    {
        $this->ensure_post();
        $this->save_patient('patients');
    }

    public function patientsEdit($id)
    {
        $this->render_patient_form('patients', (int) $id);
    }

    public function patientsUpdate($id)
    {
        $this->ensure_post();
        $this->save_patient('patients', (int) $id);
    }

    public function patientsDelete($id)
    {
        $this->ensure_post();
        $this->delete_patient('patients', (int) $id);
    }

    public function appointmentsCreate()
    {
        $this->render_patient_form('appointments');
    }

    public function appointmentsStore()
    {
        $this->ensure_post();
        $this->save_patient('appointments');
    }

    public function appointmentsEdit($id)
    {
        $this->render_patient_form('appointments', (int) $id);
    }

    public function appointmentsUpdate($id)
    {
        $this->ensure_post();
        $this->save_patient('appointments', (int) $id);
    }

    public function appointmentsDelete($id)
    {
        $this->ensure_post();
        $this->delete_patient('appointments', (int) $id);
    }

    public function medicationsCreate()
    {
        $this->render_patient_form('medications');
    }

    public function medicationsStore()
    {
        $this->ensure_post();
        $this->save_patient('medications');
    }

    public function medicationsEdit($id)
    {
        $this->render_patient_form('medications', (int) $id);
    }

    public function medicationsUpdate($id)
    {
        $this->ensure_post();
        $this->save_patient('medications', (int) $id);
    }

    public function medicationsDelete($id)
    {
        $this->ensure_post();
        $this->delete_patient('medications', (int) $id);
    }

    public function recordsCreate()
    {
        $this->render_patient_form('records');
    }

    public function recordsStore()
    {
        $this->ensure_post();
        $this->save_patient('records');
    }

    public function recordsEdit($id)
    {
        $this->render_patient_form('records', (int) $id);
    }

    public function recordsUpdate($id)
    {
        $this->ensure_post();
        $this->save_patient('records', (int) $id);
    }

    public function recordsDelete($id)
    {
        $this->ensure_post();
        $this->delete_patient('records', (int) $id);
    }

    private function render_inventory_form(?int $id = null)
    {
        $batch = [];
        $mode  = is_null($id) ? 'create' : 'edit';

        if (!is_null($id)) {
            $batch = $this->BatchModel->find((int) $id);
            if (!$batch) {
                show_404('404 Not Found', 'Batch not found.');
            }
            $item = $this->ItemModel->find((int) $batch['item_id']);
            $batch['item_name'] = $item['item_name'] ?? '';
            $batch['category']  = $item['category'] ?? 'Medicine';
            $batch['unit']      = $item['unit'] ?? 'pcs';
            $batch['critical_level'] = $item['critical_level'] ?? 10;
            $batch['item_description'] = $item['item_description'] ?? '';
            foreach (['manufacture_date','expiry_date','received_date'] as $dateField) {
                if (!empty($batch[$dateField])) {
                    $batch[$dateField] = substr($batch[$dateField], 0, 10);
                }
            }
        }

        $action = $mode === 'create'
            ? site_url('inventory/store')
            : site_url('inventory/update/' . $id);

        $data = [
            'title'   => $mode === 'create' ? 'Add Inventory Batch' : 'Update Inventory Batch',
            'action'  => $action,
            'mode'    => $mode,
            'batch'   => $batch,
            'items'   => $this->ItemModel->all_items()
        ];

        $this->call->view('management/inventory_form', $data);
    }

    private function collect_inventory_payload(string $redirect_path): array
    {
        $item_id          = (int) ($this->io->post('item_id') ?? 0);
        $new_item_name    = trim($this->io->post('new_item_name') ?? '');
        $new_item_desc    = trim($this->io->post('new_item_description') ?? '');
        $category         = trim($this->io->post('category') ?? 'Medicine');
        $unit             = trim($this->io->post('unit') ?? 'pcs');
        $critical_level   = (int) ($this->io->post('critical_level') ?? 10);
        $batch_code       = trim($this->io->post('batch_code') ?? '');
        $quantity         = (int) ($this->io->post('quantity') ?? 0);
        $remaining        = $this->io->post('remaining_quantity');
        $location         = trim($this->io->post('location') ?? 'main');
        $manufacture_date = trim($this->io->post('manufacture_date') ?? '');
        $expiry_date      = trim($this->io->post('expiry_date') ?? '');
        $received_date    = trim($this->io->post('received_date') ?? '');

        if ($batch_code === '' || $quantity <= 0) {
            set_flash_alert('danger', 'Batch code and quantity are required.');
            redirect($redirect_path);
            exit;
        }

        if ($item_id <= 0 && $new_item_name === '') {
            set_flash_alert('danger', 'Select an existing item or provide a new item name.');
            redirect($redirect_path);
            exit;
        }

        if ($item_id > 0) {
            $item = $this->ItemModel->find($item_id);
            if (!$item) {
                set_flash_alert('danger', 'Selected item does not exist.');
                redirect($redirect_path);
                exit;
            }
        } else {
            $item_id = $this->ItemModel->insert([
                'item_name'        => $new_item_name,
                'item_description' => $new_item_desc !== '' ? $new_item_desc : null,
                'category'         => in_array($category, ['Medicine','Equipment','Supply','Other'], true) ? $category : 'Medicine',
                'unit'             => $unit !== '' ? $unit : 'pcs',
                'critical_level'   => $critical_level > 0 ? $critical_level : 10
            ]);
        }

        $remaining_quantity = is_numeric($remaining) ? (int) $remaining : $quantity;
        if ($remaining_quantity < 0) {
            $remaining_quantity = 0;
        }

        $formatDate = function (?string $date) use ($redirect_path) {
            if (empty($date)) {
                return null;
            }
            $parsed = date_create($date);
            if (!$parsed) {
                set_flash_alert('danger', 'Invalid date supplied.');
                redirect($redirect_path);
                exit;
            }
            return $parsed->format('Y-m-d');
        };

        return [
            'item_id'            => $item_id,
            'batch_code'         => $batch_code,
            'quantity'           => $quantity,
            'remaining_quantity' => $remaining_quantity,
            'location'           => in_array($location, ['main','reserve'], true) ? $location : 'main',
            'manufacture_date'   => $formatDate($manufacture_date),
            'expiry_date'        => $formatDate($expiry_date),
            'received_date'      => $formatDate($received_date) ?? date('Y-m-d')
        ];
    }

    public function inventoryCreate()
    {
        $this->render_inventory_form();
    }

    public function inventoryStore()
    {
        $this->ensure_post();
        $payload = $this->collect_inventory_payload('/inventory/create');
        $this->BatchModel->insert($payload);
        set_flash_alert('success', 'Inventory batch added successfully.');
        redirect('/inventory');
        exit;
    }

    public function inventoryEdit($id)
    {
        $this->render_inventory_form((int) $id);
    }

    public function inventoryUpdate($id)
    {
        $this->ensure_post();
        $record = $this->BatchModel->find((int) $id);
        if (!$record) {
            set_flash_alert('danger', 'Batch not found.');
            redirect('/inventory');
        }
        $redirect = '/inventory/edit/' . (int) $id;
        $payload  = $this->collect_inventory_payload($redirect);
        $this->BatchModel->update((int) $id, $payload);
        set_flash_alert('success', 'Inventory batch updated successfully.');
        redirect('/inventory');
        exit;
    }

    public function inventoryDelete($id)
    {
        $this->ensure_post();
        $record = $this->BatchModel->find((int) $id);
        if (!$record) {
            set_flash_alert('danger', 'Batch not found.');
            redirect('/inventory');
        }

        $this->BatchModel->delete((int) $id);
        set_flash_alert('success', 'Batch removed.');
        redirect('/inventory');
        exit;
    }
}

