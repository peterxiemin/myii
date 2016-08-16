/**
 * Created by baidu on 16/7/24.
 */
$(function(){
    var envId = $('#env_id').val();
    var btnSave = $('#btn_save');
    var modal = $('#modal');

    modal.on('hidden.bs.modal', function(){
        $('.modal-btn-ok', modal).addClass('hide');
    });

    function showModal(message){
        $('.modal-body', modal).html(message);
        modal.modal('show');
    }


    btnSave.on('click', function(event){
        var app = $("select[id=update_code_select]").find("option:selected").text();
        var svn = $("input[id=svn]").val();
        if (!svn) {
            showModal('svn 不能为空');
            return;
        }
        if (svn.toLowerCase().indexOf(app) === -1) {
            showModal('svn 地址不正确');
            return;
        }
        $.ajax({
            url: '/sandconsole/web/index.php?r=agent/update-code&id= ' + envId + '&app=' + app + '&svn=' +svn,
            type: 'get',
            dataType: 'json',
            data: {
                id: envId,
            },
            success: function(data){
                var ret = JSON.stringify(data);
                showModal(ret);
            }
        });
    });
});