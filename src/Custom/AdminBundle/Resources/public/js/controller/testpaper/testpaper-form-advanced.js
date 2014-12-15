define(function(require, exports, module) {

    var Widget     = require('widget');
    var Notify = require('common/bootstrap-notify');
    var EditorFactory = require('common/kindeditor-factory');
    var Validator = require('bootstrap.validator');
    Validator.addRule('countcheck', function(options) {
        var $modal = $("#testpaper-part-modal");
        return parseInt($modal.find('#testpaper-count-field').val()) <= parseInt($modal.find('#testpaper-count-field-span').data('num'));
    }, '必须小于等于已有题目!');
    require('common/validator-rules').inject(Validator);
    require('jquery.sortable');
    var TagTreeChooser = require('tag-tree-chooser');
    var TagChooser = require('tag-chooser');

    var TestpaperForm = Widget.extend({

        attrs: {
            validator: null,
            parts: []
        },

        events: {
            'click .part-update-btn': '_onClickPartUpdateBtn',
            'click .part-remove-btn': '_onClickPartRemoveBtn'

        },

        _defaults: {
            'single_choice' : {name:'单选题', type: 'single_choice', mistakeScore: 0 },
            'choice' : {name:'多选题', type: 'choice', mistakeScore: 0, missScore: 0 },
            'uncertain_choice' : {name:'不定向选择题', type: 'uncertain_choice', mistakeScore: 0 },
            'fill': {name:'多选题', type: 'fill' },
            'determine' : {name:'判断题', type: 'single_choice', mistakeScore: 0 },
            'essay': {name:'问答题', type: 'essay' },
            'material' : {name:'材料题', type: 'essay' }
        },

        _tagPartChooser: null,

        setup:function() {
            this.createValidator();
            if (this.get('haveBaseFields') === true) {
                this.initBaseFields();
            }

            var self = this;
            var $modal = $("#testpaper-part-modal");
            $modal.on('show.bs.modal', function(e) {
                var $trigger = $(e.relatedTarget);
                if ($trigger.data('mode') == 'update') {
                    var values = $trigger.parents('tr').data();
                    values = $.extend({}, self._defaults[values.type], values);
                    $modal.find('.part-save-btn').data('modal', 'update');
                } else {
                    var values = self._defaults[$trigger.data('type')];
                    $modal.find('.part-save-btn').data('modal', 'create');
                    $modal.find('[name=description]').val('');
                    $modal.find('input[name=score]').val('');
                    $modal.find('input[name=count]').val('');
                    $modal.find('input[name=mistakeScore]').val('');
                    $modal.find('input[name=missScore]').val('');
                    $modal.find('input[name=tagIds]').val('');
                }

                $modal.find('.modal-title').html(values.name);
                $modal.find('input[name=name]').val(values.name);
                $modal.find('input[name=type]').val(values.type);
                $modal.find('input[name=id]').val(values.id);
                if (values.tagIds) {
                    $modal.find('input[name=tagIds]').val(values.tagIds);
                }

                var mistakeScore = parseInt(values['mistakeScore']);
                var $mistakeScoreFormGroup = $modal.find('.mistakeScore-form-group')
                if (isNaN(mistakeScore)) {
                    $mistakeScoreFormGroup.find('input[type=text]').attr('disabled', 'disabled');
                    $mistakeScoreFormGroup.hide();
                } else {
                    $mistakeScoreFormGroup.find('input[type=text]').removeAttr('disabled', 'disabled');

                    if (mistakeScore > 0) {
                        $mistakeScoreFormGroup.find('input[type=checkbox]').prop('checked', 'checked');
                        $mistakeScoreFormGroup.find('input[type=text]').removeAttr('disabled', 'disabled').val(mistakeScore);
                    } else {
                        $mistakeScoreFormGroup.find('input[type=checkbox]').prop('checked', false);
                        $mistakeScoreFormGroup.find('input[type=text]').attr('disabled', 'disabled')
                    }

                    $mistakeScoreFormGroup.show();
                }

                var missScore = parseInt(values['missScore']);
                var $missScoreFormGroup = $modal.find('.missScore-form-group')
                if (isNaN(missScore)) {
                    $missScoreFormGroup.find('input[type=text]').attr('disabled', 'disabled');
                    $missScoreFormGroup.hide();
                } else {
                    $missScoreFormGroup.find('input[type=text]').removeAttr('disabled', 'disabled');

                    if (missScore > 0) {
                        $missScoreFormGroup.find('input[type=checkbox]').prop('checked', 'checked');
                        $missScoreFormGroup.find('input[type=text]').removeAttr('disabled', 'disabled').val(missScore);
                    } else {
                        $missScoreFormGroup.find('input[type=checkbox]').prop('checked', false);
                        $missScoreFormGroup.find('input[type=text]').attr('disabled', 'disabled')
                    }

                    $missScoreFormGroup.show();
                }

            });

            $modal.on('shown.bs.modal', function(e) {
                if (!self._tagPartChooser) {
                    self._tagPartChooser = new TagChooser({
                        element: '#part-tag-chooser',
                        sourceUrl: $('#part-tag-chooser').data('sourceUrl'),
                        queryUrl: $('#part-tag-chooser').data('queryUrl'),
                        matchUrl: $('#part-tag-chooser').data('matchUrl'),
                        maxTagNum: 15
                    });

                    var $partForm = $('.testpaper-part-form');
                    self._tagPartChooser.on('change', function(tags) {
                        var ids = [];
                        $.each(tags, function(i, tag) {
                            ids.push(tag.id);
                        });
                        $partForm.find('[name=tagIds]').val(ids.join(','));
                        self._getQuestionsCount();
                    });
                } else {
                    var choosedTags = [];
                    if ($modal.find('input[name=tagIds]').val().length > 0) {
                        choosedTags = $modal.find('input[name=tagIds]').val().split(',');
                    }

                    self._tagPartChooser.resetTags(choosedTags);
                }

                self._getQuestionsCount();



            });


            $modal.find('.mistakeScore-form-group [type=checkbox]').click(function(){
                var checked = $(this).prop('checked');
                if (checked) {
                    $modal.find('.mistakeScore-form-group [type=text]').removeAttr('disabled');
                } else {
                    $modal.find('.mistakeScore-form-group [type=text]').attr('disabled', 'disabled');
                }
            });

            $modal.find('.missScore-form-group [type=checkbox]').click(function(){
                var checked = $(this).prop('checked');
                if (checked) {
                    $modal.find('.missScore-form-group [type=text]').removeAttr('disabled');
                } else {
                    $modal.find('.missScore-form-group [type=text]').attr('disabled', 'disabled');
                }
            });


            var $list = $('.testpaper-parts-list').sortable({
                itemSelector: '.testpaper-part',
                handle: '.testpaper-part-sort-handler',
                serialize: function(parent, children, isContainer) {
                    return isContainer ? children : parent.attr('id');
                }
            });

            this._initTagChooser();

            this._initPartForm();

        },

        _createPartId: function() {
            var max = 0;
            this.$('.testpaper-part').each(function(i, tr) {
                if ($(tr).data('id') > max) {
                    max = $(tr).data('id');
                }
            });

            return max + 1;
        },

        _createPartHtml: function(part) {
            var html = '<tr class="testpaper-part testpaper-part-' + part.id + '">';
            html += '<td><a href="javascript:;" class="testpaper-part-sort-handler"><span class="glyphicon glyphicon-move"></span></a></td>';
            html += '<td>' + part.name + '</td>';
            html += '<td>' + part.count + '道，总分' + (part.count*part.score) + '分</td>';
            html += '<td>' + part.description + '</td>';
            html += '<td>';
            html += '  <a href="javascript:;" class="btn btn-link btn-xs part-update-btn" data-mode="update" data-toggle="modal" data-target="#testpaper-part-modal"><span class="glyphicon glyphicon-pencil"></span></a>';
            html += '  <a href="javascript:;" class="btn btn-link btn-xs disabled"><span class="glyphicon glyphicon-eye-open"></span></a>';
            html += '  <a href="javascript:;" class="btn btn-link btn-xs part-remove-btn"><span class="glyphicon glyphicon-trash"></span></a>';
            html += '</td>';
            html += '</tr>';
            var $html = $(html).data(part);
            return $html;
        },

        _onClickPartUpdateBtn: function(e) {


        },

        _onClickPartRemoveBtn: function(e) {
            $(e.currentTarget).parents('tr').remove();
        },
        _getQuestionsCount: function(e) {
            var $form = $('#testpaper-form');
            var $partForm = $('.testpaper-part-form');
            var $modal = $("#testpaper-part-modal");
            var type = $modal.find('input[name=type]').val();
            var tagIds = $.merge($form.find('[name=tagIds]').val().split(','), $partForm.find('[name=tagIds]').val().split(','));
            tagIds = $.unique(tagIds);
            tagIds = tagIds.join(',');
            if(tagIds.indexOf(',') == 0) {
                tagIds = tagIds.substring(1);
            }
            if(tagIds.lastIndexOf(',') == tagIds.length-1) {
                tagIds = tagIds.substring(0, tagIds.length-1);
            }
            
            $modal.find('#testpaper-count-field-span').html('查询中...');

            $.get($('#testpaper-count-field').data('url'), {type:type,knowledgeIds:$form.find('[name=knowledgeIds]').val(), tagIds:tagIds}, function(count){
                $modal.find('#testpaper-count-field-span').html('(共'+count+'题)');
                $modal.find('#testpaper-count-field-span').data('num', count);
            });
        },

        _initPartForm: function() {
            var self = this;
            var validator = new Validator({
                element: '.testpaper-part-form',
                autoSubmit: false
            });

            validator.addItem({
                element: 'input[name=score]',
                required: true,
                rule: 'integer'
            });

            validator.addItem({
                element: 'input[name=count]',
                required: true,
                rule: 'integer countcheck'
            });

            validator.on('formValidated', function(error, msg, $form) {
                if (error) {
                    return ;
                }

                var $modal = $('#testpaper-part-modal');
                var formData = $form.serializeArray();
                var part = {};
                $.each(formData, function(i, field) {
                    part[field.name] = field.value;
                });
                if ($modal.find('.part-save-btn').data('modal') == 'create') {
                    part.id = self._createPartId();
                    var $html = self._createPartHtml(part);
                    $('.testpaper-parts-table tbody').append($html);
                } else {
                    $('.testpaper-part-' + part.id).replaceWith(self._createPartHtml(part));
                }
                self._setPartsInput();
                $modal.modal('hide');

                return false;

            });

        },

        _setPartsInput: function($form) {
            var $form = $('#testpaper-form');
            var parts = [];
            $form.find('.testpaper-part').each(function(i, tr){
                parts.push($(tr).data());
            });

            var parts = JSON.stringify(parts);

            $form.find('input[name=parts]').val(parts);
        },

        _initTagChooser: function() {
            var $form = $('#testpaper-form');

            var knowledgeChooser = new TagTreeChooser({
                element: '#testpaper-knowledge-chooser',
                sourceUrl: $('#testpaper-knowledge-chooser').data('sourceUrl'),
                queryUrl: $('#testpaper-knowledge-chooser').data('queryUrl'),
                matchUrl: $('#testpaper-knowledge-chooser').data('matchUrl'),
                maxTagNum: 15
            });

            knowledgeChooser.on('change', function(tags) {
                var ids = [];
                $.each(tags, function(i, tag) {
                    ids.push(tag.id);
                });
                $form.find('[name=knowledgeIds]').val(ids.join(','));
            });

            var tagChooser = new TagChooser({
                element: '#testpaper-tag-chooser',
                sourceUrl: $('#testpaper-tag-chooser').data('sourceUrl'),
                queryUrl: $('#testpaper-tag-chooser').data('queryUrl'),
                matchUrl: $('#testpaper-tag-chooser').data('matchUrl'),
                maxTagNum: 15
            });

            tagChooser.on('change', function(tags) {
                var ids = [];
                $.each(tags, function(i, tag) {
                    ids.push(tag.id);
                });
                $form.find('[name=tagIds]').val(ids.join(','));
            });

        },

        createValidator: function() {
            this.set('validator', new Validator({
                element: this.element
            }));
        },

        initBaseFields: function() {
            var validator = this.get('validator');
            var editor = EditorFactory.create('#testpaper-description-field', 'simple_noimage');
            validator.on('formValidate', function(elemetn, event) {
                editor.sync();
            });

            validator.addItem({
                element: 'input[name=parts]',
                required: true,
                errormessageRequired: '请添加题目设置'
            });

            validator.addItem({
                element: '#testpaper-name-field',
                required: true
            });

            validator.addItem({
                element: '#testpaper-description-field',
                required: true,
                rule: 'maxlength{max:500}'
            });

            validator.addItem({
                element: '#testpaper-limitedTime-field',
                required: true,
                rule: 'integer,max{max:10000}'
            });

            validator.on('formValidated', function(error, msg, $form) {
                if (error) {
                    return ;
                }

                $form[0].submit();


            });

        }

    });

    exports.run = function() {
        new TestpaperForm({
            element: '#testpaper-form'
        });
    }

});
