<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="text-center">
                            <div class="mbottom20">
                                <img src="<?php echo base_url('assets/images/user-placeholder.jpg'); ?>" class="img-circle img-thumbnail" style="width: 100px; height: 100px;">
                            </div>
                            <h3 class="bold no-margin"><?php echo $patient->fullname; ?></h3>
                            <span class="label label-info mtop10 display-inline-block">Paciente</span>
                        </div>
                        
                        <hr />
                        
                        <h4 class="bold font-medium">Dados de Contato</h4>
                        <address>
                            <ul class="list-unstyled fa-ul">
                                <li>
                                    <i class="fa fa-envelope fa-li" aria-hidden="true"></i>
                                    <a href="mailto:<?php echo $patient->email; ?>"><?php echo $patient->email; ?></a>
                                </li>
                                <li class="mtop10">
                                    <i class="fa fa-phone fa-li" aria-hidden="true"></i>
                                    <?php echo $patient->phone; ?>
                                </li>
                                <li class="mtop10">
                                    <i class="fa fa-birthday-cake fa-li" aria-hidden="true"></i>
                                    <?php echo _d($patient->birth_date); ?>
                                </li>
                            </ul>
                        </address>

                        <?php if(!empty($patient->perfex_client_id)): ?>
                            <div class="alert alert-success mtop20">
                                <i class="fa fa-check"></i> Este paciente é um Cliente Financeiro.
                                <a href="<?php echo admin_url('clients/client/'.$patient->perfex_client_id); ?>" class="alert-link pull-right" target="_blank">Ver Perfil Financeiro</a>
                            </div>
                        <?php endif; ?>

                        <hr />
                        <a href="<?php echo admin_url('patient_appointments/patients'); ?>" class="btn btn-default btn-block">
                            <i class="fa fa-arrow-left"></i> Voltar para Lista
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-heading">
                        <span class="font-medium text-uppercase">Prontuário Eletrônico</span>
                    </div>
                    <div class="panel-body">
                        
                        <div class="horizontal-scrollable-tabs">
                            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                            <div class="horizontal-tabs">
                                <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                                    <li role="presentation" class="active">
                                        <a href="#history" aria-controls="history" role="tab" data-toggle="tab">
                                            <i class="fa fa-history menu-icon"></i> Histórico de Atendimentos
                                        </a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#medical_notes" aria-controls="medical_notes" role="tab" data-toggle="tab">
                                            <i class="fa fa-user-md menu-icon"></i> Anamnese / Notas
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="tab-content mtop20">
                            
                            <div role="tabpanel" class="tab-pane active" id="history">
                                <div class="table-responsive">
                                    <table class="table dt-table" data-order-col="0" data-order-type="desc">
                                        <thead>
                                            <tr>
                                                <th>Data / Hora</th>
                                                <th>Procedimento</th>
                                                <th>Profissional</th>
                                                <th>Valor</th>
                                                <th>Status</th>
                                                <th class="text-right">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($history as $h): 
                                                // Lógica de visualização
                                                $is_future = strtotime($h['start_time']) > time();
                                                $status_label = $is_future ? 'label-primary' : 'label-success';
                                                $status_text  = $is_future ? 'Agendado' : 'Realizado';
                                            ?>
                                            <tr>
                                                <td data-order="<?php echo $h['start_time']; ?>">
                                                    <span class="bold"><?php echo _d(date('Y-m-d', strtotime($h['start_time']))); ?></span><br>
                                                    <small class="text-muted">
                                                        <?php echo date('H:i', strtotime($h['start_time'])); ?> às <?php echo date('H:i', strtotime($h['end_time'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php echo $h['service_name']; ?><br>
                                                    <small class="text-muted"><?php echo $h['notes']; ?></small>
                                                </td>
                                                <td>
                                                    <?php echo $h['staff_name']; ?>
                                                </td>
                                                <td>
                                                    <?php echo app_format_money($h['price'], 'BRL'); ?>
                                                </td>
                                                <td>
                                                    <span class="label <?php echo $status_label; ?>"><?php echo $status_text; ?></span>
                                                </td>
                                                <td class="text-right">
                                                    <?php if(has_permission('invoices', '', 'create')): ?>
                                                        <a href="<?php echo admin_url('patient_appointments/create_invoice/' . $h['id']); ?>" 
                                                           class="btn btn-success btn-xs _delete"
                                                           data-toggle="tooltip" 
                                                           data-title="Gerar Fatura (Invoice)"
                                                           onclick="return confirm('Deseja gerar uma fatura para este atendimento? O paciente será cadastrado como Cliente se ainda não for.');">
                                                            <i class="fa fa-money"></i> Faturar
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="medical_notes">
                                <div class="alert alert-warning">
                                    <i class="fa fa-info-circle"></i> Estas informações são internas e visíveis apenas para a equipe médica/administrativa.
                                </div>
                                
                                <?php echo form_open(admin_url('patient_appointments/update_history/'.$patient->id)); ?>
                                    <div class="form-group">
                                        <label for="history" class="control-label">Histórico Clínico Completo / Alergias / Observações</label>
                                        <textarea id="history" name="history" class="form-control" rows="15"><?php echo $patient->history; ?></textarea>
                                        </div>
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-save"></i> Salvar Prontuário
                                        </button>
                                    </div>
                                <?php echo form_close(); ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>