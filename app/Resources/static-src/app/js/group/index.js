import notify from "common/notify";


let initEditor = (options) => {

  var editor = CKEDITOR.replace(options.replace, {
    toolbar: options.toolbar,
    filebrowserImageUploadUrl: $("#" + options.replace).data('imageUploadUrl'),
    allowedContent: true,
    height: 300
  });
  editor.on('change', () => {
    $("#" + options.replace).val(editor.getData());
  });
}


function checkUrl(url) {
  var hrefArray = new Array();
  hrefArray = url.split('#');
  hrefArray = hrefArray[0].split('?');
  return hrefArray[1];
}


var add_btn_clicked = false;


$('#add-btn').click(function () {
  if (!add_btn_clicked) {
    $('#add-btn').button('loading').addClass('disabled');
    add_btn_clicked = true;
  }
  return true;
});

$("#thread-list").on('click', '.uncollect-btn, .collect-btn', function () {
  var $this = $(this);

  $.post($this.data('url'), function () {
    $this.hide();
    if ($this.hasClass('collect-btn')) {
      $this.parent().find('.uncollect-btn').show();
    } else {
      $this.parent().find('.collect-btn').show();
    }
  });
});

$('.attach').tooltip();

if ($('#thread_content').length > 0) {

  initEditor({
    toolbar: 'Thread',
    replace: 'thread_content'
  });


  var $userThreadForm = $("#user-thread-form").validate({
    rules: {
      'thread[title]': {
        required: true,
        minlength: 2,
        maxlength: 100
      },
      'thread[content]': {
        required: true,
        minlength: 2,
      }
    },
    messages: {}
  });

  $userThreadForm.form();
}

if ($('#post-thread-form').length > 0) {


  initEditor({
    toolbar: 'Thread',
    replace: 'post_content'
  });


  var $userThreadForm = $("#post-thread-form").validate({
    rules: {
      'content': {
        required: true,
        minlength: 2,
        visible_character: true
      }
    },
    messages: {},
    submitHandler: function (form) {
      if (!$(form).valid()) {
        return false;
      }
      $.ajax({
        url: $("#post-thread-form").attr('post-url'),
        data: $("#post-thread-form").serialize(),
        cache: false,
        async: false,
        type: "POST",
        dataType: 'text',
        success: function (url) {
          if (url == "/login") {
            window.location.href = url;
            return;
          }
          window.location.reload();
        },
        error: function (data) {
          console.log(1);
          data = data.responseText;
          data = $.parseJSON(data);
          if (data.error) {
            notify('danger', data.error.message);
          } else {
            notify('danger', Translator.trans('发表回复失败，请重试'));
          }
        }
      });
    }
  });
  $userThreadForm.form();

  // var validator_post_content = new Validator({
  //   element: '#post-thread-form',
  //   failSilently: true,
  //   autoSubmit: false,
  //   onFormValidated: function (error) {
  //     if (error) {
  //       return false;
  //     }
  //
  //     $.ajax({
  //       url: $("#post-thread-form").attr('post-url'),
  //       data: $("#post-thread-form").serialize(),
  //       cache: false,
  //       async: false,
  //       type: "POST",
  //       dataType: 'text',
  //       success: function (url) {
  //         if (url == "/login") {
  //           window.location.href = url;
  //           return;
  //         }
  //         window.location.reload();
  //       },
  //       error: function (data) {
  //         console.log(1);
  //         data = data.responseText;
  //         data = $.parseJSON(data);
  //         if (data.error) {
  //           notify('danger', data.error.message);
  //         } else {
  //           notify('danger', Translator.trans('发表回复失败，请重试'));
  //         }
  //       }
  //     });
  //   }
  // });
  // validator_post_content.addItem({
  //   element: '[name="content"]',
  //   required: true,
  //   rule: 'minlength{min:2} visible_character'
  // });

}

