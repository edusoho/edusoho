import notify from 'common/notify';

class ThreadShowWidget {
  constructor(prop) {
    this.ele = $(prop.element);
    this.init();
  }

  init() {
    this.initEvent();
    this.initPostForm();
  }
  initEvent() {
    const $node = this.ele;

    console.log($node);

    $node.on('click', '.js-post-more', event => this.onClickPostMore(event));
    $node.on('click', '.js-reply', event => this.onClickReply(event));
    $node.on('click', '.js-post-delete', event => this.onPostDelete(event));
    $node.on('click', '.js-post-up', event => this.onPostUp(event));
    $node.on('click', '[data-role=confirm-btn]', event => this.onConfirmBtn(event));
    $node.on('click', '.js-toggle-subpost-form', event => this.onClickToggleSubpostForm(event));
    $node.on('click', '.js-event-cancel', event => this.onClickEventCancelBtn(event));
    $node.on('click', '.thread-subpost-container .pagination a', event => this.onClickSubpost(event));
  }

  onClickPostMore(e) {
    e.stopPropagation();
    const $btn = $(e.currentTarget);
    $btn.parents('.thread-subpost-moretext').addClass('hide');
    $btn.parents('.thread-post').find('.thread-subpost').removeClass('hide');
    $btn.parents('.thread-post').find('.pagination').removeClass('hide');
  }

  onClickReply(e) {
    console.log('ok');
    e.stopPropagation();
    const $btn = $(e.currentTarget);
    const inSubpost = $btn.parents('.thread-subpost-list').length > 0;
    const $container = $btn.parents('.thread-post').find('.thread-subpost-container');
    const $form = $container.find('.thread-subpost-form');
    if (inSubpost) {
      $form.removeClass('hide');
      const text = Translator.trans('thread.post.reply') + ' @ ' + $btn.parents('.thread-post').data('authorName') + '： ';
      $form.find('textarea').val(text).trigger('focus');

    } else {
      $container.toggleClass('hide');
    }

    if ($btn.html() == Translator.trans('thread.post.reply')) {
      $btn.html(Translator.trans('thread.post.put_away'));
    } else {
      $btn.html(Translator.trans('thread.post.reply'));
    }

    this.initSubpostForm($form);
  }

  onPostDelete(e) {
    e.stopPropagation();
    const $node = this.ele;
    const $btn = $(e.currentTarget);
    if (!confirm(Translator.trans('thread.post.delete_hint'))) {
      return;
    }
    const inSubpost = $btn.parents('.thread-subpost-list').length > 0;

    $.post($btn.data('url'), function () {
      if (inSubpost) {
        const $subpostsNum = $btn.parents('.thread-post').find('.subposts-num');
        $subpostsNum.text(parseInt($subpostsNum.text()) - 1);
      } else {
        $node.find('.thread-post-num').text(parseInt($node.find('.thread-post-num').text()) - 1);
      }
      $($btn.data('for')).remove();
    });
  }

  onPostUp(e) {
    e.stopPropagation();
    const $btn = $(e.currentTarget);
    $.post($btn.data('url'), function (response) {
      if (response.status == 'ok') {
        $btn.find('.post-up-num').text(parseInt($btn.find('.post-up-num').text()) + 1);
      } else if (response.status == 'votedError') {
        notify('danger', Translator.trans('thread.post.like_hint'));
      } else {
        notify('danger', Translator.trans('thread.post.like_error_hint'));
      }
    }, 'json');
  }

  onConfirmBtn(e) {
    e.stopPropagation();
    const $btn = $(e.currentTarget);
    if (!confirm($btn.data('confirmMessage'))) {
      return;
    }
    $.post($btn.data('url'), function () {
      if ($btn.data('afterUrl')) {
        window.location.href = $btn.data('afterUrl');
        return;
      }
      window.location.reload();
    });
  }

  onClickToggleSubpostForm(e) {
    e.stopPropagation();
    const $btn = $(e.currentTarget);
    const $form = $btn.parents('.thread-subpost-container').find('.thread-subpost-form');
    $form.toggleClass('hide');
    this.initSubpostForm($form);
  }

  onClickEventCancelBtn(e) {
    $.post($(e.currentTarget).data('url'), function () {
      window.location.reload();
    });
  }

  onClickSubpost(e) {
    e.preventDefault();
    const $pageBtn = $(e.currentTarget);

    $.post($pageBtn.attr('href'), function (result) {

      const id = $pageBtn.parents('.thread-post').attr('id');
      $('body,html').animate({
        scrollTop: $('#' + id).offset().top
      }, 300), !1;

      $pageBtn.closest('.thread-subpost-container .thread-subpost-content').html(result);
    });
  }

  initPostForm() {
    const $list = $('.thread-pripost-list');
    const $form = $('#thread-post-form');

    if ($form.length == 0) {
      return;
    }

    let editor = null;
    const $textarea = $form.find('textarea[name=content]');
    if ($textarea.data('imageUploadUrl')) {
      editor = CKEDITOR.replace($textarea.attr('id'), {
        toolbar: 'Thread',
        fileSingleSizeLimit: app.fileSingleSizeLimit,
        filebrowserImageUploadUrl: $textarea.data('imageUploadUrl')
      });
      editor.on('change',()=> {
        $textarea.val(editor.getData());
      });
    }
    const $btn = $form.find('[type=submit]');
    $form.validate({
      ajax: true,
      currentDom: $btn,
      rules: {
        content: 'required'
      },
      submitSuccess: function (response) {
        $btn.button('reset');
        if ($textarea.data('imageUploadUrl')) {
          $list.append(response);
          editor.setData('');
        } else {
          $list.prepend(response);
          $textarea.val('');
        }

        const pos = $list.find('li:last-child').offset();
        $('body').scrollTop(pos.top);
        $form.find('.thread-post-num').text(parseInt($form.find('.thread-post-num').text()) + 1);
        $list.find('li.empty').remove();
        $list.closest('.top-reply').removeClass('hidden');

        //清除附件
        $('.js-attachment-list').empty();
        $('.js-attachment-ids').val('');
        $('.js-upload-file').show();
      },
      submitError: function (data) {
        $btn.button('reset');
      }
    });
  }

  initSubpostForm($form) {
    const $btn = $form.find('[type=submit]');
    $form.validate({
      ajax: true,
      currentDom: $btn,
      rules: {
        content: 'required'
      },
      submitSuccess: function (data) {
        if (data.error) {
          notify('danger', data.error);
          return;
        }
        $btn.button('reset');
        $form.parents('.thread-subpost-container').find('.thread-subpost-list').append(data);
        $form.find('textarea').val('');
        const $subpostsNum = $form.parents('.thread-post').find('.subposts-num');
        $subpostsNum.text(parseInt($subpostsNum.text()) + 1);
        $subpostsNum.parent().removeClass('hide');
      },
      submitError: function (data) {
        $btn.button('reset');
        data = $.parseJSON(data.responseText);
        if (data.error) {
          notify('danger', data.error.message);
        } else {
          notify('danger', Translator.trans('thread.post.reply_error_hint'));
        }
      }
    });
  }

  undelegateEvents(element, eventName) {
    this.ele.off(element, eventName);
  }
}

export default ThreadShowWidget;