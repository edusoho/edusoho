(function () {
    "use strict";
    function KityformulaDialog(editor) {
        var isIE=!-[1,];

        //防止modal关闭后重新打开创建多个iframe导致BUG
        if($("#editorContainer_"+editor.name).length > 0 ){
            $("#editorContainer_"+editor.name).remove();
        }
        var html = '<iframe scrolling="no" id="editorContainer_'+editor.name+'" src="/assets/libs/ckeditor/4.6.7/plugins/kityformula/kityformula/index.html" width="780" height="500"></iframe>';

        return {
            title: '公式编辑器',
            minWidth: 300,
            minHeight: 80,
            buttons: [
            CKEDITOR.dialog.okButton,
            CKEDITOR.dialog.cancelButton],
            contents: [{
                id: 'kityformula',
                label: '公式编辑器',
                title: '公式编辑器',
                expand: true,
                padding: 0,
                elements: [{
                    type: "html",
                    html: html
                }]
            }],
            onLoad: function () {

            },
            onShow: function () {
                $("#editorContainer_"+editor.name)[0].contentWindow.postMessage({eventName: 'kityformula.show'}, '*');
            },
            onHide: function () {

            },
            onOk: function () {
                if(isIE){
                    $("#oldFormula").val();
                }else{
                    $("#editorContainer_"+editor.name)[0].contentWindow.postMessage({eventName: 'kityformula.ok'}, '*');
                }
            },
            onCancel: function () {
                // alert('onCancel');
            },
            resizable: CKEDITOR.DIALOG_RESIZE_HEIGHT
        };
    }

    CKEDITOR.dialog.add('kityformula', function (editor) {
        window.addEventListener('message', function (e) {
            var eventName = e.data.eventName;
            if (eventName === 'es-ckeditor.post') {
                var source = e.data.source;
                var $imgUrl = e.data.imageUrl;
                $.post($('#'+editor.name).data('imageDownloadUrl'),{url:$imgUrl}, function(result){
                    var insertHtml='<img kityformula="true" src="'+result+'" alt="'+source+'">';
                    editor.insertHtml(insertHtml);
                });
            }
        }, false);
        return KityformulaDialog(editor);
    });
})();