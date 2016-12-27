import Emitter from 'common/es-event-emitter';
import 'jquery-sortable';
import notify from 'common/notify';
import BatchSelect from '../../../common/widget/batch-select';
import DeleteAction from '../../../common/widget/delete-action';
import { deleteQuestion, replaceQuestion  } from '../../../common/component/question-operate';

class Picker{
  constructor($button, $typeNav, $form) {
    this.$button = $button;
    this.$typeNav = $typeNav;
    this.$form = $form;
    this.$modal = $('#testpaper-confirm-modal');
    this.currentType = this.$typeNav.find('.active').children().data('type');
    this._initEvent();
    this._initSortList();
    this.questions = [];
  }

  _initEvent() {
    this.$button.on('click',event => this._showPickerModal(event));
    this.$typeNav.on('click','li', event => this._changeNav(event));
    this.$form.on('click','.request-save',event => this._confirmSave(event));
    this.$modal.on('click','.confirm-submit',event => this._submitSave(event));
  }

  _initSortList() {
    //$('table').sortable({
    //   containerSelector: 'table',
    //   itemPath: '> tbody',
    //   itemSelector: 'tr',
    //   placeholder: '<tr class="placeholder"/>'
    // });
    this.$form.find('table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        onDrop: function (item, container, _super) {
            console.log(item);
            console.log(container);
            _super(item, container);

            if (item.hasClass('have-sub-questions')) {
                let $tbody = item.parents('tbody');
                $tbody.find('tr.is-question').each(function() {
                    let $tr = $(this);
                    $tbody.find('[data-parent-id=' + $tr.data('id') + ']').detach().insertAfter($tr);
                });
            }

            // self.refreshSeqs();
        }
    });
  }

  _showPickerModal(event) {
    let excludeIds = [];
    $('[data-type="'+this.currentType+'"]').find('[name="questionId[]"]').each(function(){
        excludeIds.push($(this).val());
    });

    let $modal = $("#modal").modal();
    $modal.data('manager', this);
    $.get(this.$button.data('url'), {excludeIds: excludeIds.join(','), type: this.currentType}, function(html) {
        $modal.html(html);
    });
  }

  _changeNav(event) {
    let $target = $(event.currentTarget);
    let type = $target.children().data('type');
    this.currentType = type;

    this.$typeNav.find('li').removeClass('active');
    $target.addClass('active');

    this.$form.find('[data-role="question-body"]').addClass('hide');
    this.$form.find('#testpaper-items-'+type).removeClass('hide');
  }
  _confirmSave(event) {
    let isOk = this._validateScore();

    if (!isOk) {
        return ;
    }

    if( $('[name="passedScore"]').length > 0){
        let passedScoreErrorMsg = $('.passedScoreDiv').siblings('.help-block').html();
        if ($.trim(passedScoreErrorMsg) != ''){
            return ;
        }
    }

    let stats = this._calTestpaperStats();
    
    let html='';
    $.each(stats, function(index, statsItem){
        let tr = "<tr>";
            tr += "<td>" + statsItem.name + "</td>";
            tr += "<td>" + statsItem.count + "</td>";
            tr += "<td>" + statsItem.score.toFixed(1) + "</td>";
            tr += "</tr>";
        html += tr;
    });

    this.$modal.find('.detail-tbody').html(html);

    this.$modal.modal('show');
  }

  _validateScore() {
    let isOk = true;

    if (this.$form.find('[name="scores[]"]').length == 0) {
        notify('danger','请选择题目。');
        isOk = false;
    }

    this.$form.find('input[type="text"][name="scores[]"]').each(function() {
        var score = $(this).val();

        if (score == '0') {
            notify('danger','题目分值不能为0。');
            isOk = false;
        }

        if (!/^(([1-9]{1}\d{0,2})|([0]{1}))(\.(\d){1})?$/.test(score)) {
            notify('danger','题目分值只能填写数字，并且在3位数以内，保留一位小数。');
            $(this).focus();
            isOk = false;
        }
    });

    return isOk;
  }

  _calTestpaperStats() {
    let stats = {};
    let self = this;

    this.$typeNav.find('li').each(function() {
        let type = $(this).find('a').data('type'),
            name = $(this).find('a').data('name');
            

        stats[type] = {name:name, count:0, score:0, missScore:0};

        self.$form.find('#testpaper-items-'+type).find('[name="scores[]"]').each(function() {
            let itemType = $(this).closest('tr').data('type');
            let score = itemType == 'material' ? 0 : parseFloat($(this).val());
            let question = {};

            if (itemType != 'material') {
              stats[type]['count'] ++;
            }
            
            stats[type]['score'] += score;
            stats[type]['missScore'] = parseFloat($(this).data('miss-score'));

            let questionId = $(this).closest('tr').data('id');

            question['id'] = questionId;
            question['score'] = score;
            question['missScore'] = parseFloat($(this).data('miss-score'));
            question['type'] = type;
            
            self.questions.push(question);
        });
    });

    let total = {name:Translator.trans('总计'), count:0, score:0};
    $.each(stats, function(index, statsItem) {
        total.count += statsItem.count;
        total.score += statsItem.score;
    });

    stats.total = total;

    return stats;
  }

  _submitSave(event) {
    
    $.post(this.$form.attr('action'),{questions:this.questions},function(result){
      if (result.goto) {
        window.location.href = result.goto;
      }
    })
  }
}

let $form = $('#question-checked-form');
new Picker($('[data-role="pick-item"]'), $('.nav-mini'), $form);
new BatchSelect($form);
new DeleteAction($form);
replaceQuestion($form,$("#modal"))
deleteQuestion($form);
//modal 预览

