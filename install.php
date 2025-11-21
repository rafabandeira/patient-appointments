<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// 1. Tabela de Pacientes
if (!$CI->db->table_exists(db_prefix() . 'pat_patients')) {
    $charset = isset($CI->db->char_set) ? $CI->db->char_set : 'utf8';
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pat_patients` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `fullname` varchar(150) NOT NULL,
      `email` varchar(100) DEFAULT NULL,
      `phone` varchar(50) DEFAULT NULL,
      `birth_date` date DEFAULT NULL,
      `history` text DEFAULT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $charset . ';');
}

// 2. Tabela de Serviços
if (!$CI->db->table_exists(db_prefix() . 'pat_services')) {
    $charset = isset($CI->db->char_set) ? $CI->db->char_set : 'utf8';
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pat_services` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(150) NOT NULL,
      `duration_minutes` int(11) NOT NULL DEFAULT 30,
      `price` decimal(15,2) DEFAULT 0.00,
      `color` varchar(20) DEFAULT "#3b82f6",
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $charset . ';');
}

// 3. Tabela de Agendamentos
if (!$CI->db->table_exists(db_prefix() . 'pat_appointments')) {
    $charset = isset($CI->db->char_set) ? $CI->db->char_set : 'utf8';
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'pat_appointments` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `patient_id` int(11) NOT NULL,
      `service_id` int(11) NOT NULL,
      `staff_id` int(11) NOT NULL,
      `start_time` datetime NOT NULL,
      `end_time` datetime NOT NULL,
      `status` varchar(50) DEFAULT "confirmed",
      `notes` text,
      PRIMARY KEY (`id`),
      KEY `patient_id` (`patient_id`),
      KEY `service_id` (`service_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $charset . ';');
}


// 4. Criar Template de E-mail Padrão (se não existir)
// Template de E-mail
$CI->load->model('emails_model');

// Verifica se já existe
$CI->db->where('slug', 'patient-appointment-created');
$CI->db->where('language', 'portuguese');
$exists = $CI->db->count_all_results(db_prefix() . 'emailtemplates');

if (!$exists) {
    $CI->db->insert(db_prefix() . 'emailtemplates', [
        'type'     => 'patient_appointments',
        'slug'     => 'patient-appointment-created',
        'language' => 'portuguese',
        'name'     => 'Confirmação de Agendamento (Paciente)',
        'subject'  => 'Confirmação: Consulta agendada para {appointment_date}',
        'message'  => '<p>Olá {patient_name},</p><p>Sua consulta foi agendada com sucesso.</p><p><strong>Serviço:</strong> {service_name}<br /><strong>Data:</strong> {appointment_date}<br /><strong>Horário:</strong> {appointment_time}</p><p>Atenciosamente,<br />{email_signature}</p>',
        'fromname' => '{companyname}',
        'fromemail' => '{companyemail}',
        'plaintext' => 0,
        'active'   => 1,
        'order'    => 0,
    ]);
}

// Template em inglês
$CI->db->where('slug', 'patient-appointment-created');
$CI->db->where('language', 'english');
$exists_en = $CI->db->count_all_results(db_prefix() . 'emailtemplates');

if (!$exists_en) {
    $CI->db->insert(db_prefix() . 'emailtemplates', [
        'type'     => 'patient_appointments',
        'slug'     => 'patient-appointment-created',
        'language' => 'english',
        'name'     => 'Appointment Confirmation (Patient)',
        'subject'  => 'Confirmation: Appointment on {appointment_date}',
        'message'  => '<p>Hi {patient_name},</p><p>Your appointment is confirmed.</p><p><strong>Service:</strong> {service_name}<br /><strong>Date:</strong> {appointment_date}<br /><strong>Time:</strong> {appointment_time}</p><p>Regards,<br />{email_signature}</p>',
        'fromname' => '{companyname}',
        'fromemail' => '{companyemail}',
        'plaintext' => 0,
        'active'   => 1,
        'order'    => 0,
    ]);
}

// 5. Adicionar coluna de vínculo com Cliente Perfex
if (!$CI->db->field_exists('perfex_client_id', db_prefix() . 'pat_patients')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'pat_patients` ADD COLUMN `perfex_client_id` INT(11) DEFAULT NULL AFTER `id`;');
}