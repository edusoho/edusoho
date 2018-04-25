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
        if($("#editorContainer_"+editor.name).length > 0 ){
            $("#editorContainer_"+editor.name).remove();
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
                var kfe = $("#editorContainer_"+editor.name)[0].contentWindow.kfe;
                if(kfe){
                    kfe.execCommand( "render", '\\placeholder');
                }
            },
            onHide: function () {
                
            },
            onOk: function () {
                var source;
                if(isIE){
                    source = $("#oldFormula").val();
                }else{
                    var kfe = $("#editorContainer_"+editor.name)[0].contentWindow.kfe;
                    source = kfe.execCommand( "get.source" );
                    var replaceSpecialCharacter = function(source) {
                        var $source = source.replace(/\\cong/g,'=^\\sim')
                            .replace(/\\varnothing/g,'\\oslash')
                            .replace(/\\gets/g,'\\leftarrow')
                            .replace(/\\because/g,'\\cdot_\\cdot\\cdot')
                            .replace(/\\blacksquare/g,'\\rule{20}{20}');
                        return $source;
                    };
                    source = replaceSpecialCharacter(source);
                    if ($.trim(source) == "\\placeholder"){
                        return;
                    }
                    if(/.*[\u4e00-\u9fa5]+.*$/.test(source)) {
                        alert("不能含有汉字！");
                        return false;
                    }
                    for(var i=0;i<source.length;i++)
                    {
                        var strCode=source.charCodeAt(i);
                        if((strCode>65248)||(strCode==12288)){
                            alert("不能含有中文全角字符");
                            return false;
                        }
                    }
                    var $imgUrl = 'http://formula.edusoho.net/cgi-bin/mimetex.cgi?'+source;
                    $.post($('#'+editor.name).data('imageDownloadUrl'),{url:$imgUrl}, function(result){
                        var insertHtml='<img kityformula="true" src="'+result+'" alt="'+source+'">';
                        editor.insertHtml(insertHtml);
                    });
                }
            },
            onCancel: function () {
                // alert('onCancel');
            }
        };
    }
 
    CKEDITOR.dialog.add('kityformula', function (editor) {
        return KityformulaDialog(editor);
    });
})();