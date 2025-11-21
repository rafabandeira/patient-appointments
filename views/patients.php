<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <?php if(has_permission('patient_appointments', '', 'create')){ ?>
                                <a href="#" class="btn btn-info mright5 test pull-left display-block" data-toggle="modal" data-target="#new_patient_modal">
                                    <i class="fa fa-plus"></i> Novo Paciente
                                </a>
                            <?php } ?>
                            <div class="clearfix"></div>
                        </div>
                        
                        <hr class="hr-panel-heading" />
                        
                        <div class="clearfix"></div>

                        <table class="table dt-table" data-order-col="0" data-order-type="desc">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome Completo</th>
                                    <th>E-mail</th>
                                    <th>Telefone</th>
                                    <th>Nascimento</th>
                                    <th>Criado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($patients as $p): ?>
                                <tr>
                                    <td><?php echo $p['id']; ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('patient_appointments/view_patient/' . $p['id']); ?>" class="font-medium">
                                            <?php echo $p['fullname']; ?>
                                        </a>
                                        <?php if(!empty($p['perfex_client_id'])): ?>
                                            <span class="text-muted" data-toggle="tooltip" title="Vinculado ao Cliente #<?php echo $p['perfex_client_id']; ?>"><i class="fa fa-link"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><a href="mailto:<?php echo $p['email']; ?>"><?php echo $p['email']; ?></a></td>
                                    <td><?php echo $p['phone']; ?></td>
                                    <td><?php echo _d($p['birth_date']); ?></td>
                                    <td><?php echo _dt($p['created_at']); ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('patient_appointments/view_patient/' . $p['id']); ?>" class="btn btn-default btn-icon">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="new_patient_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <?php echo form_open(admin_url('patient_appointments/patients')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Cadastrar Novo Paciente</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('fullname', 'Nome Completo', '', 'text', ['required'=>'true']); ?>
                        <?php echo render_input('email', 'E-mail', '', 'email'); ?>
                        <?php echo render_input('phone', 'Telefone/Celular'); ?>
                        <?php echo render_date_input('birth_date', 'Data de Nascimento'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-primary">Salvar Paciente</button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php init_tail(); ?>
</body>
</html>