if ($('.group-post-list').length > 0) {
  $('.group-post-list').on('click', '.li-reply', function () {
    var postId = $(this).attr('postId');
    var fromUserId = $(this).data('fromUserId');
    $('#fromUserIdDiv').html('<input type="hidden" id="fromUserId" value="' + fromUserId + '">');
    $('#li-' + postId).show();
    $('#reply-content-' + postId).focus();
    $('#reply-content-' + postId).val(Translator.trans('回复 ') + $(this).attr("postName") + ":");

  });

  $('.group-post-list').on('click', '.reply', function () {
    var postId = $(this).attr('postId');
    if ($(this).data('fromUserIdNosub') != "") {

      var fromUserIdNosubVal = $(this).data('fromUserIdNosub');
      $('#fromUserIdNoSubDiv').html('<input type="hidden" id="fromUserIdNosub" value="' + fromUserIdNosubVal + '">')
      $('#fromUserIdDiv').html("");

    }
    ;
    $(this).hide();
    $('#unreply-' + postId).show();
    $('.reply-' + postId).css('display', "");
  });

  $('.group-post-list').on('click', '.unreply', function () {
    var postId = $(this).attr('postId');

    $(this).hide();
    $('#reply-' + postId).show();
    $('.reply-' + postId).css('display', "none");

  });

  $('.group-post-list').on('click', '.replyToo', function () {
    var postId = $(this).attr('postId');
    if ($(this).attr('data-status') == 'hidden') {
      $(this).attr('data-status', "");
      $('#li-' + postId).show();
      $('#reply-content-' + postId).focus();
      $('#reply-content-' + postId).val("");

    } else {
      $('#li-' + postId).hide();
      $(this).attr('data-status', "hidden");
    }


  });

  $('.group-post-list').on('click', '.lookOver', function () {

    var postId = $(this).attr('postId');
    $('.li-reply-' + postId).css('display', "");
    $('.lookOver-' + postId).hide();
    $('.paginator-' + postId).css('display', "");

  });

  $('.group-post-list').on('click', '.postReply-page', function () {

    var postId = $(this).attr('postId');
    $.post($(this).data('url'), "", function (html) {

      $("body,html").animate({
        scrollTop: $("#post-" + postId).offset().top
      }, 300), !1

      $('.reply-post-list-' + postId).replaceWith(html);

    })

  });

  if ($('.thread-post-reply-form').length > 0) {

    var forms = $('.thread-post-reply-form');

    for (var i = forms.length - 1; i >= 0; i--) {
      console.log(222, $(forms[i]).find('textarea').attr('id'));



      // $("#" + $(forms[i]).find('textarea').attr('id')).rules("add", {
      //   visible_character: true
      // });
      var field = $(forms[i]).find('textarea').attr('id');
      var $postReplyForm = $(forms[i]).validate({
        rules: {
          field: {
            required: true,
            minlength: 2,
            visible_character: true
          }
        },
        submitHandler: function (form) {
          if (!$(form).valid()) {
            return false;
          }
          var replyBtn = $(this.element).find('.reply-btn');

          var postId = replyBtn.attr('postId');
          var fromUserIdVal = "";
          if ($('#fromUserId').length > 0) {
            fromUserIdVal = $('#fromUserId').val();
          } else {
            if ($('#fromUserIdNosub').length > 0) {
              fromUserIdVal = $('#fromUserIdNosub').val();
            } else {
              fromUserIdVal = "";
            }
          }


          $.ajax({
            url: $(form).attr('post-url'),
            data: "content=" + $(form).find('textarea').val() + '&' + 'postId=' + postId + '&' + 'fromUserId=' + fromUserIdVal,
            cache: false,
            async: false,
            type: $(form).attr('method'),
            dataType: 'text',
            success: function (url) {
              if (url == "/login") {
                //window.location.href = url;
                return;
              }
             // window.location.reload();
            },
            error: function (data) {
              data = data.responseText;
              data = $.parseJSON(data);
              if (data.error) {
                notify('danger', data.error.message);
              } else {
                notify('danger', Translator.trans('发表回复失败，请重试'));
              }
              replyBtn.button('reset').removeClass('disabled');
            }
          });
        }
      });
    }


   // $postReplyForm.form();

    // var forms = $('.thread-post-reply-form');
    //
    // for (var i = forms.length - 1; i >= 0; i--) {
    //   var form = $(forms[i]);
    //
    //   var validator_threadPost = new Validator({
    //     element: $(form),
    //     failSilently: true,
    //     autoSubmit: false,
    //     onFormValidated: function (error) {
    //       if (error) {
    //         return false;
    //       }
    //       var replyBtn = $(this.element).find('.reply-btn');
    //
    //       var postId = replyBtn.attr('postId');
    //       var fromUserIdVal = "";
    //       if ($('#fromUserId').length > 0) {
    //         fromUserIdVal = $('#fromUserId').val();
    //       } else {
    //         if ($('#fromUserIdNosub').length > 0) {
    //           fromUserIdVal = $('#fromUserIdNosub').val();
    //         } else {
    //           fromUserIdVal = "";
    //         }
    //       }
    //
    //       replyBtn.button('submiting').addClass('disabled');
    //
    //       $.ajax({
    //         url: $(this.element).attr('post-url'),
    //         data: "content=" + $(this.element).find('textarea').val() + '&' + 'postId=' + postId + '&' + 'fromUserId=' + fromUserIdVal,
    //         cache: false,
    //         async: false,
    //         type: "POST",
    //         dataType: 'text',
    //         success: function (url) {
    //           if (url == "/login") {
    //             window.location.href = url;
    //             return;
    //           }
    //           window.location.reload();
    //         },
    //         error: function (data) {
    //           data = data.responseText;
    //           data = $.parseJSON(data);
    //           if (data.error) {
    //             notify('danger', data.error.message);
    //           } else {
    //             notify('danger', Translator.trans('发表回复失败，请重试'));
    //           }
    //           replyBtn.button('reset').removeClass('disabled');
    //         }
    //       });
    //     }
    //   });
    //   validator_threadPost.addItem({
    //     element: '#' + $(form).find('textarea').attr('id'),
    //     required: true,
    //     rule: 'visible_character'
    //   });
    // }
  }

}

