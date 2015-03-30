CKEDITOR.plugins.add('hidden', {
    icons: 'hidden',
    init: function(editor) {
        //Plugin logic goes here.
        editor.addCommand('hidden', {
            exec: function(editor) {

                $('#myModal').modal('show');
                $('#insert').on('click', function() {

                    var type = $('input[name=type]:checked').val();

                    var text = $('#text').val();

                    if (type == "reply") {

                        var content = "&nbsp;[hide=reply]" + text + "[/hide]";
                        editor.insertHtml(content);

                    } else {

                        var amount = $('#amount').val();

                        amount = parseInt(amount);

                        if (amount > 0) {

                            var content = "&nbsp;[hide=coin" + amount + "]" + text + "[/hide]";
                            editor.insertHtml(content);
                        }

                    }

                    $('#text').val('');
                    $('#amount').val('');
                    $("#insert").unbind("click")
                    $('#myModal').modal('hide');

                });
            }
        });
        editor.ui.addButton('Hidden', {
            label: '隐藏内容',
            command: 'hidden',
            toolbar: 'insert,100'
        });

    }
});