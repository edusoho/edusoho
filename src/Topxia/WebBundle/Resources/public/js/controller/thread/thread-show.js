define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('es-ckeditor');
    var Widget = require('widget');
    var Notify = require('common/bootstrap-notify');

    var ThreadShowWidget = Widget.extend({

        attrs: {

        },

        events: {
            'click .js-post-more': 'onClickPostMore',
            'click .js-reply': 'onClickReply',
            'click .js-post-delete': 'onPostDelete',
            'click .js-post-up': 'onPostUp',
            'click  [data-role=confirm-btn]': 'onConfirmBtn',
            'click .js-toggle-subpost-form': 'onClickToggleSubpostForm',
            'click .js-event-cancel': 'onClickEventCancelBtn',
            'click .thread-subpost-container .pagination a': 'onClickSubpost'
        },

        setup: function() {
            if ($('[name=access-intercept-check]').length > 0) {
                $.get($('[name=access-intercept-check]').val(), function(response) {
                    if (response) {
                        return;
                    }

                    $('.access-intercept-modal').modal('show');

                }, 'json');
            }

            this._initPostForm();
        },

        onClickPostMore: function(e) {
            e.stopPropagation();
            var $btn = $(e.currentTarget);
            $btn.parents('.thread-subpost-moretext').addClass('hide');
            $btn.parents('.thread-post').find('.thread-subpost').removeClass('hide');
            $btn.parents('.thread-post').find('.pagination').removeClass('hide');
        },

        onPostDelete: function(e) {
            e.stopPropagation();
            var that = this;
            var $btn = $(e.currentTarget);
            if (!confirm('真的要删除该回复吗？')) {
                return;
            }
            var inSubpost = $btn.parents('.thread-subpost-list').length > 0;

            $.post($btn.data('url'), function() {
                if (inSubpost) {
                    var $subpostsNum = $btn.parents('.thread-post').find('.subposts-num');
                    $subpostsNum.text(parseInt($subpostsNum.text()) - 1);
                } else {
                    that.$('.thread-post-num').text(parseInt(that.$('.thread-post-num').text()) - 1);
                }
                $($btn.data('for')).remove();
            });
        },

        onPostUp: function(e) {
            e.stopPropagation();
            var $btn = $(e.currentTarget);
            $.post($btn.data('url'), function(response) {
                if (response.status == 'ok') {
                    $btn.find(".post-up-num").text(parseInt($btn.find(".post-up-num").text()) + 1);
                } else if (response.status == 'votedError') {
                    Notify.danger('您已点过赞了！');
                } else {
                    alert('点赞失败，请重试！');
                }
            }, 'json');

        },

        onConfirmBtn: function(e) {
            e.stopPropagation();
            var $btn = $(e.currentTarget);
            if (!confirm($btn.data('confirmMessage'))) {
                return;
            }
            $.post($btn.data('url'), function() {
                if ($btn.data('afterUrl')) {
                    window.location.href = $btn.data('afterUrl');
                    return;
                }
                window.location.reload();
            });
        },

        onClickReply: function(e) {
            e.stopPropagation();
            var $btn = $(e.currentTarget);
            var inSubpost = $btn.parents('.thread-subpost-list').length > 0;
            var $container = $btn.parents('.thread-post').find('.thread-subpost-container');
            var $form = $container.find('.thread-subpost-form');
            if (inSubpost) {
                $form.removeClass('hide');
                var text = '回复 @' + $btn.parents('.thread-post').data('authorName') + '： ';
                $form.find('textarea').val(text).trigger('focus');

            } else {
                if ($container.hasClass('hide')) {
                    $container.removeClass('hide');
                } else {
                    $container.addClass('hide');
                }
            }
            this._initSubpostForm($form);
        },

        onClickToggleSubpostForm: function(e) {
            e.stopPropagation();
            var $btn = $(e.currentTarget);
            var $form = $btn.parents('.thread-subpost-container').find('.thread-subpost-form');
            $form.toggleClass('hide');
            this._initSubpostForm($form);
        },
        onClickEventCancelBtn: function(e) {
            $.post($(e.currentTarget).data('url'), function(result) {
                window.location.reload();
            });
        },
        onClickSubpost: function(e) {
            e.preventDefault();
            var $pageBtn = $(e.currentTarget);

            $.post($pageBtn.attr('href'), function(result) {

                var id = $pageBtn.parents(".thread-post").attr("id");
                $("body,html").animate({
                    scrollTop: $("#" + id).offset().top
                }, 300), !1

                $pageBtn.closest('.thread-subpost-container .thread-subpost-content').html(result);
            });

        },

        _initSubpostForm: function($form) {
            var validator = Validator.query($form);
            if (validator) {
                return;
            }

            validator = new Validator({
                element: $form,
                autoSubmit: false,
                onFormValidated: function(error, results, $form) {
                    if (error) {
                        return false;
                    }

                    var $btn = this.$('[type=submit]').button('loading');
                    $.post($form.attr('action'), $form.serialize(), function(response) {
                        $btn.button('reset');
                        $form.parents('.thread-subpost-container').find('.thread-subpost-list').append(response);
                        $form.find('textarea').val('');
                        var $subpostsNum = $form.parents('.thread-post').find('.subposts-num');
                        $subpostsNum.text(parseInt($subpostsNum.text()) + 1);
                        $subpostsNum.parent().removeClass('hide');

                    }).error(function(data) {
                        $btn.button('reset');
                        data = $.parseJSON(data.responseText);
                        if (data.error) {
                            Notify.danger(data.error.message);
                        } else {
                            Notify.danger('发表回复失败，请重试');
                        }
                    });
                }

            });

            validator.addItem({
                element: $form.find('[name=content]'),
                required: true
            });
        },

        _initPostForm: function() {
            var $list = this.$('.thread-pripost-list');
            var $form = this.$('#thread-post-form');
            var that = this;

            if ($form.length == 0) {
                return;
            }

            var $textarea = $form.find('textarea[name=content]');
            if ($textarea.data('imageUploadUrl')) {
                var editor = CKEDITOR.replace($textarea.attr('id'), {
                    toolbar: 'Thread',
                    filebrowserImageUploadUrl: $textarea.data('imageUploadUrl')
                });
            }

            var validator = new Validator({
                element: $form,
                autoSubmit: false,
                onFormValidated: function(error) {
                    if (error) {
                        return false;
                    }

                    var $btn = this.$('[type=submit]').button('loading');
                    $.post($form.attr('action'), $form.serialize(), function(response) {
                        $btn.button('reset');
                        if ($textarea.data('imageUploadUrl')) {
                            $list.append(response);
                            editor.setData('');
                        } else {
                            $list.prepend(response);
                            $textarea.val('');
                        }

                        var pos = $list.find('li:last-child').offset();
                        $('body').scrollTop(pos.top);
                        that.$('.thread-post-num').text(parseInt(that.$('.thread-post-num').text()) + 1);
                        $list.find('li.empty').remove();
                        $list.closest('.top-reply').removeClass('hidden');

                        //清除附件
                        $('.js-attachment-list').empty();
                        $('.js-attachment-ids').val("");
                        $('.js-upload-file').removeClass('hidden');

                    }).error(function(data) {
                        $btn.button('reset');
                        data = $.parseJSON(data.responseText);
                        if (data.error) {
                            Notify.danger(data.error.message);
                        } else {
                            Notify.danger('发表回复失败，请重试');
                        }
                    });

                }
            });

            validator.addItem({
                element: $textarea,
                required: true
            });

            if (editor) {
                validator.on('formValidate', function(element, event) {
                    editor.updateElement();
                });
            }


        }
    });

    module.exports = ThreadShowWidget;

});