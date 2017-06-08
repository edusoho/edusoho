'use strict';

CKEDITOR.dialog.add('questionblank', function(editor) {
    var lang = editor.lang.questionblank,
        generalLabel = editor.lang.common.generalTab,
        validNameRegex = /^[^\[\]\<\>]+$/;

    var inputId = 0;

    var makeBlankInput = function(value) {
        inputId ++;
        if (!value) {
            value = '';
        }

        var html = '<table class="cke_dialog_ui_hbox" data-role="input-row" style="margin-bottom:10px;">' + '  <tr class="cke_dialog_ui_hbox">' + '    <td class="cke_dialog_ui_hbox_first" style="width:85%">' + '      <input type="text" class="qb-input cke_dialog_ui_input_text" value="' + value + '" id=qb-input-' + inputId + '>' + '    </td>' + '    <td class="cke_dialog_ui_hbox_last" style="width:15%">' + '      <a href="javascript:;" class="cke_dialog_ui_button" data-role="delete"><span class="cke_dialog_ui_button">'+editor.lang.questionblank.deleteBtnText+'</span></a>' + '    </td>' + '  </tr>' + '</table>';
        return html;
    };

    var makeBlankAddButton = function() {
        var html = '<table class="cke_dialog_ui_hbox" style="margin-bottom:10px;">' + '  <tr class="cke_dialog_ui_hbox">' + '    <td class="cke_dialog_ui_hbox_last" style="width:100%">' + '      <a href="javascript:;" class="cke_dialog_ui_button" data-role="add"><span class="cke_dialog_ui_button">+ '+editor.lang.questionblank.addBtnText+'</span></a>' + '    </td>' + '  </tr>' + '</table>';
        return html;
    }


    var dialog;
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
                onLoad: function( event ) {
                    dialog = event.sender;
                },
                setup: function(widget) {
                    var html = '';

                    var answers = widget.data.name.split('|');
                    $.each(answers, function(i, answer) {
                        html += makeBlankInput(answer);
                    });

                    html = '<div class="input-blank-rows">' + html + '</div>';

                    var $container = $('#' + this.domId);
                    $container.html(html + makeBlankAddButton());

                    $container.find('.qb-input').each(function(){
                        dialog.addFocusable(new CKEDITOR.dom.element( document.getElementById( $(this).attr('id') ) ));
                    });
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
                html: '<span style="color:#666;">'+editor.lang.questionblank.tip+'</span>'
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
                var $row = $(makeBlankInput(''));
                $body.find('.input-blank-rows').append($row);
                dialog.addFocusable(new CKEDITOR.dom.element( document.getElementById( $row.find('.qb-input').attr('id') ) ));
                if ($body.find('.qb-input').length > 1) {
                    $body.find('[data-role=delete]').show();
                }
            });

        }

    };
});