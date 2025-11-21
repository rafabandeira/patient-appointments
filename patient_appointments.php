<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Agendamento de Pacientes
Description: Sistema completo de gestão de pacientes, serviços e agenda médica.
Version: 1.0.0
Requires at least: 2.3.*
Author: Dev Perfex
*/

if (!defined('PATIENT_APPOINTMENTS_MODULE_NAME')) {
    define('PATIENT_APPOINTMENTS_MODULE_NAME', 'patient_appointments');
}

hooks()->add_action('admin_init', 'patient_appointments_init_menu_items');
hooks()->add_action('admin_init', 'patient_appointments_permissions');
hooks()->add_filter('other_merge_fields_available_for', 'patient_appointments_register_merge_fields');
hooks()->add_action('after_parse_email_template_message', 'patient_appointments_parse_email');

/**
 * Registra o menu lateral
 */
function patient_appointments_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('patient_appointments', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('patient_appointments', [
            'name'     => 'Clínica',
            'icon'     => 'fa fa-user-md', 
            'position' => 60,
        ]);

        $CI->app_menu->add_sidebar_children_item('patient_appointments', [
            'slug'     => 'pa-calendar',
            'name'     => 'Agenda',
            'href'     => admin_url('patient_appointments'),
            'position' => 5,
        ]);

        $CI->app_menu->add_sidebar_children_item('patient_appointments', [
            'slug'     => 'pa-patients',
            'name'     => 'Pacientes',
            'href'     => admin_url('patient_appointments/patients'),
            'position' => 10,
        ]);

        $CI->app_menu->add_sidebar_children_item('patient_appointments', [
            'slug'     => 'pa-services',
            'name'     => 'Serviços & Procedimentos',
            'href'     => admin_url('patient_appointments/services'),
            'position' => 15,
        ]);
    }
}

/**
 * Registra Permissões
 */
function patient_appointments_permissions()
{
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capability('patient_appointments', $capabilities, 'Agendamento Clínica');
}

// Função para mostrar os campos disponíveis na tela de edição de template
function patient_appointments_register_merge_fields($for)
{
    $for[] = 'patient_appointments';
    return $for;
}

// Função que realiza a troca real {variavel} -> Valor
function patient_appointments_parse_email($data)
{
    // Verifica se o template é do nosso módulo
    if (isset($data['template']) && $data['template']->type == 'patient_appointments') {
        
        // Acessa os merge fields passados
        if (isset($data['template']->merge_fields) && is_array($data['template']->merge_fields)) {
            foreach ($data['template']->merge_fields as $key => $val) {
                $data['message'] = str_replace($key, $val, $data['message']);
                if (isset($data['subject'])) {
                    $data['subject'] = str_replace($key, $val, $data['subject']);
                }
            }
        }
    }
    return $data;
}