<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Patient_appointments extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('patient_appointments_model');
    }

    // View Principal (Calendário)
    public function index()
    {
        if (!has_permission('patient_appointments', '', 'view')) access_denied('Agendamento');
        
        $data['title'] = 'Agenda Médica';
        $data['patients'] = $this->patient_appointments_model->get_patients();
        $data['services'] = $this->patient_appointments_model->get_services();
        
        $this->load->view('manage', $data);
    }

    // JSON para o FullCalendar
    public function get_events()
    {
        $start = $this->input->get('start');
        $end   = $this->input->get('end');

        $appointments = $this->patient_appointments_model->get_appointments($start, $end);
        
        $events = [];
        foreach ($appointments as $appt) {
            $events[] = [
                'id'    => $appt['id'],
                'title' => $appt['patient_name'] . ' - ' . $appt['service_name'],
                'start' => $appt['start_time'],
                'end'   => $appt['end_time'],
                'color' => $appt['color'],
                'description' => $appt['notes']
            ];
        }

        echo json_encode($events);
        die();
    }

    // Salvar Agendamento via AJAX
    public function add_appointment_ajax()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        // Validação de permissão
        if (!has_permission('patient_appointments', '', 'create')) {
            echo json_encode(['status' => false, 'message' => 'Sem permissão']);
            die();
        }
        
        $data = $this->input->post();
        $result = $this->patient_appointments_model->add_appointment($data);
        
        echo json_encode($result);
        die();
    }

    // CRUD Pacientes
    public function patients()
    {
        if ($this->input->post()) {
            $this->patient_appointments_model->add_patient($this->input->post());
            set_alert('success', 'Paciente adicionado!');
            redirect(admin_url('patient_appointments/patients'));
        }
        $data['patients'] = $this->patient_appointments_model->get_patients();
        $data['title'] = 'Pacientes';
        $this->load->view('patients', $data);
    }

    // CRUD Serviços
    public function services()
    {
         if ($this->input->post()) {
            $this->patient_appointments_model->add_service($this->input->post());
            set_alert('success', 'Serviço criado!');
            redirect(admin_url('patient_appointments/services'));
        }
        $data['services'] = $this->patient_appointments_model->get_services();
        $data['title'] = 'Serviços e Procedimentos';
        $this->load->view('services', $data);
    }

    // Adicione este método dentro da classe Patient_appointments
    public function view_patient($id)
    {
        if (!has_permission('patient_appointments', '', 'view')) access_denied('Ver Paciente');

        // Carrega dados do paciente
        $patient = $this->patient_appointments_model->get_patient($id);

        if (!$patient) {
            show_404();
        }

        $data['patient'] = $patient;
        // Carrega o histórico de agendamentos deste paciente
        $data['history'] = $this->patient_appointments_model->get_patient_history($id);
        
        $data['title']   = $patient->fullname;
        $this->load->view('patient_profile', $data);
    }

    // Cria a fatura a partir do agendamento
    public function create_invoice($appointment_id)
    {
        if (!has_permission('invoices', '', 'create')) access_denied('Criar Fatura');

        $invoice_id = $this->patient_appointments_model->generate_invoice($appointment_id);

        if ($invoice_id) {
            set_alert('success', 'Fatura gerada com sucesso! Você pode adicionar mais detalhes agora.');
            // Redireciona para a tela de EDIÇÃO da fatura nativa do Perfex
            redirect(admin_url('invoices/invoice/' . $invoice_id));
        } else {
            set_alert('danger', 'Erro ao gerar fatura. Verifique se o paciente possui e-mail válido.');
            redirect(admin_url('patient_appointments'));
        }
    }

    // Atualiza o prontuário do paciente
    public function update_history($id)
    {
        if ($this->input->post()) {
            $history = $this->input->post('history');
            $this->db->where('id', $id);
            $this->db->update(db_prefix().'pat_patients', ['history' => $history]);
            set_alert('success', 'Prontuário atualizado com sucesso.');
        }
        redirect(admin_url('patient_appointments/view_patient/' . $id));
    }

}