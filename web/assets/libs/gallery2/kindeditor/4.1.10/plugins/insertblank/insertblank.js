
KindEditor.plugin('insertblank', function(K) {
    var self = this, name = 'insertblank';
    self.clickToolbar(name, function() {
        var html = ['<div style="margin:20px 20px;">',
                    '  <div class="insertblank-inputs">',
                    '    <div class="ke-dialog-row">',
                    '      <input class="ke-input-text" type="text" value="" style="width:250px;" />',
                    '    </div>',
                    '  </div>',
                    '  <div class="ke-dialog-row">',
                    '    <a class="insertblank-add-btn" href="javascript:;">+ 添加</a>',
                    '  </div>',
                    '  <div class="ke-dialog-row">',
                    '    <div style="color:#666;">提示：点击 [+添加]，可为一个填空设置多个答案</div>',
                    '</div>'].join(''),

            dialog = self.createDialog({
                name : name,
                width : 450,
                title : '插入填空项',
                body : html,
                yesBtn : {
                    name : self.lang('yes'),
                    click : function(e) {

                        var blankValues = [];

                        K('div.insertblank-inputs').scan(function(node){
                            if (K(node).hasClass('ke-input-text')){
                                blankValues.push(K(node).val());
                            }
                        });

                        html = '[[' + blankValues.join('|') + ']]';

                        self.insertHtml(html).hideDialog().focus();
                    }
                }
            }),

            dialogRoot = dialog.div,

            blankInput = K('.ke-input-text', dialogRoot);
        
        blankInput[0].focus();

        K('.insertblank-add-btn', dialogRoot).click(function() {
            var blankInputHtml = [
                    '<div class="ke-dialog-row">',
                    '<input class="ke-input-text" type="text" value="" style="width:250px;" />',
                    '</div>'].join(''),
                blankInputNode = K(blankInputHtml);
            K('.insertblank-inputs', dialogRoot).append(blankInputNode);
            K('.ke-input-text', blankInputNode).get(0).focus();

            dialogRoot.height(dialogRoot.height() + 26);
        });

        K('.insertblank-inputs', dialogRoot).bind('keydown', function(event) {
            if (event.keyCode == 13){
                var blankInputHtml = [
                        '<div class="ke-dialog-row">',
                        '<input class="ke-input-text" type="text" value="" style="width:250px;" />',
                        '</div>'].join(''),
                    blankInputNode = K(blankInputHtml);
                K('.insertblank-inputs', dialogRoot).append(blankInputNode);
                K('.ke-input-text', blankInputNode).get(0).focus();

                dialogRoot.height(dialogRoot.height() + 26);
            }
        });



    });

});