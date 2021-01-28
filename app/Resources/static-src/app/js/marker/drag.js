import * as Tool from 'common/utils';

class Drag {
  constructor({
    element,
    initMarkerArry = [],
    _video_time = '18',
    messenger = {},
    editbox = '.editbox',
    scalebox = '.js-scalebox',
    timepartnum = '6',
    markers_array = new Array(),
    
    addScale,
    mergeScale,
    updateScale,
    deleteScale,
    updateSeq
  }) {
    this.$element = $(element);
    this.initMarkerArry = initMarkerArry;
    this.markers_array = markers_array;
    this._video_time = _video_time;
    this.messenger = messenger;
    this.editbox = editbox;
    this.timepartnum = timepartnum;

    this.courseId = this.$element.data('course-id');

    this.addScale = addScale;
    this.mergeScale = mergeScale;
    this.updateScale = updateScale;
    this.deleteScale = deleteScale;
    this.updateSeq = updateSeq;

    this.init();
  }

  init() {
    this.initSortable();
    this.initeditbox(false);
    this.initMarker(this.initMarkerArry);
    this.lisentresize();
    this.initPlayer();

    this.initEvent();
  }

  initEvent() {
    this.$element.on('mousedown', '.gruop-lesson-list .drag', event => this.itemDraggable(event));
    this.$element.on('click', '.lesson-list [data-role="question-remove"]', event => this.itemRmove(event));
    this.$element.on('click', '#subject-lesson-list .item-lesson', event => this.stopEvent(event));
    this.$element.on('mousedown', '.scale-blue', event => this.slideScale(event));
    this.$element.on('mouseenter', '.scale-blue', event => this.hoverScale(event));
    // this.$element.on('mousedown', '.scale-blue .item-lesson', event => this.previewQuestion(event));
    this.$element.on('mousedown', '.js-question-preview', event => this.previewMouseDown(event));
  }

  initPlayer() {
    let messenger = this.messenger;
    let _self = this;
    let changeleft = true;
    let $editbox_list = $('#editbox-lesson-list');
    messenger.on('timechange', function (data) {
      if (changeleft) {
        $('.scale-white').css('left', _self.getleft(data.currentTime));
      }
    });
    $('.scale-white').on('mousedown', function (event) {
      changeleft = false;
      $(document).on('mousemove.playertime', function (event) {
        window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
        let left = event.pageX > ($editbox_list.width() + 20) ? ($editbox_list.width() + 20) : event.pageX && event.pageX <= 20 ? 20 : event.pageX;
        $('.scale-white').css('left', left);
        let times = _self.gettime(left);
        messenger.sendToChild({id: 'viewerIframe'}, 'setCurrentTime', {time: times});
      }).on('mouseup.playertime', function (event) {
        $(document).off('mousemove.playertime');
        $(document).off('mousedown.playertime');
        changeleft = true;
        // messenger.sendToChild({ id: 'viewerIframe' }, 'setPlayerPlay');
      });
    });
  }

  initSortable() {
    let _obj = this;
    $('#subject-lesson-list').sortable({
      group: 'no-drop',
      drop: false,
      delay: 500,
      handle: '.drag',
      onDrop($item, container, _super) {
        if ($item.hasClass('item-lesson')) {
          _super($item, container);
          let $_scale = $item.closest('.scale.blue');
          if ($_scale.find('.lesson-list .item-lesson').length > 0) {
            _obj.sortList($_scale.find('.lesson-list'));
            _obj.addScale($_scale, $_scale.find('.time').html(), $_scale.css('left'), $_scale.find('.lesson-list').children().length);
          }
        }
      }
    });
    
    $('#editbox-lesson-list').sortable({
      group: 'no-drop',
      drag: false
    });
  }

  sortList($list) {
    let num = 1;
    $list.find('.item-lesson').each(function () {
      $(this).find('[data-role="sqe-number"]').text(num);
      num ++;
    });
  }

  addScale($marker, time, seq, markers_array) {
    let $marker_item = $marker.find('li' + ':last');
    let markerJson = {
      'id': $marker.attr('id'),
      'second': time,
      'questionMarkers': [{
        'id': $marker_item.attr('id'),
        'seq': seq,
        'questionId': $marker_item.attr('question-id')
      }]
    };
    $.extend(this.addScale(markerJson, $marker, markers_array));
  }

