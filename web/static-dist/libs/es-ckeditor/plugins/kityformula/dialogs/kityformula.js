(function () {
    "use strict";
    function iframeSrcPath(srcpath) {
        var filename = "dialogs/kityformula.js",
        scripts = document.getElementsByTagName('script'),
        script = null,
        len = scripts.length;

        for(var i = 0; i < scripts.length; i++) {
            if(scripts[i].src.indexOf(filename) != -1) {
                script = scripts[i];
                break;
            }
        }

        if(script) {
          var src = script.src;
          src = src.substr(0, src.lastIndexOf("/")+1);

          return src + srcpath;
        }
    }

    function KityformulaDialog(editor) {
        var isIE=!-[1,];

        //防止modal关闭后重新打开创建多个iframe导致BUG
        if($(document.getElementById("editorContainer_" + editor.name)).length > 0 ){
            $(document.getElementById("editorContainer_" + editor.name)).remove();
        }
        var html = '<iframe scrolling="no" id="editorContainer_'+editor.name+'" src="'+ iframeSrcPath("../kityformula/index.html") +'" style="width: 100% !important; height: 300px !important"></iframe>';

        return {
            title: '公式编辑器',
            minWidth: 780,
            minHeight: 300,
            resizable: CKEDITOR.DIALOG_RESIZE_NONE,
            buttons: [
            CKEDITOR.dialog.cancelButton,
            CKEDITOR.dialog.okButton],
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
                $(document.getElementById("editorContainer_" + editor.name))[0].contentWindow.postMessage({eventName: 'kityformula.show', editorName: editor.name }, '*');
            },
            onHide: function () {

            },
            onOk: function () {
                if(isIE){
                    $("#oldFormula").val();
                }else{
                    $(document.getElementById("editorContainer_" + editor.name))[0].contentWindow.postMessage({eventName: 'kityformula.ok', editorName: editor.name }, '*');
                }
            },
            onCancel: function () {
                // alert('onCancel');
            }
        };
    }

    CKEDITOR.dialog.add('kityformula', function (editor) {
        window.addEventListener('message', function (e) {
            var eventName = e.data.eventName;
            var editorName = e.data.editorName;
            if (eventName === 'es-ckeditor.post' && editorName === editor.name ) {
                var source = e.data.source;
                var $imgUrl = e.data.imageUrl;
                $.ajax({
                    type: "post",
                    url: $(document.getElementById(editor.name)).data(
                    "imageDownloadUrl"
                    ),
                    data: { url: $imgUrl },
                    beforeSend: function(request) {
                        request.setRequestHeader(
                            "X-CSRF-Token",
                            $("meta[name=csrf-token]").attr("content")
                        );
                    }
                }).done(function(result) {
                    var insertHtml='<img kityformula="true" src="'+result+'" alt="'+source+'">';
                    editor.insertHtml(insertHtml);
                });
            }
          }, false);
        return KityformulaDialog(editor);
    });
})();