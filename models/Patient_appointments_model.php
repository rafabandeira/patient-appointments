<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Patient_appointments_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_appointments($start, $end)
    {
        $this->db->select('a.*, p.fullname as patient_name, s.name as service_name, s.color');
        $this->db->from(db_prefix() . 'pat_appointments a');
        $this->db->join(db_prefix() . 'pat_patients p', 'p.id = a.patient_id', 'left');
        $this->db->join(db_prefix() . 'pat_services s', 's.id = a.service_id', 'left');
        $this->db->where('start_time >=', $start);
        $this->db->where('start_time <=', $end);
        return $this->db->get()->result_array();
    }

    public function add_appointment($data)
    {
        // (Código anterior de cálculo de tempo...)
        $service = $this->get_service($data['service_id']);
        
        if (!$service) {
            return ['status' => false, 'message' => 'Serviço não encontrado.'];
        }

        $duration = $service->duration_minutes;
        $start_dt = new DateTime($data['start_time']);
        $end_dt   = clone $start_dt;
        $end_dt->modify("+{$duration} minutes");
        $data['end_time'] = $end_dt->format('Y-m-d H:i:s');
        $data['staff_id'] = get_staff_user_id();

        // Inserção
        $this->db->insert(db_prefix() . 'pat_appointments', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            // NOVA PARTE: Enviar E-mail
            $this->send_confirmation_email($insert_id);
            
            return ['status' => true, 'id' => $insert_id];
        }
        return ['status' => false, 'message' => 'Erro ao inserir no banco'];
    }

    // Método auxiliar para preparar e enviar o e-mail
    public function send_confirmation_email($appointment_id)
    {
        $this->load->model('emails_model');

        // 1. Buscar dados completos (JOINs) para preencher as variáveis
        $this->db->select('a.*, p.fullname as patient_name, p.email as patient_email, s.name as service_name, CONCAT(st.firstname, " ", st.lastname) as staff_name');
        $this->db->from(db_prefix() . 'pat_appointments a');
        $this->db->join(db_prefix() . 'pat_patients p', 'p.id = a.patient_id', 'left');
        $this->db->join(db_prefix() . 'pat_services s', 's.id = a.service_id', 'left');
        $this->db->join(db_prefix() . 'staff st', 'st.staffid = a.staff_id', 'left');
        $this->db->where('a.id', $appointment_id);
        $appointment = $this->db->get()->row();

        if ($appointment && !empty($appointment->patient_email)) {
            
            // 2. Enviar usando o sistema nativo
            // Passamos o objeto $appointment dentro de um array para ser pego no Hook
            $data['appointment'] = $appointment;

            return $this->emails_model->send_simple_email(
                $appointment->patient_email, 
                'Confirmação de Agendamento', // Fallback subject
                '', // Mensagem vazia pois o template vai substituir
                'patient-appointment-created', // Slug do template
                $data // Dados para o hook
            );
        }
        return false;
    }

    public function check_conflict($start, $end, $staff_id)
    {
        $this->db->where('staff_id', $staff_id);
        $this->db->group_start();
            $this->db->where("start_time < '$end' AND end_time > '$start'");
        $this->db->group_end();
        return $this->db->count_all_results(db_prefix() . 'pat_appointments') > 0;
    }

    // CRUD Básico de Serviços e Pacientes
    public function get_service($id) {
        return $this->db->get_where(db_prefix().'pat_services', ['id'=>$id])->row();
    }
    
    public function get_services() {
        return $this->db->get(db_prefix().'pat_services')->result_array();
    }

    public function get_patients() {
        return $this->db->get(db_prefix().'pat_patients')->result_array();
    }
    
    public function add_patient($data) {
        $this->db->insert(db_prefix().'pat_patients', $data);
        return $this->db->insert_id();
    }
    
    public function add_service($data) {
        $this->db->insert(db_prefix().'pat_services', $data);
        return $this->db->insert_id();
    }

    // Adicione estes métodos na classe Patient_appointments_model
    public function get_patient($id) {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'pat_patients')->row();
    }

    public function get_patient_history($patient_id) {
        // Seleciona agendamento + nome do serviço + nome do médico (Staff)
        $this->db->select('a.*, s.name as service_name, s.price, CONCAT(st.firstname, " ", st.lastname) as staff_name');
        $this->db->from(db_prefix() . 'pat_appointments a');
        $this->db->join(db_prefix() . 'pat_services s', 's.id = a.service_id', 'left');
        $this->db->join(db_prefix() . 'staff st', 'st.staffid = a.staff_id', 'left');
        
        $this->db->where('a.patient_id', $patient_id);
        $this->db->order_by('a.start_time', 'DESC'); // Mais recentes primeiro
        
        return $this->db->get()->result_array();
    }

    /**
     * Garante que o paciente existe como Cliente no Perfex
     */
    public function ensure_patient_is_client($patient_id) {
        $patient = $this->get_patient($patient_id);
        
        // Se já tem ID vinculado, retorna ele
        if (!empty($patient->perfex_client_id)) {
            return $patient->perfex_client_id;
        }

        // Se não, vamos criar o Cliente (Company)
        $this->load->model('clients_model');
        
        $client_data = [
            'company'     => $patient->fullname,
            'phonenumber' => $patient->phone,
            // Campos obrigatórios padrão do Perfex
            'billing_street' => '', 
            'currency' => $this->get_default_currency_id(),
        ];

        // Inserir Cliente
        $client_id = $this->clients_model->add($client_data);

        if ($client_id) {
            // Inserir Contato Principal (Necessário para enviar fatura por e-mail)
            $contact_data = [
                'firstname' => explode(' ', $patient->fullname)[0],
                'lastname'  => substr(strstr($patient->fullname, ' '), 1), // Sobrenome
                'email'     => $patient->email,
                'phonenumber' => $patient->phone,
                'is_primary' => 1,
            ];
            
            // Adiciona contato vinculado ao cliente criado
            $this->clients_model->add_contact($contact_data, $client_id);

            // Atualiza nossa tabela de pacientes com o ID novo
            $this->db->where('id', $patient->id);
            $this->db->update(db_prefix() . 'pat_patients', ['perfex_client_id' => $client_id]);

            return $client_id;
        }

        return false;
    }

    /**
     * Gera Fatura a partir do Agendamento
     */
    public function generate_invoice($appointment_id) {
        $this->load->model('invoices_model');

        // 1. Pegar dados do agendamento
        $this->db->select('a.*, s.name as service_name, s.price, s.id as service_id');
        $this->db->from(db_prefix() . 'pat_appointments a');
        $this->db->join(db_prefix() . 'pat_services s', 's.id = a.service_id', 'left');
        $this->db->where('a.id', $appointment_id);
        $appointment = $this->db->get()->row();

        if (!$appointment) return false;

        // 2. Garantir que é Cliente
        $clientid = $this->ensure_patient_is_client($appointment->patient_id);
        if (!$clientid) return false;

        // 3. Montar dados da Fatura
        // No Perfex, itens são passados no array 'newitems'
        $newitems = [];
        $newitems[1] = [ // O índice pode ser arbitrário na criação
            'description'      => $appointment->service_name,
            'long_description' => 'Atendimento realizado em ' . _d($appointment->start_time),
            'qty'              => 1,
            'rate'             => $appointment->price,
            'unit'             => 'Unid',
            'order'            => 1
        ];

        $invoice_data = [
            'clientid'            => $clientid,
            'number'              => get_option('next_invoice_number'),
            'date'                => date('Y-m-d'),
            'duedate'             => date('Y-m-d'), // Vencimento hoje
            'currency'            => $this->get_default_currency_id(),
            'newitems'            => $newitems,
            'subtotal'            => $appointment->price,
            'total'               => $appointment->price,
            'billing_street'      => '', // Pega do cliente automaticamente se vazio, mas precisa passar a chave
            'show_quantity_as'    => 1,
        ];

        // 4. Criar Fatura
        $invoice_id = $this->invoices_model->add($invoice_data);

        if ($invoice_id) {
            return $invoice_id;
        }

        return false;
    }

    private function get_default_currency_id()
    {
        $currency = $this->db->get_where(db_prefix().'currencies', ['isdefault'=>1])->row();
        if ($currency) {
            return $currency->id;
        }
        // Fallback: return the first currency found or 0 (which might still fail validation but avoids 500)
        $first = $this->db->get(db_prefix().'currencies')->row();
        return $first ? $first->id : 0;
    }

}