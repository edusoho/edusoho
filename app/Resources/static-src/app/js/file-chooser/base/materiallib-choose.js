import Chooser from './chooser';

class MaterialLibChoose extends Chooser {

  constructor($container) {
    super();
    this.container = $container;
    this.loadShareingContacts = false;
    this._init();
    this._initEvent();
  }

  _init() {
    this._initTagSelect();
    this._loadList();
  }

  _initTagSelect(){
    let $tags = $(this.container).find('#materialTags');
    $tags.select2({
      ajax: {
        url: $tags.data('url') + '#',
        dataType: 'json',
        quietMillis: 100,
        data: function(term, page) {
          return {
            q: term,
            page_limit: 100
          };
        },
        results: function(data) {
          var results = [{id: "0", name: "--选择标签--"}];
          $.each(data, function(index, item) {
            results.push({
              id: item.id,
              name: item.name
            });
          });
          return {
            results: results
          };
        }
      },
      formatSelection: function(item) {
        return item.name;
      },
      formatResult: function(item) {
        return item.name;
      },
      width: 'off',
      multiple: false,
      locked: true,
      placeholder: Translator.trans('--选择标签--'),
      maximumSelectionSize: 100,
    });
  }

  _initEvent() {
    $(this.container).on('change', '#materialType',this._switchFileSourceSelect.bind(this));
    $(this.container).on('click', '#materialTags',this._switchTags.bind(this));
    $(this.container).on('click', '.js-material-type', this._switchFileSource.bind(this));
    $(this.container).on('click', '.js-browser-search', this._filterByFileName.bind(this));
    $(this.container).on('click', '.pagination a', this._paginationList.bind(this));
    $(this.container).on('click', '.file-browser-item', this._onSelectFile.bind(this));
  }

  _loadList() {
    let url = $('.js-browser-search').data('url');
    $.get(url, this._getParams(), html => {
      this.container.find('.js-material-list').html(html);
    });
  }

  _getParams() {
    let params = {};
    $('.js-material-lib-search-form input[type=hidden]').each(function(input) {
      params[$(this).attr('name')] = $(this).val();
    });
    return params;
  }

  _paginationList(event) {
    event.stopImmediatePropagation();
    event.preventDefault();

    let page = this._getUrlParameter($(event.currentTarget).attr('href'), 'page');
    $('input[name=page]').val(page);
    this._loadList();
  }

  _switchTags(event) {
    let that = event.currentTarget;
    $('input[name=tagId]').val($(that).val());
    this._loadList();
  }

  _switchFileSourceSelect(event) {
    let that = event.currentTarget;
    $('input[name=sourceFrom]').val($(that).val());
    $('input[name=page]').val(1);
    switch ($(that).val()) {
      case 'upload':
        $('.js-file-name-group').removeClass('hidden');
        $('.js-file-owner-group').addClass('hidden');
        break;
      case 'shared':
        this._loadSharingContacts.call(this, $(that).data('sharingContactsUrl'));
        $('.js-file-name-group').removeClass('hidden');
        $('.js-file-owner-group').addClass('hidden');
        break;
      default:
        $('.js-file-name-group').removeClass('hidden');
        $('.js-file-owner-group').addClass('hidden');
        break;
    }
    this._loadList();
  }

  _switchFileSource(event) {
    let that = event.currentTarget;
    var type = $(that).data('type');
    $(that).addClass('active').siblings().removeClass('active');
    $('input[name=sourceFrom]').val(type);
    $('input[name=page]').val(1);
    switch (type) {
    case 'my':
      $('.js-file-name-group').removeClass('hidden');
      $('.js-file-owner-group').addClass('hidden');
      break;
    case 'sharing':
      this._loadSharingContacts.call(this, $(that).data('sharingContactsUrl'));
      $('.js-file-name-group').removeClass('hidden');
      $('.js-file-owner-group').addClass('hidden');
      break;
    default:
      $('.js-file-name-group').removeClass('hidden');
      $('.js-file-owner-group').addClass('hidden');
      break;
    }
    this._loadList();
  }

  _loadSharingContacts(url) {
    if (this.loadShareingContacts == true) {
      console.error('teacher list has been loaded');
      return;
    }
    $.get(url, function(teachers) {
      if (Object.keys(teachers).length > 0) {
        var html = `<option value=''>${Translator.trans('activity.manage.choose_teacher_hint')}</option>`;
        $.each(teachers, function(i, teacher) {
          html += `<option value='${teacher.id}'>${teacher.nickname} </option>`;
        });

        $('.js-file-owner', self.element).html(html);
      }

    }, 'json');
    this.loadShareingContacts = true;
  }


  _filterByFileName() {
    $('input[name=keyword]').val($('.js-file-name').val());
    this._loadList();
  }


  _onSelectFile(event) {
    $('.file-browser-item').removeClass('active');
    var $that = $(event.currentTarget).addClass('active');
    var file = $that.data();
    $('[data-role="placeholder"]').html(file.name);
    this.emit('select', file);
  }

}

export default MaterialLibChoose;
