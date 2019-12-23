class BankEdit {
  constructor() {
    this.$form = $('#bank-form');
    this.initValidate();
    this.initSelect();
  }

  initValidate() {
    this.$form.validate({
      currentDom: '#save-btn',
      ajax: true,
      rules: {
        name: {
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            }
          },
          maxlength: 30,
          trim: true
        },
        categoryId: {
          required: true,
        },
      },
      messages: {
        categoryId: {
          required: Translator.trans('admin.question_bank.choose_category')
        }
      },
      submitSuccess(response) {
        window.location.reload();
      },
    });
  }

  initSelect() {
    $('[name="categoryId"]').select2({
      treeview: true,
      dropdownAutoWidth: true,
      treeviewInitState: 'collapsed',
      placeholderOption: 'first',
      formatNoMatches: function() {
        return Translator.trans('admin.question_bank.no_category');
      }
    });

    $('#bank-members').select2({
      ajax: {
        url: $('#bank-members').data('matchUrl'),
        dataType: 'json',
        quietMillis: 100,
        data: function(term, page) {
          return {
            q: term,
            page_limit: 10
          };
        },
        results: function(data) {
          let results = [];

          $.each(data, function(index, item) {
            results.push({
              id: item.id,
              name: item.nickname
            });
          });

          return {
            results: results
          };
        }
      },
      initSelection: function(element, callback) {
        let data = [];
        let members =  JSON.parse(element.val());
        element.val('');
        $(members).each(function() {
          data.push({
            id: this.id,
            name: this.name
          });
        });
        callback(data);
      },
      formatSelection: function(item) {
        return item.name;
      },
      formatResult: function(item) {
        return item.name;
      },
      multiple: true,
      maximumSelectionSize: 20,
      width: 'off',
      createSearchChoice: function() {
        return null;
      }
    });

    $('#bank-members').removeClass('hidden');
  }
}

new BankEdit();