  initeditbox(isresize) {
    let _self = this,
      $_editbox = $(_self.editbox);
    if (isresize) {
      $_editbox.find('.scale.scale-default:visible').each(function () {
        $(this).css('left', _self.getleft(Tool.time2Sec($(this).find('[data-role="scale-time"]').text())));
      });
      $_editbox.find('.scale.scale-blue:visible').each(function () {
        $(this).css('left', _self.getleft(Tool.time2Sec($(this).find('[data-role="scale-blue-time"]').text())));
      });
    } else {
      let _partnum = _self.timepartnum;
      let _parttime = _self._video_time / _partnum;
      for (let i = 0; i <= _partnum; i++) {
        let $new_scale_default = $('[data-role="scale-default"]').clone().css('left', _self.getleft(_parttime * i)).removeClass('hidden').removeAttr('data-role');

        $new_scale_default.find('[data-role="scale-time"]').text(Tool.sec2Time(Math.round(_parttime * i)));
        $('[data-role="scale-default"]').before($new_scale_default);
      }
    }
  }

  initMarker(initMarkerArry) {
    if (initMarkerArry.length > 0) {
      let $scale_blue = $('[data-role="scale-blue"]');
      for (let i = 0; i < initMarkerArry.length; i++) {
        let $new_scale_blue = $scale_blue.clone().css('left', this.getleft(initMarkerArry[i].second)).removeAttr('data-role').removeClass('hidden').attr('id', initMarkerArry[i].id);
        
        let $scale_blue_time = $new_scale_blue.find('[data-role="scale-blue-time"]').text(Tool.sec2Time(initMarkerArry[i].second));
        let questionMarkers = initMarkerArry[i].questionMarkers;
        let $scale_blue_item = $new_scale_blue.find('[data-role="scale-blue-item"]');

        for (let j = 0; j < questionMarkers.length; j++) {
          let $new_scale_blue_item = $scale_blue_item.clone().removeAttr('data-role').attr({ 'question-id': questionMarkers[j].questionId, 'id': questionMarkers[j].id});

          console.log('new_scale_blue_item',  $new_scale_blue_item);

          $new_scale_blue_item
            .data('url', `/course/${this.courseId}/question/${questionMarkers[j].questionId}/marker/preview`)
            .find('[data-role="sqe-number"]').text(j + 1).end()
            .find('[data-role="question-type"]').text(Translator.trans('course.question.type.single_choice')).end()
            .find('[data-role="question-info"]').text(questionMarkers[j]['question'].stem.replace(/<.*?>/ig, ''));
          
          $scale_blue_item.before($new_scale_blue_item);
        }
        
        $scale_blue.after($new_scale_blue);
        $scale_blue_item.remove();
        this.markers_array.push({id: initMarkerArry[i].id, time: initMarkerArry[i].second});
      }
      this.newSortList($(this.scalebox).find('[data-role="scale-blue-list"]'));
    }
  }

  lisentresize() {
    let _self = this;
    $(window).resize(function () {
      _self.initeditbox(true);
    });
  }

  getleft(time) {
    let _width = $('#editbox-lesson-list').width();
    let _totaltime = parseInt(this._video_time);
    let _left = time * _width / _totaltime;
    return _left + 20;
  }

  newSortList($list) {
    let _self = this;
    $list.sortable({
      delay: 500,
      itemSelector: '.item-lesson',
      onDrop: function ($item, container, _super) {
        _super($item, container);
        _self.maskShow(false);
        let $scale_blue = $item.closest('.scale-blue');
        let markerJson = {
          'id': '',
          'questionMarkers': []
        };
        markerJson.id = $scale_blue.attr('id');
        _self.sortList($scale_blue.find('[data-role="scale-blue-list"]'));
        
        $scale_blue.find('li').each(function () {
          let questionMarkers = {
            'id': $(this).attr('id'),
            'seq': $(this).find('[data-role="sqe-number"]').html()
          };
          markerJson.questionMarkers.push(questionMarkers);
        });
        _self._updateSeq($scale_blue, markerJson);
        $scale_blue.removeClass('moveing');
      },
      serialize: function (parent, children, isContainer) {
        return isContainer ? children : parent.attr('id');
      },
      isValidTarget: function ($item, container) {
        _self.maskShow(true);
        $item.closest('.scale-blue').addClass('moveing');
        return true;
      }
    });
  }

  maskShow(show) {
    (show) ? $('[data-role="player-mask"]').removeClass('hidden') : $('[data-role="player-mask"]').addClass('hidden');
  }

