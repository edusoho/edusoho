// import ThreadShowWidget from '../../course-thread/show-widget'

class QuestionPane {
  constructor(option) {
    this.$element = option.element;
    this.dataInitialized = false;
    this.createFormId = 'lesson-question-plugin-form';
    this.editor = null;
    this.createFormElement = null;
    this.plugin= option.plugin;
    this._init();
  }
  _init() {
    this.$element.on('focusin','.expand-form-trigger',event=>this.expandForm(event));
    $('.collapse-form-btn').click(event=>this.collapseForm(event));
    $('.show-question-item').click(event=>this.showItem(event));
    $('.back-to-list').click(event=>this.backToList(event));
  }

  showList() {
    let toolbar = this.plugin.toolbar;
    $.get('http://www.esdev.com/lessonplugin/question/init', {courseId:toolbar.courseId, lessonId:toolbar.lessonId}, (html) => {
        this._dataInitialized = true;
        this.$element.html(html);
        this.createFormElement = $('#' + this.createFormId);
        this._showListPane();
        // this._showWidget = new ThreadShowWidget({
        //     element: $('[data-role=show-pane]')
        // });
    });
  }
  show() {
    this.plugin.toolbar.showPane(this.plugin.code);
    this.showList();
  }
  expandForm() {
    console.log("expandForm");
    let $form = this.createFormElement;
    if ($form.hasClass('form-expanded')) {
        return ;
    }
    $form.addClass('form-expanded');

    var editor = CKEDITOR.replace('question_content', {
        toolbar: 'Simple',
        filebrowserImageUploadUrl: $('#question_content').data('imageUploadUrl')
    });

    this.editor = editor;

    // let validator = $form.validate({
    //   onkeyup: false,
    //   rules: {
    //       'question[title]': {
    //         required: true,
    //       },
    //       length: {
    //         required: true,
    //         digits: true,
    //         max: 300
    //       },
    //       remark: {
    //         maxlength: 1000
    //       },
    //   },
    // });

    // if(validator.form()) {
    //   $.post($form.attr('action'), $form.serialize(), function(html) {
    //     pane.$('[data-role=list]').prepend(html);
    //     pane.$('.empty-item').remove();
    //     pane.collapseForm();
    //   }).error(function(response){
    //     var response = $.parseJSON(response.responseText);
    //     Notify.danger(response.error.message);
    //   });
    // }
    this.createFormElement.find('.detail-form-group').removeClass('hide');
  }
  collapseForm() {
    this.createFormElement.removeClass('form-expanded');
    if (this.editor) {
      this.editor.destroy();
    }
    // Validator.query(this.createFormElement).destroy();
    this.clearForm();
    this.createFormElement.find('.detail-form-group').addClass('hide');
  }
  clearForm() {
    this.createFormElement.find('input[type=text],textarea').each(function(){
        $(this).val('');
    });
  }
  showItem(e) {
    let toolbar = this.plugin.toolbar,
      $thread = $(e.currentTarget);
    $.get(this.plugin.api.show, {courseId:toolbar.courseId, id:$thread.data('id')}, (html) => {
      this._showItemPane().html(html);
      this._showWidget.trigger('reload');
    });
  }
  backToList(e) {
    this.showList();
  }
  _showListPane() {
      $('[data-role=show-pane]').hide();
      $('[data-role=list-pane]').show();
      // $('.question-list-pane').perfectScrollbar({wheelSpeed:50});
      return $('[data-role=list-pane]');
  }
  _showItemPane() {
      $('[data-role=list-pane]').hide();
      return $('[data-role=show-pane]').show();
  }

}

export default QuestionPane;




// define(function(require, exports, module) {
//     var Notify = require('common/bootstrap-notify');
//     var Widget = require('widget'),
//     Validator = require('bootstrap.validator'),
//     ThreadShowWidget = require('../../../course-thread/show-widget');
//     require('jquery.perfect-scrollbar');
//     require('es-ckeditor');

