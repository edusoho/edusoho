export default class Base {
  constructor() {
    this.init();
  }

  init() {
    this.initValidator();
    this.initTags();
    this.initOrg();
  }

  initValidator() {
    const $form = $('#courseset-form');
    const validator = $form.validate({
      rules: {
        title: {
          maxlength: 100,
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            }
          },
	        course_title: true
        },
        subtitle: {
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return false;
            }
          },
	        course_title: true
        }
      },
    });
    $('#courseset-base-submit').click((event) => {
      if (validator.form()) {
        $(event.currentTarget).button('loading');
        $form.submit();
      }
    });
  }

  initTags() {
    const $tags = $('#tags');
    $tags.select2({
      ajax: {
        url: $tags.data('url'),
        dataType: 'json',
        quietMillis: 500,
        data (term, page) {
          return {
            q: term,
            page_limit: 10
          };
        },
        results (data) {
          console.log(data);
          return {
            results: data.map((item) => {
              return { id: item.name, name: item.name };
            })
          };
        }
      },
      initSelection (element, callback) {
        const data = [];
        $(element.val().split(',')).each(function () {
          data.push({
            id: this,
            name: this
          });
        });
        callback(data);
      },
      formatSelection (item) {
        return item.name;
      },
      formatResult (item) {
        return item.name;
      },
      formatSearching: function() {
        return Translator.trans('搜索中...');
      },
      multiple: true,
      maximumSelectionSize: 20,
      placeholder: Translator.trans('请输入标签'),
      width: 'off',
      createSearchChoice () {
        return null;
      }
    });
  }

  initOrg() {
     $('[data-role="tree-select"], [name="categoryId"]').select2({
        treeview: true,
        dropdownAutoWidth: true,
        treeviewInitState: 'collapsed',
        placeholderOption: 'first'
      });
  }
}