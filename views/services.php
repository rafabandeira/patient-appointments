<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                         <h4>Novo Serviço</h4>
                         <?php echo form_open(); ?>
                         <?php echo render_input('name', 'Nome do Serviço'); ?>
                         <?php echo render_input('duration_minutes', 'Duração (minutos)', '30', 'number'); ?>
                         <?php echo render_input('price', 'Preço', '', 'number'); ?>
                         <?php echo render_color_picker('color', 'Cor no Calendário', '#3b82f6'); ?>
                         <button type="submit" class="btn btn-primary">Salvar</button>
                         <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
             <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4>Serviços Ativos</h4>
                        <table class="table dt-table">
                            <thead>
                                <tr><th>Nome</th><th>Duração</th><th>Preço</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($services as $s): ?>
                                <tr>
                                    <td><span style="color:<?php echo $s['color']; ?>">■</span> <?php echo $s['name']; ?></td>
                                    <td><?php echo $s['duration_minutes']; ?> min</td>
                                    <td><?php echo app_format_money($s['price'], 'BRL'); ?></td>
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
<?php init_tail(); ?>