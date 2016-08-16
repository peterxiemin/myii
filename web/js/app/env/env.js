/**
 * Created by baidu on 16/7/24.
 */
$(function(){
    var checkConfPath = true;
    var envId = $('#env_id').val();
    var tree = $("#tree");
    var filePath = $('#filePath');
    var content = $('#content');
    var btnSave = $('#btn_save');
    var modal = $('#modal');
    var lastContent = null;

    modal.on('hidden.bs.modal', function(){
        $('.modal-btn-ok', modal).addClass('hide');
    });

    window.onbeforeunload = function(){
        if(lastContent !== null && lastContent !== content.val()){
            if(!confirm('文件内容未保存，确认离开当前编辑状态吗？')){
                return false;
            };
        }
    };

    var setting = {
        async: {
            enable: true,
            url: '/sandconsole/web/index.php',
            type: 'get',
            dataType: 'json',
            autoParam:["id=path", "name=n", "level=lv"],
            otherParam:{"r": 'agent/ls',"id": envId},
            dataFilter: filter
        },
        callback: {
            onClick: function (event, treeId, treeNode, clickFlag) {
                if(lastContent !== null && lastContent != content.val()){
                    showConfirm('文件内容未保存，确认离开当前编辑状态吗？', function(){
                        lastContent = null;
                        if(!treeNode.isParent){
                            filePath.val(treeNode.id);
                            readFile(treeNode.id, function(data){
                                if(data && data.errno == 0){
                                    lastContent = data.data;
                                    content.val(data.data);
                                }
                            });
                        }
                    });
                }else{
                    if(!treeNode.isParent){
                        filePath.val(treeNode.id);
                        readFile(treeNode.id, function(data){
                            if(data && data.errno == 0){
                                lastContent = data.data;
                                content.val(data.data);
                            }
                        });
                    }
                }

            },
            onAsyncSuccess: zTreeOnAsyncSuccess
        }
    };
    var zTree = $.fn.zTree.init(tree, setting);

    function zTreeOnAsyncSuccess(event, treeId, treeNode, msg){
        if (!treeNode || (treeNode && needOpen(treeNode))) {
            try {
                //调用默认展开第一个结点
                var selectedNode = zTree.getSelectedNodes();
                var nodes = zTree.getNodes();

                if(!treeNode){
                    //nodes[0] 此时是根节点
                    zTree.expandNode(nodes[0], true);
                }

                var childNodes = zTree.transformToArray(nodes[0]);
                for(var i = 0; i < childNodes.length; i++){
                    var node = childNodes[i];
                    if(needOpen(node)){
                        zTree.expandNode(node, true);
                    }
                }
            } catch (err) {

            }
        }
    }

    function filter(treeId, parentNode, responseData) {
        if(responseData && responseData['errno'] === 0){
            var nodes = [];
            for(var i = 0; i < responseData.data.length; i++){
                var file = responseData.data[i];
                if(checkConfPath) {
                    if (file['is_dir'] && !isConfDir(file)) {
                        continue;
                    }
                    if (!file['is_dir'] && !isConfFile(file)) {
                        continue;
                    }
                }
                var node = {};
                node.id = file['path'];
                node.pid = file['dir'];
                node.name = file['name'];
                node.isParent = file['is_dir'];
                node.open = needOpen(file);

                nodes.push(node);
            }
            return nodes;
        }else{
            return null;
        }
    }


    function isConfDir(file){
        if(file['path'].indexOf('/conf') > 0 && $.inArray(file['name'], ['.svn', '.git', 'sysdata']) == -1){
            return true;
        }
        return false;
    }

    function isConfFile(file){
        if('conf' == file['ext']){
            return true;
        }
        return false;
    }

    function needOpen(node){
        if(node.isParent && $.inArray(node.name, ['conf', 'app', 'ral', 'services']) != -1){
            return true;
        }
        return false;
    }

    function readFile(path, cbk){
        $.ajax({
            url: '/sandconsole/web/index.php',
            type: 'get',
            dataType: 'json',
            data: {
                id: envId,
                r: 'agent/read-file',
                path: path,
            },
            success: function(data){
                cbk(data);
            }
        });
    }

    function writeFile(path, cbk, errCbk){
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        $.ajax({
            url: '/sandconsole/web/index.php?r=agent/write-file&id=' + envId + '&path=' + path,
            type: 'post',
            dataType: 'json',
            data: {
                content: content.val(),
                _csrf : csrfToken
            },
            success: function(data){
                cbk(data);
            },
            error: function(xhr, status, error){
                errCbk(xhr, status, error);
            }
        });
    }

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

    btnSave.on('click', function(event){
        var path = filePath.val();
        if(!path){
            showModal('请选择文件');
            return;
        }
        if($.trim(content.val()) == ''){
            showConfirm('文件内容是空的，确认保存？', function(){
                clickSave(path);
            });
        }else{
            clickSave(path);
        }

    });

    function clickSave(path){
        btnSave[0].disabled = true;
        btnSave.text('保存中...');
        writeFile(path, function(data){
            if(data.errno){
                showModal('保存失败：' + data.msg);
            }else{
                lastContent = null;
                showModal('保存成功');
            }

            btnSave[0].disabled = false;
            btnSave.text('保存');
        },function(xhr, status, error){
            console.log(xhr.responseText);
            showModal('保存失败：' + (status ? status : error.message));
            btnSave[0].disabled = false;
            btnSave.text('保存');
        });
    }
});