  gettime(left) {
    return Math.round((left - 20) * this._video_time / $('#editbox-lesson-list').width());
  }

  itemDraggable(e) {
    var _self = this,
      marker_array = [],
      $merge_marker = null,
      _mover_left = null,
      _move_time = null;

    var $dragingitem = $(e.currentTarget),
      $editbox_list = $('#editbox-lesson-list'),
      $scale_red = $('[data-role="scale-red"]'),
      $scale_red_details = $scale_red.find('[data-role="scale-red-details"]'),
      $dragingitemcopy = $dragingitem.clone().removeClass('drag').addClass('disdragg');

    $dragingitem.after($dragingitemcopy);
    _self.maskShow(true);

    //查询现有的时间刻度：数组保存现有的所有时间刻度 markers_array
    $(document).on('mousemove.dragitem', function (event) {
      if ($editbox_list.find('.placeholder').length <= 0) {
        $scale_red.addClass('hidden');
        $editbox_list.removeClass('highlight');
        return;
      }
      //显示红线
      $editbox_list.addClass('highlight');

      _mover_left = event.pageX > ($editbox_list.width() + 20) ? ($editbox_list.width() + 20) : event.pageX && event.pageX <= 20 ? 20 : event.pageX;
      _move_time = _self.gettime(_mover_left);
      $scale_red_details.text(Tool.sec2Time(_move_time));
      $scale_red.removeClass('hidden').css('left', _mover_left);

      //markers_array靠近的元素提示合并,
      if (_self.markers_array.length > 0) {
        $('.scale-blue').removeClass('highlight');
        marker_array = [];
        $merge_marker = null;
        for (let i in _self.markers_array) {
          if (Math.abs(_self.markers_array[i].time - _move_time) <= 5) {
            marker_array = [{
              id: _self.markers_array[i].id,
              time: _self.markers_array[i].time
            }];
            //靠近的元素刻度线高亮条件ID
            $merge_marker = $('.scale-blue[id=' + _self.markers_array[i].id + ']').addClass('highlight');
            return;
          }
        }
      }
    }).on('mouseup.dragitem', function (event) {
      $(document).off('mousemove.dragitem');
      $(document).off('mouseup.dragitem');
      _self.maskShow(false);
      $scale_red.addClass('hidden');
      $editbox_list.removeClass('highlight');
      //未拖动
      var $moveeditem = $editbox_list.find('.item-lesson');
      if ($moveeditem.length <= 0) {
        $scale_red.addClass('hidden');
        $editbox_list.removeClass('highlight');
        $dragingitemcopy.remove();
        return;
      }
      //标记已存在，新增题目
      if (marker_array.length > 0) {
        var $list = $merge_marker.find('[data-role="scale-blue-list"]');
        $moveeditem.appendTo($list).find('[data-role="sqe-number"]').text($list.children().length);
        _self._addScale($merge_marker, marker_array[0].time, $list.children().length, _self.markers_array);
        $merge_marker.removeClass('highlight');
        _self.newSortList($list);
      } else { //新增标记和题目
        var $scale_blue = $('[data-role="scale-blue"]'),
          $new_scale_blue = $scale_blue.clone().css('left', _mover_left).removeAttr('data-role'),
          $scale_blue_list = $new_scale_blue.find('[data-role="scale-blue-list"]'),
          $scale_blue_time = $new_scale_blue.find('[data-role="scale-blue-time"]').text(Tool.sec2Time(_move_time));
        $scale_blue_list.children().remove();
        $scale_blue_list.append($moveeditem);
        $scale_blue.after($new_scale_blue);
        _self._addScale($new_scale_blue, _move_time, 1, _self.markers_array);
      }
    });
  }

  itemRmove(e) {
    e.stopPropagation();
    let $this = $(e.currentTarget);
    let $list = $this.closest('[data-role="scale-blue-list"]'),
      $marker_question = $this.closest('li'),
      $marker = $this.closest('.scale-blue');
    this._deleteScale($marker, $marker_question, $list.children().length, this.markers_array);
  }

  stopEvent(e) {
    e.stopPropagation();
  }