if ($('#hasAttach').length > 0) {

  $('.ke-icon-accessory').addClass('ke-icon-accessory-red');

}

if ($('#post-action').length > 0) {

  $('#post-action').on('click', '#closeThread', function () {

    var $trigger = $(this);
    if (!confirm($trigger.attr('title') + '？')) {
      return false;
    }

    $.post($trigger.data('url'), function (data) {

      window.location.href = data;

    });
  })

  $('#post-action').on('click', '#elite,#stick,#cancelReward', function () {

    var $trigger = $(this);

    $.post($trigger.data('url'), function (data) {
      window.location.href = data;
    });
  })

}
if ($('#exit-btn').length > 0) {
  $('#exit-btn').click(function () {
    if (!confirm(Translator.trans('真的要退出该小组？您在该小组的信息将删除！'))) {
      return false;
    }
  })

}
if ($('.actions').length > 0) {

  $('.group-post-list').on('click', '.post-delete-btn,.post-adopt-btn', function () {

    var $trigger = $(this);
    if (!confirm($trigger.attr('title') + '？')) {
      return false;
    }
    $.post($trigger.data('url'), function () {
      window.location.reload();
    });
  })

}


if ($('#group').length > 0) {
  initEditor({
    toolbar: 'Full',
    replace: 'group'
  });


  var $groupForm = $("#user-group-form").validate({
    rules: {
      'group[grouptitle]': {
        required: true,
        minlength: 2,
        maxlength: 100
      },
    },
    messages: {}
  });

  $groupForm.form();
}