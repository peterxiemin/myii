/**
 * Created by baidu on 16/7/24.
 */
$(function(){
    var envId = $('#env_id').val();
    var btn_submit = $('#btn_submit');
    var modal = $('#modal');

    modal.on('hidden.bs.modal', function(){
        $('.modal-btn-ok', modal).addClass('hide');
    });

    function showModal(message){
        $('.modal-body', modal).html(message);
        modal.modal('show');
    }

    function showConfirm(message, okCbk){
        $('.modal-body', modal).html(message);
        modal.modal('show'); 
        $('.modal-btn-ok', modal).unbind();
        $('.modal-btn-ok', modal).removeClass('hide').one('click', function(){
            modal.modal('hide');
            okCbk();
        });

    }

    btn_submit.on('click', function(event){
        var branch_name = $("input[id=env-branch_name]").val();
        var discription = $("input[id=env-discription]").val();
        if (!branch_name) {
            showModal('分支名称 不能为空');
            return;
        }

        if (branch_name.toLowerCase().indexOf('master') >= 0) {
            showModal('分支名称 不能为master');
            return;
        }

        $.ajax({
            url: '/sandconsole/web/index.php?r=agent/clone&id= ' + envId + '&discription=' + discription + '&branch_name=' + branch_name,
            type: 'get',
            dataType: 'json',
            data: {
                id: envId,
            },
	    success: function (data) {
                if (data.data == 'success') {
                    location.href = 'index.php';
                }
                else {
                    var ret = JSON.stringify(data);
                    showModal(ret);
                }
            }
        });
    });
});
