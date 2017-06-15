import './video';
import messenger from './messenger';
import Drag from './drag';
import Cookies from 'js-cookie';

let drag = (initMarkerArry, mediaLength, messenger) => {
  let drag = new Drag({
    element: '#task-dashboard',
    initMarkerArry: initMarkerArry,
    _video_time: mediaLength,
    messenger: messenger,
    addScale(markerJson, $marker, markers_array) {
      var url = $('.js-pane-question-content').data('queston-marker-add-url');
      var param = {
        markerId: markerJson.id,
        second: markerJson.second,
        questionId: markerJson.questionMarkers[0].questionId,
        seq: markerJson.questionMarkers[0].seq
      };
      $.post(url, param, function (data) {
        if (data.id == undefined) {
          return;
        }
        //新增时间刻度
        if (markerJson.id == undefined) {
          $marker.attr('id', data.markerId);
          markers_array.push({id: data.markerId, time: markerJson.second});
          //排序
        }
        $marker.removeClass('hidden');
        $marker.find('.item-task[question-id=' + markerJson.questionMarkers[0].questionId + ']').attr('id', data.id);
      });
      return markerJson;
    },
    mergeScale(markerJson, $marker, $merg_emarker, markers_array) {
      var url = $('.js-pane-question-content').data('marker-merge-url');
      $.post(url, {
        sourceMarkerId: markerJson.id,
        targetMarkerId: markerJson.merg_id
      }, function (data) {
        $marker.remove();
        for (i in markers_array) {
          if (markers_array[i].id == markerJson.id) {
            markers_array.splice(i, 1);
            break;
          }
        }
      });
      return markerJson;
    },
    updateScale(markerJson, $marker) {
      var url = $('.js-pane-question-content').data('marker-update-url');
      var param = {
        id: markerJson.id,
        second: markerJson.second
      };
      if(markerJson.second){
        $.post(url, param, function (data) {
        });
      }else{
        console.log('do not need upgrade scale...');
      }
      return markerJson;
    },
    deleteScale(markerJson, $marker, $marker_question, marker_questions_num, markers_array) {
      var url = $('.js-pane-question-content').data('queston-marker-delete-url');
      $.post(url, {
        questionId: markerJson.questionMarkers[0].id
      }, function (data) {
        $marker_question.remove();
        $('#subject-lesson-list').find('.item-task[question-id=' + markerJson.questionMarkers[0].questionId + ']').removeClass('disdragg').addClass('drag');
        if ($marker.find('[data-role="scale-blue-list"]').children().length <= 0) {
          $marker.remove();
          for (i in markers_array) {
            if (markers_array[i].id == $marker.attr('id')) {
              markers_array.splice(i, 1);
              break;
            }
          }
        } else {
          //剩余排序
          sortList($marker.find('[data-role="scale-blue-list"]'));
        }
      });
    },
    updateSeq($scale, markerJson) {
      if (markerJson == undefined || markerJson.questionMarkers == undefined || markerJson.questionMarkers.length == 0) {
        return;
      }

      var url = $('.js-pane-question-content').data('queston-marker-sort-url');
      param = new Array();

      for (var i = 0; i < markerJson.questionMarkers.length; i++) {
        param.push(markerJson.questionMarkers[i].id);
      }

      $.post(url, {questionIds: param});
    }
  })

  return drag;
}

class Manage {
  constructor({ formElement }) {
    this.$form = $(formElement);
    this.init();
  }

  init() {
    this.initData();
    this.validator();
    this.initDrag();
    this.initEvent();
  }

  initData() {
    let count = parseInt((document.body.clientHeight - 350) / 50) > 0 ? parseInt((document.body.clientHeight - 350) / 50) : 1;
    
    $.post(this.$form.attr('action'), this.$form.serialize() + '&pageSize=' + count, (response) => {
      $('#subject-lesson-list').html(response);
      $('[data-toggle="popover"]').popover();
      if (!Cookies.get("MARK-MANGE-GUIDE")) {
        this.initIntro();
      } else {
        this.initDrag();
        $('#step-1').removeClass('introhelp-icon-help');
      }
      Cookies.set("MARK-MANGE-GUIDE", 'true', {expires: 360, path: "/"});
      this.$form.data('pageSize', count);
    });
  }

  initIntro() {
    $('.js-introhelp-overlay').removeClass('hidden');
    $('.show-introhelp').addClass('show');

    var $img = $('.js-introhelp-img img'),
        img = document.createElement('img'),
        imgheight = $(window).height() - $img.offset().top - 80;
        
    img.src = $img.attr('src');
    left = imgheight * img.width / img.height / 2 + 50;
    $img.height(imgheight);
    $('.js-introhelp-img').css('margin-left', '-' + left + 'px');
  }

  validator() {
    // @TODO 添加表单验证
    // @TODO 删除题目锚点有问题
    // @TODO 题目锚点初始化名称有问题
  }

  initEvent() {
    $(".js-marker-manage-content").on('change', 'select[name=target]', event => this.onChangeSelect(event));
    $(".js-marker-manage-content").on('click', '.js-question-preview', event => this.onQuestionPreview(event));
    $(".js-marker-manage-content").on('click', '.js-more-questions', event => this.onMoreQuestion(event));
    $(".js-marker-manage-content").on('click', '.js-close-introhelp', event => this.onCloseHelp(event));
  }

  onChangeSelect(e) {
    let count = $('.js-mark-from').data('pageSize');
    $.post(this.$form.attr('action'), this.$form.serialize() + '&pageSize=' + count, function (response) {
      $('#subject-lesson-list').html(response);
    });
  }

  onQuestionPreview(e) {
    $.get($(e.currentTarget).data('url'), function (response) {
      $('.modal').modal('show');
      $('.modal').html(response);
    })
  }

  onMoreQuestion(e) {
    let target = $('select[name=target]');
    let $this = $(e.currentTarget).hide().parent().addClass('loading'),
        $list = $('#subject-lesson-list').css('max-height', $('#subject-lesson-list').height()),
        getpage = parseInt($this.data('current-page')) + 1,
        lastpage = $this.data('last-page');

    $.post($this.data('url') + getpage, {'target': target.val(), 'pageSize': $('.js-mark-from').data('pageSize')}, function (response) {
      $this.remove();
      $list.append(response).animate({scrollTop: 40 * ($list.find('.item-task').length + 1)});
      if (getpage == lastpage) {
        $('.js-more-questions').parent().remove();
      }
    });
  }

  onCloseHelp(e) {
    let $this = $(e.currentTarget);
    $this.closest('.show-introhelp').removeClass('show-introhelp');
    if ($('.show-introhelp').height() <= 0) {
      $('.js-introhelp-overlay').addClass('hidden');
      this.initDrag();
    }
  }

  initDrag() {
    let initMarkerArry = [];
    let mediaLength = 30;

    $.ajax({
      type: "get",
      url: $('.js-pane-question-content').data('marker-metas-url'),
      cache: false,
      async: false,
      success: function (data) {
        initMarkerArry = data.markersMeta;
        mediaLength = data.videoTime;
      }
    });
    drag(initMarkerArry, mediaLength, messenger)
  }
}

export default Manage;