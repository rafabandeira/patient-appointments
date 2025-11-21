<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        
                        <button class="btn btn-info mbot15" data-toggle="modal" data-target="#newAppointmentModal">
                            <i class="fa fa-plus"></i> Novo Agendamento
                        </button>

                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="newAppointmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <?php echo form_open(admin_url('patient_appointments/add_appointment_ajax'), ['id'=>'appointment-form']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Agendar Paciente</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Paciente</label>
                    <select name="patient_id" class="form-control selectpicker" data-live-search="true" required>
                        <?php foreach($patients as $p): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo $p['fullname']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Serviço</label>
                    <select name="service_id" class="form-control selectpicker" required>
                        <?php foreach($services as $s): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo $s['name']; ?> (<?php echo $s['duration_minutes']; ?> min)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Data e Hora Início</label>
                    <input type="datetime-local" name="start_time" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Observações</label>
                    <textarea name="notes" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(function(){
    var calendarEl = document.getElementById('calendar');
    
    // Simulação de inicialização do Calendar (Depende da versão do FullCalendar no Perfex do usuário)
    // Abaixo, código genérico jQuery FullCalendar v3/v4 compatível
    var calendar = $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        defaultView: 'agendaWeek',
        editable: false,
        events: '<?php echo admin_url("patient_appointments/get_events"); ?>',
        eventClick: function(event) {
            alert('Paciente: ' + event.title + '\nObs: ' + event.description);
        }
    });

    // Form Submit via AJAX
    $('#appointment-form').submit(function(e){
        e.preventDefault();
        var form = $(this);
        $.post(form.attr('action'), form.serialize(), function(response){
            var res = JSON.parse(response);
            if(res.status) {
                alert_float('success', 'Agendado com sucesso');
                $('#newAppointmentModal').modal('hide');
                $('#calendar').fullCalendar('refetchEvents');
            } else {
                alert_float('danger', res.message);
            }
        });
    });
});
</script>
</body>
</html>