  slideScale(e) {
    let _self = this,
      marker_array = [],
      $merge_marker = null,
      _mover_left = null,
      _move_time = null;

    let $moveitem = $(e.currentTarget),
      $editbox_list = $('#editbox-lesson-list'),
      _oldleft = $moveitem.css('left');
    _self.maskShow(true);
    $('.marker-manage').addClass('slideing');
    $moveitem.addClass('moveing');
    $(document).on('mousemove.slide', function (event) {
      window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
      _mover_left = event.pageX > ($editbox_list.width() + 20) ? ($editbox_list.width() + 20) : event.pageX && event.pageX <= 20 ? 20 : event.pageX;
      _move_time = Math.round((_mover_left - 20) * _self._video_time / $editbox_list.width());
      $moveitem.css('left', _mover_left);
      $moveitem.find('[data-role="scale-blue-time"]').text(Tool.sec2Time(_move_time));

      if (_self.markers_array.length > 0) {
        $('.scale-blue').removeClass('highlight');
        marker_array = [];
        $merge_marker = null;
        for (let i in _self.markers_array) {
          if (Math.abs(_self.markers_array[i].time - _move_time) <= 5 && $moveitem.attr('id') != _self.markers_array[i].id) {
            marker_array = [{
              id: _self.markers_array[i].id,
              time: _self.markers_array[i].time
            }];
            //靠近的元素刻度线高亮条件ID
            $merge_marker = $('.scale-blue[id=' + _self.markers_array[i].id + ']').addClass('highlight');
            return;
          }
        }
      }
    }).on('mouseup.slide', function (event) {
      $(document).off('mousemove.slide');
      $(document).off('mouseup.slide');
      _self.maskShow(false);
      $moveitem.removeClass('moveing');
      $('.marker-manage').removeClass('slideing');
      if (marker_array.length > 0) {
        var $list = $merge_marker.find('[data-role="scale-blue-list"]');
        $list.append($moveitem.find('[data-role="scale-blue-list"]').children());
        _self.sortList($list);
        $merge_marker.removeClass('highlight');
        _self._mergeScale($moveitem, $merge_marker, _self.markers_array);
      } else {
        //新增
        _self._updateScale($moveitem, _move_time);
      }
    });
  }

  hoverScale(e) {
    let $this = $(e.currentTarget);
    if ($this.offset().left - 20 < 110) {
      $this.find('.scale-details').css('margin-left', '-' + ($this.offset().left - 20) + 'px');
    } else {
      $this.find('.scale-details').css('margin-left', '-110px');
    }
  }

  // previewQuestion(e) {
  //   e.stopPropagation();
  //   let $this = $(e.currentTarget), url = $this.data('url');
  //   if (url) {
  //     let imgUrl = app.config.loading_img_path;
  //     let $target = $($this.data('target'));
  //     let $loadingImg = "<img src='" + imgUrl + "' class='modal-loading' style='z-index:1041;width:60px;height:60px;position:absolute;top:50%;left:50%;margin-left:-30px;margin-top:-30px;'/>";
  //     $target.html($loadingImg);
  //     $target.load(url);
  //   }
  // }

  previewMouseDown(e) {
    //阻止默认事件，父层的拖动
    e.stopPropagation();
  }

  _addScale($marker, time, seq, markers_array) {
    let $marker_item = $marker.find('li' + ':last');
    let markerJson = {
      'id': $marker.attr('id'),
      'second': time,
      'questionMarkers': [{
        'id': $marker_item.attr('id'),
        'seq': seq,
        'questionId': $marker_item.attr('question-id')
      }]
    };
    $.extend(this.addScale(markerJson, $marker, markers_array));
  }

  _mergeScale($marker, $merg_marker, markers_array) {
    // 合并时后台去处理顺序，被合并数按序号依次增加
    let markerJson = {
      'id': $marker.attr('id'),
      'merg_id': $merg_marker.attr('id')
    };
    $.extend(this.mergeScale(markerJson, $marker, $merg_marker, markers_array));
  }

  _updateScale($marker, time) {
    let markerJson = {
      'id': $marker.attr('id'),
      'second': time,
    };
    $.extend(this.updateScale(markerJson, $marker));
  }

  _deleteScale($marker, $marker_question, marker_questions_num, markers_array) {
    console.log('id', $marker, $marker.attr('id'));
    let markerJson = {
      'id': $marker.attr('id'),
      'questionMarkers': [{
        'id': $marker_question.attr('id'),
        'seq': $marker_question.find('[data-role="sqe-number"]').html(),
        'questionId': $marker_question.attr('question-id')
      }]
    };
    $.extend(this.deleteScale(markerJson, $marker, $marker_question, marker_questions_num, markers_array));
  }

  _updateSeq($scale, markerJson) {
    $.extend(this.updateSeq($scale, markerJson));
  }
  
}

export default Drag;