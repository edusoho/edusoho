import notify from 'common/notify';
import ThreadShowWidget from 'app/js/thread/thread-show';

let $form = $('#review-form');

let validator = $form.validate({
  rules: {
    rating: {
      required: true,
      'raty_star': true,
    },
    content: {
      required: true,
    }
  },
  messages: {
    rating: {
      required: Translator.trans('course.marking_hint'),
    }
  }
});

if ($form.length > 0) {
  $form.find('.rating-btn').raty({
    path: $form.find('.rating-btn').data('imgPath'),
    hints: [Translator.trans('course.marking_one_star'), Translator.trans('course.marking_two_star'), Translator.trans('course.marking_three_star'), Translator.trans('course.marking_four_star'), Translator.trans('course.marking_five_star')],
    score: function () {
      return $(this).attr('data-rating');
    },
    click: function (score, event) {
      $form.find('[name=rating]').val(score);
    }
  });

  $form.find('.js-btn-save').on('click', function () {
    let self = $(this);
    if (validator.form()) {
      self.button('loading');

      $.ajax({
        type: "POST",
        beforeSend: function (request) {
          request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
          request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
        },
        url: '/api/review',
        data: $form.serialize()
          + '&targetType=' + $('.js-btn-save').data('targetType')
          + '&targetId=' + $('.js-btn-save').data('targetId'),
        success: function () {
          $form.find('.js-review-remind').fadeIn('fast', function () {
            window.location.reload();
          });
        },
        error: function () {
          self.button('reset');
        }
      });
    }
  });


  $('.js-hide-review-form').on('click', function () {
    $(this).hide();
    $('.js-show-review-form').show();
    $form.hide();
  });

  $('.js-show-review-form').on('click', function () {
    $(this).hide();
    $('.js-hide-review-form').show();
    $form.show();
  });

}

let $reviews = $('.js-reviews');

$('.js-reviews').hover(function () {
  let $fullLength = $(this).find('.full-content').text().length;

  if ($fullLength > 100 && $(this).find('.short-content').is(':hidden') == false) {
    $(this).find('.show-full-btn').show();
  } else {
    $(this).find('.show-full-btn').hide();
  }
});

$reviews.on('click', '.show-full-btn', function () {
  let $review = $(this).parents('.media');
  $review.find('.short-content').slideUp('fast', function () {
    $review.find('.full-content').slideDown('fast');
  });
  $(this).hide();
  $review.find('.show-short-btn').show();
});

$reviews.on('click', '.show-short-btn', function () {
  let $review = $(this).parents('.media');
  $review.find('.full-content').slideUp('fast', function () {
    $review.find('.short-content').slideDown('fast');
  });
  $(this).hide();
  $review.find('.show-full-btn').show();
});

if ($('.js-reviews').length > 0) {
  let threadShowWidget = new ThreadShowWidget({
    element: '.js-reviews',
  });

  threadShowWidget.undelegateEvents('.js-toggle-subpost-form', 'click');
  threadShowWidget.undelegateEvents('.js-reply', 'click');
  threadShowWidget.undelegateEvents('.js-post-delete', 'click');

  $('.js-toggle-subpost-form').click(function (e) {
    e.stopPropagation();
    let postNum = $(this).closest('.thread-subpost-container').find('.thread-subpost-content .thread-subpost-list .thread-subpost').length;

    if (postNum >= 5) {
      notify('danger', Translator.trans('course.manage.post_limit_hint'));
      return;
    }
    let $form = $(this).parents('.thread-subpost-container').find('.thread-subpost-form');
    $form.toggleClass('hide');
    threadShowWidget.initSubpostForm($form);

    submitPostForm($form);
  });

  $('.js-reply').on('click', function (e) {
    e.stopPropagation();
    let $btn = $(e.currentTarget);
    let inSubpost = $btn.parents('.thread-subpost-list').length > 0;
    let $container = $btn.parents('.thread-post').find('.thread-subpost-container');
    let $form = $container.find('.thread-subpost-form');
    if (inSubpost) {
      $form.removeClass('hide');
      let text = Translator.trans('thread.post.reply') + ' @ ' + $btn.parents('.thread-post').data('authorName') + '： ';
      $form.find('textarea').val(text).trigger('focus');
    } else {
      $container.toggleClass('hide');
    }

    if ($btn.html() == Translator.trans('thread.post.reply')) {
      $btn.html(Translator.trans('thread.post.put_away'));
    } else {
      $btn.html(Translator.trans('thread.post.reply'));
    }

    threadShowWidget.initSubpostForm($form);

    submitPostForm($form);
  });

  $('.js-reviews').on('click', '.js-delete-post', function (e) {
    const $node = this.ele;
    const $btn = $(e.currentTarget);

    if (!confirm(Translator.trans('thread.post.delete_hint'))) {
      return;
    }

    let inSubpost = $btn.parents('.thread-subpost-list').length > 0;

    $.ajax({
      type: "DELETE",
      beforeSend: function (request) {
        request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
        request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
      },
      url: '/api/review/' + $btn.data('reviewId'),
      success: function (res) {
        if (inSubpost) {
          let $subpostsNum = $btn.parents('.thread-post').find('.subposts-num');
          $subpostsNum.text(parseInt($subpostsNum.text()) - 1);
        } else {
          $node.find('.thread-post-num').text(parseInt($node.find('.thread-post-num').text()) - 1);
        }
        $($btn.data('for')).remove();
        notify('success', Translator.trans('site.delete_success_hint'));
      },
      error: function () {
      }
    });
  });
}

function submitPostForm($form) {
  $('.js-btn-save-post').off('click').on('click', function (e) {
    e.stopPropagation();

    if ($form.validate().form()) {
      let self = $(this);
      self.button('loading');

      $.ajax({
        type: "POST",
        beforeSend: function (request) {
          request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
          request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
        },
        url: '/api/review/' + self.data('targetId') + '/post',
        data: $form.serialize(),
        success: function (res) {
          self.button('reset');
          $form.parents('.thread-subpost-container').find('.thread-subpost-list').append(res.template);
          $form.find('textarea').val('');

          let $subpostsNum = $form.parents('.thread-post').find('.subposts-num');
          $subpostsNum.text(parseInt($subpostsNum.text()) + 1);
          $subpostsNum.parent().removeClass('hide');
        },
        error: function () {
          self.button('reset');
        }
      });
    }
  });
}



