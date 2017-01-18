import notify from "common/notify";
import { initThread,initThreadReplay } from './thread-opreate';

initThread();
initThreadReplay();


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


// if ($('#group').length > 0) {
//   initEditor({
//     toolbar: 'Full',
//     replace: 'group'
//   });


//   var $groupForm = $("#user-group-form").validate({
//     rules: {
//       'group[grouptitle]': {
//         required: true,
//         minlength: 2,
//         maxlength: 100
//       },
//     },
//     messages: {}
//   });

//   $groupForm.form();
// }