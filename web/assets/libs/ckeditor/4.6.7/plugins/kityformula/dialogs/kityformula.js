(function () {
    function KityformulaDialog(editor) {
        var isIE=!-[1,];
        if (isIE) {
            html='<div class="formulaOld" style="width:750px;height:350px;"><div class="tips" style="color:red;line-height:40px;">请升级您的浏览器到IE9版本及以上版本，或使用chrome浏览器使用可视化公式编辑器</div><textarea id="oldFormula" style="width:100%;height:240px;border:1px solid #ccc;"></textarea></div>';
        }
        html = '<iframe scrolling="no" id="editorContainer_'+editor.name+'" src="/assets/libs/ckeditor/4.6.7/plugins/kityformula/kityformula/index.html" width="780" height="500"></iframe>';
        
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
                    html:html
                }]
            }],
            onLoad: function () {
                // alert('onLoad');
            },
            onShow: function () {
                if(isIE){
                    $("#oldFormula").val(source);
                    return false;
                }
                var kfe = $("#editorContainer_"+editor.name)[0].contentWindow.kfe;
                if(kfe){
                    kfe.execCommand( "render", '\\placeholder');
                }
            },
            onHide: function () {
                // alert('onHide');
            },
            onOk: function () {

                if(isIE){
                    source = $("#oldFormula").val();
                }else{
                    var kfe = $("#editorContainer_"+editor.name)[0].contentWindow.kfe;
                    source = kfe.execCommand( "get.source" );
                    source = kfe.replaceSpecialCharacter(source);
                    if ($.trim(source) == "\\placeholder"){
                        return;
                    }
                    if(/.*[\u4e00-\u9fa5]+.*$/.test(source)) {
                        alert("不能含有汉字！");
                        return false;
                    }
                    for(var i=0;i<source.length;i++)
                    {
                        strCode=source.charCodeAt(i);
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
            },
            resizable: CKEDITOR.DIALOG_RESIZE_HEIGHT
        };
    }
 
    CKEDITOR.dialog.add('kityformula', function (editor) {
        return KityformulaDialog(editor);
    });
})();