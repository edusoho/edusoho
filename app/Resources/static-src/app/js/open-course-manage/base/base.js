export default class Base {
  constructor() {
    this.initSelect2();
    this.initCkeditor();
    this.initValidator();
    this.initCategory();
  }

  initSelect2() {
    $('#course_tags').select2({
      ajax: {
        url: app.arguments.tagMatchUrl + '#',
        dataType: 'json',
        quietMillis: 100,
        data: function (term, page) {
          return {
            q: term,
            page_limit: 10
          };
        },
        results: function (data) {
          var results = [];
          $.each(data, function (index, item) {

            results.push({
              id: item.name,
              name: item.name
            });
          });

          return {
            results: results
          };

        }
      },
      initSelection: function (element, callback) {
        var data = [];
        $(element.val().split(',')).each(function () {
          data.push({
            id: this,
            name: this
          });
        });
        callback(data);
      },
      formatSelection: function (item) {
        return item.name;
      },
      formatResult: function (item) {
        return item.name;
      },
      formatSearching: function () {
        return Translator.trans('open_course.load_search_hint');
      },
      width: 'off',
      multiple: true,
      maximumSelectionSize: 20,
      placeholder: Translator.trans('open_course.tag_required_hint'),
      createSearchChoice: function () {
        return null;
      },
    });
  }

  initValidator() {
    let $form = $('#course-form');
    let validator = $form.validate({
      currentDom: '#course-create-btn',
      ajax: true,
      rules: {
        title: {
          required: true,
          byte_maxlength: 200,
          trim: true,
          course_title: true,
        },
        startTime: {
          required: true,
          after_now: true,
          es_remote: {
            type: 'post',
            data: {
              clientTime: function () {
                return $('[name=startTime]').val();
              }
            }
          }
        },
        timeLength: {
          required: true,
          positive_integer: true,
          es_remote: {
            type: 'get',
            data: {                     //要传递的数据
              startTime: function () {
                return $('[name=startTime]').val();
              },
              length: function () {
                return $('[name=timeLength]').val();
              },
            }
          }
        }
      },
      messages: {
        startTime: {
          es_remote: Translator.trans('validate.after_now.message')
        }
      },
      submitSuccess: (data) => {
        cd.message({ type: 'success', message: Translator.trans('site.save_success_hint') });
        window.location.reload();
      }
    });

    $('#course-create-btn').click(() => {
      if (validator.form()) {
        $('#course-about-field').val(this.editor.getData());
        $form.submit();
      }
    });

    if (!$form.data('update')) {
      const $helpTip = $('.js-time-help-tip');
      $helpTip.html(Translator.trans('open_course.live_time_can_not_edit_bint')).addClass('color-danger');
      $('[name=startTime]').attr('disabled', true);
      $('#live-length-field').attr('disabled', true);
    } else {
      $('[name=startTime]').attr('disabled', false);
    }

    let now = new Date();

    $('[name=startTime]').datetimepicker({
      autoclose: true,
      language: document.documentElement.lang
    }).on('hide', function (ev) {
      validator.element('[name=startTime]');
    });
    $('[name=startTime]').datetimepicker('setStartDate', now);
  }

  initCkeditor() {
    let self = this;
    if ($('#course-about-field').length > 0) {
      self.editor = CKEDITOR.replace('course-about-field', {
        allowedContent: true,
        toolbar: 'Detail',
        fileSingleSizeLimit: app.fileSingleSizeLimit,
        filebrowserImageUploadUrl: $('#course-about-field').data('imageUploadUrl')
      });
    }
  }

  initCategory() {
    $('[name="categoryId"]').select2({
      treeview: true,
      dropdownAutoWidth: true,
      treeviewInitState: 'collapsed',
      placeholderOption: 'first'
      // treeviewInitState: 'expanded'
    });
  }
}