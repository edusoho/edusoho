'use strict';

CKEDITOR.dialog.add('questionblank', function(editor) {
    var lang = editor.lang.questionblank,
        generalLabel = editor.lang.common.generalTab,
        validNameRegex = /^[^\[\]\<\>]+$/;

    var makeBlankInput = function(value) {
        if (!value) {
            value = '';
        }

        var html = '<table class="cke_dialog_ui_hbox" data-role="input-row" style="margin-bottom:10px;">' + '  <tr class="cke_dialog_ui_hbox">' + '    <td class="cke_dialog_ui_hbox_first" style="width:85%">' + '      <input type="text" class="qb-input cke_dialog_ui_input_text" value="' + value + '">' + '    </td>' + '    <td class="cke_dialog_ui_hbox_last" style="width:15%">' + '      <a href="javascript:;" class="cke_dialog_ui_button" data-role="delete"><span class="cke_dialog_ui_button">删</span></a>' + '    </td>' + '  </tr>' + '</table>';
        return html;
    };

    var makeBlankAddButton = function() {
        var html = '<table class="cke_dialog_ui_hbox" style="margin-bottom:10px;">' + '  <tr class="cke_dialog_ui_hbox">' + '    <td class="cke_dialog_ui_hbox_last" style="width:100%">' + '      <a href="javascript:;" class="cke_dialog_ui_button" data-role="add"><span class="cke_dialog_ui_button">+ 添加</span></a>' + '    </td>' + '  </tr>' + '</table>';
        return html;
    }


    return {
        title: lang.title,
        minWidth: 360,
        minHeight: 120,
        contents: [{
            id: 'info',
            label: generalLabel,
            title: generalLabel,
            elements: [{
                id: 'blanks',
                type: 'html',
                html: '<div class="qb-container"></div>',
                setup: function(widget) {
                    var html = '';

                    var answers = widget.data.name.split('|');
                    $.each(answers, function(i, answer) {
                        html += makeBlankInput(answer);
                    });

                    html = '<div class="input-blank-rows">' + html + '</div>';

                    var $container = $('#' + this.domId);
                    $container.html(html + makeBlankAddButton());
                },
                commit: function(widget) {
                    var $container = $('#' + this.domId);

                    var answers = [];
                    $.each($container.find('.qb-input'), function() {
                        answers.push($(this).val());
                    });

                    widget.setData('name', answers.join('|'));
                }
            }, {
                id: 'hint',
                type: 'html',
                html: '<span style="color:#666;">提示：点击 [+添加]，可为一个填空设置多个答案</span>'
            }]
        }],

        onShow: function(event) {
        },

        onLoad: function(event) {
            var $body = $(this.parts.contents.$);

            $body.on('click', '[data-role=delete]', function() {
                $(this).parents('[data-role=input-row]').remove();
                if ($body.find('.qb-input').length <= 1) {
                    $body.find('[data-role=delete]').hide();
                }
            });

            $body.on('click', '[data-role=add]', function() {
                var $row = $body.find('[data-role=input-row]:first').clone();
                $row.find('.qb-input').val('');
                $body.find('.input-blank-rows').append($row);
                if ($body.find('.qb-input').length > 1) {
                    $body.find('[data-role=delete]').show();
                }
            });


        }

    };
});