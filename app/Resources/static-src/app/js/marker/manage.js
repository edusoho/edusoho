import './video';
import messenger from './messenger';
import Drag from './drag';
import Cookies from 'js-cookie';
import { htmlEscape } from 'app/common/unit.js';

let drag = (initMarkerArry, mediaLength, messenger) => {
  let drag = new Drag({
    element: '#task-dashboard',
    initMarkerArry: initMarkerArry,
    _video_time: mediaLength,
    messenger: messenger,
    addScale(markerJson, $marker, markers_array) {
      let url = $('.js-pane-question-content').data('queston-marker-add-url');
      let param = {
        markerId: markerJson.id,
        second: markerJson.second,
        questionId: markerJson.questionMarkers[0].questionId,
        seq: markerJson.questionMarkers[0].seq
      };
      $.post(url, param, function (data) {
        if (data.id === undefined) {
          return;
        }
        //新增时间刻度
        if (markerJson.id === undefined) {
          $marker.attr('id', data.markerId);
          markers_array.push({id: data.markerId, time: markerJson.second});
          //排序
        }
        $marker.removeClass('hidden');
        $marker.find('.item-lesson[question-id=' + markerJson.questionMarkers[0].questionId + ']').attr('id', data.id);
      });
      return markerJson;
    },
    mergeScale(markerJson, $marker, $merge_marker, markers_array) {
      let url = $('.js-pane-question-content').data('marker-merge-url');
      $.post(url, {
        sourceMarkerId: markerJson.id,
        targetMarkerId: markerJson.merg_id
      }, function (data) {
        $marker.remove();
        for (let i in markers_array) {
          if (markers_array[i].id == markerJson.id) {
            markers_array.splice(i, 1);
            break;
          }
        }
      });
      return markerJson;
    },
    updateScale(markerJson, $marker) {
      let url = $('.js-pane-question-content').data('marker-update-url');
      let param = {
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
      let url = $('.js-pane-question-content').data('queston-marker-delete-url');
      $.post(url, {
        questionId: markerJson.questionMarkers[0].id
      }, function (data) {
        $marker_question.remove();
        console.log(markerJson.questionMarkers[0].questionId, 'questionId');
        $('#subject-lesson-list').find('.item-lesson[question-id=' + markerJson.questionMarkers[0].questionId + ']').removeClass('disdragg').addClass('drag');
        if ($marker.find('[data-role="scale-blue-list"]').children().length <= 0) {
          $marker.remove();
          for (let i in markers_array) {
            if (markers_array[i].id == $marker.attr('id')) {
              markers_array.splice(i, 1);
              break;
            }
          }
        } else {
          //剩余排序
          console.log('drag', drag);
          drag.sortList($marker.find('[data-role="scale-blue-list"]'));
        }
      });
    },
    updateSeq($scale, markerJson) {
      if (markerJson === undefined || markerJson.questionMarkers === undefined || markerJson.questionMarkers.length === 0) {
        return;
      }

      let url = $('.js-pane-question-content').data('queston-marker-sort-url');
      let param = [];

      for (let i = 0; i < markerJson.questionMarkers.length; i++) {
        param.push(markerJson.questionMarkers[i].id);
      }

      $.post(url, {questionIds: param});
    }
  });

  return drag;
};

class Manage {
  constructor(options) {
    this.$form = $(options.formSelect);
    this.$marker = $(options.markerSelect);
    this.questionBankSelector = $('#mark-form-bankId');
    this.questionCategorySelector = $('#mark-form-categoryId');
    this.init();
  }

  init() {
    this.initData();
    this.initEvent();
    this.initQuestionBankSelector();
    this.disableQuestionCategorySelector();
  }

  initData() {
    let count = parseInt((document.body.clientHeight - 350) / 50) > 0 ? parseInt((document.body.clientHeight - 350) / 50) : 1;

    $.post(this.$form.attr('action'), this.$form.serialize() + '&pageSize=' + count, (response) => {
      $('#subject-lesson-list').html(response);
      $('[data-toggle="popover"]').popover();
      if (!Cookies.get('MARK-MANGE-GUIDE')) {
        this.initIntro();
      } else {
        this.initDrag();
        $('#step-1').removeClass('introhelp-icon-help');
      }
      Cookies.set('MARK-MANGE-GUIDE', 'true', {expires: 360, path: '/'});
      this.$form.data('pageSize', count);
    });
  }

  initIntro() {
    $('.js-introhelp-overlay').removeClass('hidden');
    $('.show-introhelp').addClass('show');

    let $img = $('.js-introhelp-img img'),
      img = document.createElement('img'),
      imgHeight = $(window).height() - $img.offset().top - 80;

    img.src = $img.attr('src');
    let left = imgHeight * img.width / img.height / 2 + 50;
    $img.height(imgHeight);
    $('.js-introhelp-img').css('margin-left', '-' + left + 'px');
  }

  initEvent() {
    this.$marker.on('click', '.js-question-preview', event => this.onQuestionPreview(event));
    this.$marker.on('click', '.js-more-questions', event => this.onMoreQuestion(event));
    this.$marker.on('click', '.js-close-introhelp', event => this.onCloseHelp(event));
    this.$marker.on('click', '#mark-form-submit', event => this.onFormSubmit(event));
    this.$marker.on('change', '#mark-form-bankId', event => this.onChangeSelect(event));
    this.$marker.on('change', '#mark-form-categoryId', event => this.onChangeSelect(event));
    this.$marker.on('keydown', '#mark-form-keyword', event => this.onFormAutoSubmit(event));
  }

  initQuestionBankSelector() {
    this.questionBankSelector.select2({
      treeview: true,
      dropdownAutoWidth: true,
      treeviewInitState: 'collapsed',
      placeholderOption: 'first',
      formatResult: function(item) {
        let text = htmlEscape(item.text);
        if (!item.id) {
          return text;
        }
        return `<div class="select2-result-text"><span class="select2-match"></span><span><i class="es-icon es-icon-tiku"></i>${text}</span></div>`;
      },
      dropdownCss: {
        width: ''
      },
    });
  }

  disableQuestionCategorySelector() {
    this.questionCategorySelector.select2({
      'disable': true,
    });
  }

  enableQuestionCategorySelector() {
    this.questionCategorySelector.select2({
      treeview: true,
      dropdownAutoWidth: true,
      treeviewInitState: 'collapsed',
      placeholderOption: 'first',
    });
  }

  onFormAutoSubmit(event) {
    if (event.keyCode === 13) {
      event.preventDefault();
      this.onFormSubmit(event);
    }
  }

  onFormSubmit(e) {
    let validator = this.$form.validate();

    if (validator.form()) {
      let count = this.$form.data('pageSize');
      $.post(this.$form.attr('action'), this.$form.serialize() + '&pageSize=' + count, function (response) {
        $('#subject-lesson-list').html(response);
      });

      let $target = $(e.target);
      let url = $target.data('url');
      if (url === undefined) {
        return;
      }
      let $categorySelect = $('#mark-form-categoryId');
      let option = `<option value="0">${Translator.trans('question.marker_question.select_question_category')}</option>`;
      let bankId = $target.val();
      if (!parseInt(bankId)) {
        $categorySelect.html(option);
        this.disableQuestionCategorySelector();
        return;
      }

      let self = this;
      $.post(url, {bankId: bankId}, function (response) {
        option += '<option value="0">无</option>';
        $.each(response, function (index, category) {
          let space = category.depth > 1 ? '　'.repeat(category.depth-1) : '';
          option += `<option value="${category.id}">${space}${category.name}</option>`;
        });
        $categorySelect.html(option);
        self.enableQuestionCategorySelector();
      });
    }
  }

  onChangeSelect(e) {
    this.onFormSubmit(e);
  }

  onQuestionPreview(e) {
    $.get($(e.currentTarget).data('url'), function (response) {
      let $modal = $('.modal').modal('show');
      $modal.html(response);
    });
  }

  onMoreQuestion(e) {
    let $this = $(e.currentTarget).hide().parent().addClass('loading'),
      $list = $('#subject-lesson-list').css('max-height', $('#subject-lesson-list').height()),
      page = parseInt($this.data('current-page')) + 1,
      lastPage = parseInt($this.data('last-page'));
    let data = {
      'bankId': $('select[name=bankId]').val(),
      'categoryId': $('select[name=categoryId]').val(),
      'keyword': $('[name=keyword]').val(),
      'pageSize': this.$form.data('pageSize'),
    };

    $.post($this.data('url') + page, data, function(response) {
      $this.remove();
      $list.append(response).animate({scrollTop: 40 * ($list.find('.item-lesson').length + 1)});
      if (page === lastPage) {
        $('.js-more-questions').parent().remove();
      }
    });
  }

  onCloseHelp(e) {
    let $this = $(e.currentTarget);
    $this.closest('.show-introhelp').removeClass('show-introhelp');
    if ($('.show-introhelp').length <= 0) {
      $('.js-introhelp-overlay').addClass('hidden');
      this.initDrag();
    }
  }

  initDrag() {
    let initMarkerArry = [];
    let mediaLength = 30;

    $.ajax({
      type: 'get',
      url: $('.js-pane-question-content').data('marker-metas-url'),
      cache: false,
      async: false,
      success: function (data) {
        initMarkerArry = data.markersMeta;
        mediaLength = data.videoTime;
      }
    });
    drag(initMarkerArry, mediaLength, messenger);
  }
}

export default Manage;