//     var QuestionPane = Widget.extend({
//         _dataInitialized: false,
//         attrs: {
//             createFormId: 'lesson-question-plugin-form',
//             editor:null
//         },
//         events: {
//             'focusin .expand-form-trigger' : 'expandForm',
//             'click .collapse-form-btn' : 'collapseForm',
//             'click .show-question-item' : 'showItem',
//             'click .back-to-list' : 'backToList'
//         },
//         setup: function() {
//             this.get('plugin').toolbar.on('change:lessonId', function(id) {
//             });
//         },
//         showList: function() {
//             var pane = this,
//                 toolbar = pane.get('plugin').toolbar;

//             // if (!pane._dataInitialized) {
//                 $.get(pane.get('plugin').api.init, {courseId:toolbar.get('courseId'), lessonId:toolbar.get('lessonId')}, function(html) {
//                     pane._dataInitialized = true;
//                     pane.element.html(html);
//                     pane.createFormElement = $('#' + pane.get('createFormId'));
//                     pane._showListPane();
//                     pane._showWidget = new ThreadShowWidget({
//                         element: pane.$('[data-role=show-pane]')
//                     });
//                 });
                
//             // } else {
//             //     pane._showListPane();
//             // }
//         },
//         show: function() {
//           this.get('plugin').toolbar.showPane(this.get('plugin').code);
//           this.showList();
//         },
//         expandForm: function() {
//             var pane = this,
//                 $form = this.createFormElement;
//             if ($form.hasClass('form-expanded')) {
//                 return ;
//             }
//             $form.addClass('form-expanded');

//             // group: 'course'
//             var editor = CKEDITOR.replace('question_content', {
//                 toolbar: 'Simple',
//                 filebrowserImageUploadUrl: $('#question_content').data('imageUploadUrl')
//             });

//             this.set('editor', editor);

//             var validator = new Validator({
//                 element: $form,
//                 autoSubmit: false,
//                 triggerType: 'submit'
//             });

//             validator.addItem({
//                 element: '[name="question[title]"]',
//                 required: true
//             });

//             validator.on('formValidate', function(elemetn, event) {
//                 editor.updateElement();
//             });

//             validator.on('formValidated', function(err, msg, ele) {
//                 if (err == true) {
//                     return ;
//                 }

//                 $.post($form.attr('action'), $form.serialize(), function(html) {
//                     pane.$('[data-role=list]').prepend(html);
//                     pane.$('.empty-item').remove();
//                     pane.collapseForm();
//                 }).error(function(response){
//                     var response = $.parseJSON(response.responseText);
//                     Notify.danger(response.error.message);
//                 });
//             });

//             this.createFormElement.find('.detail-form-group').removeClass('hide');
//         },
//         collapseForm: function() {
//             this.createFormElement.removeClass('form-expanded');
//             if (this.get('editor')) {
//                 this.get('editor').destroy();
//             }

//             Validator.query(this.createFormElement).destroy();

//             this.clearForm();

//             this.createFormElement.find('.detail-form-group').addClass('hide');
//         },
//         clearForm: function() {
//             this.createFormElement.find('input[type=text],textarea').each(function(){
//                 $(this).val('');
//             });
//         },
//         showItem: function(e) {
//             var pane = this,
//                 toolbar = pane.get('plugin').toolbar,
//                 $thread = $(e.currentTarget);

//             $.get(pane.get('plugin').api.show, {courseId:toolbar.get('courseId'), id:$thread.data('id')}, function(html) {
//                 pane._showItemPane().html(html);
//                 pane._showWidget.trigger('reload');
//             });
//         },
//         backToList: function(e) {
//             this.showList();
//         },
//         _showListPane: function() {
//             this.$('[data-role=show-pane]').hide();
//             this.$('[data-role=list-pane]').show();
//             this.element.find('.question-list-pane').perfectScrollbar({wheelSpeed:50});
//             return this.$('[data-role=list-pane]');
//         },
//         _showItemPane: function() {
//             this.$('[data-role=list-pane]').hide();
//             return this.$('[data-role=show-pane]').show();
//         }
//     });

//     module.exports = QuestionPane;

// });