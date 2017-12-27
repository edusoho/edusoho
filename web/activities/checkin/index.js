
if ($('.js-homepage').length > 0) {
  getCourse($('input[name=course-id]', window.parent.document).val());

  setInterval(function () {
    getSignStatus($('input[name=course-id]', window.parent.document).val());
  }, 1000);

}

function getSignStatus(courseId)
{
  $.ajax({
    url: 'http://www.esdev.com/ltc/status?courseId='+courseId,
    method: 'GET',
    beforeSend: function(request) {
      request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
      request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]', window.parent.document).attr('content'));
    },
    success: function (resp) {
      $('.js-sign-list').html('');
      $.each(JSON.parse(resp), function (index, item) {
        $('.js-sign-list').append('<li>'+item['nickname']+'</li>');
      });
      $('.js-sign-number').html(JSON.parse(resp).length);
    }
  });
}

function getCourse(courseId)
{
  $.ajax({
    url: 'http://www.esdev.com/api/courses/'+courseId,
    method: 'GET',
    beforeSend: function(request) {
      request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
      request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]', window.parent.document).attr('content'));
    },
    success: function (resp) {
      $('.js-student-number').html(resp.studentNum);
    }
  });
}

isOK = false;
function getUser(nickname) {
  $.ajax({
    url: 'http://www.esdev.com/api/users/'+nickname+'?identifyType=nickname',
    method: 'GET',
    async: false,
    beforeSend: function(request) {
      request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
      request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]', window.parent.document).attr('content'));
    },
    success: function (resp) {
      if ('id' in resp) {
        isOK = true;
      }
    }
  });
}

$('#sign-form').submit(function () {
  var nickname = $('#nickname').val();
  getUser(nickname);
  if (isOK) {
    alert('签到成功');
  } else {
    alert('用户不存在，重新输入');
    return false;
  }
});