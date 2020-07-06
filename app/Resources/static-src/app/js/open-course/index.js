import ThreadShowWidget from 'app/js/thread/thread-show';
import notify from 'common/notify';

const main = {
  init: function () {
    main.onClickThumb();
    main.onClickfavorite();
    main.removeMask();
    main.onClickReplay();
  },
  onClickThumb: function () {
    $('.js-like-num').on('click', function () {
      var self = $(this);
      var url, action;
      self.off('click').css('cursor', 'default');
      url = self.data('likeUrl');
      action = 'addClass';
      $.post(url, function (res) {
        var $number = self.parent().next();
        var currentNum = $number.html();

        $number.html(parseInt(currentNum) + 1);

        self.parent()[action]('active');
      });
    });
  },
  onClickfavorite: function () {
    $('.js-favorite-num').on('click', function () {
      var self = $(this);

      var isFavorited = self.parent().hasClass('active');
      var text;

      if (isFavorited) {
        $.ajax({
          type: "DELETE",
          beforeSend: function (request) {
            request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
            request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
          },
          url: '/api/favorite?' + 'targetType=' + $(this).data('targetType') + '&targetId=' + $(this).data('targetId'),
          success: function (resp) {
            self.parent().next().html(Translator.trans('open_course.collect'));
            self.parent().removeClass('active');
          },
          error: function () {
            $('#modal').html();
            $('#modal').load(self.data('loginUrl'));
            $('#modal').modal('show');
          }
        });
      } else {
        $.ajax({
          type: "POST",
          data: {
            'targetType': $(this).data('targetType'),
            'targetId': $(this).data('targetId'),
          },
          beforeSend: function (request) {
            request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
            request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
          },
          url: '/api/favorite',
          success: function (resp) {
            self.parent().next().html(Translator.trans('open_course.collected'));
            self.parent().addClass('active');
          },
          error: function () {
            $('#modal').html();
            $('#modal').load(self.data('loginUrl'));
            $('#modal').modal('show');
          }
        });
      }
    });
  },
  //点击ES直播公开课回放
  onClickReplay: function () {
    $('.js-play-es-live').on('click', function () {
      var replayUrl = $(this).data('url');
      var html = '<iframe src=\'' + replayUrl + '\' name=\'viewerIframe\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
      $('.open-course-views').html(html);
    });
  },
  isEsVedio: function () {
    if ($('#lesson-preview-player').html() == '') {
      $('.embed-responsive-16by9').addClass('masks');
    }
  },
  removeMask: function () {
    setTimeout(main.isEsVedio, 1500);
  }
};

main.init();

if (!$('#open-course-comment').find('[type=submit]').hasClass('disabled')) {
  var threadShowWidget = new ThreadShowWidget({
    element: '#open-course-comment',
  });
}
