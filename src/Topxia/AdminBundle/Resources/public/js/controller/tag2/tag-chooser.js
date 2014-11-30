define(function(require, exports, module) {

var Widget = require('widget');
var Overlay = require('overlay');
var AutoComplete = require('autocomplete');

var TagChooser = Widget.extend({
  attrs: {
    sourceUrl:'',
    queryUrl: '',
    matchUrl: '',
    choosedTags: [],
    maxTagNum: 10,
    maxTagMessage: '最多只能选择{{num}}个',
    existedMessage: '{{name}}已添加，不能重复添加'
  },

  _tagOverlay: null,
  _autocomplete: null,

  events: {
    'click .dropdown' : 'onDropdown',
    'click .tag-cancel': 'onTagCancel',
    'click .tag-confirm': 'onTagConfirm',
    'click .tag-item': 'onClickTagItem',
    'click .tag-node': 'onTagNode',
    'click .tag-remove': 'onTagRemove',
    'blur [data-role=tag-input]': 'onBlurTagInput'
  },

  setup: function() {
    this._initDorpdownOverlay();
    this._initChoosedTags();
    this._initAutocomplete();

    this.on('maxlimit', function(){
      var message = this.get('maxTagMessage').replace(/\{\{num\}\}/g, this.get('maxTagNum'));
      this._showInputError(message);
    });

    this.on('existed', function(tag) {
      var message = this.get('existedMessage').replace(/\{\{name\}\}/g, tag.name);
      this._showInputError(message);
    });
  },

  _initAutocomplete: function() {
    var autocomplete = new AutoComplete({
      trigger: this.$('[data-role=tag-input]'),
      dataSource: this.get('matchUrl'),
      width: this.$('[data-role=tag-input]').width(),
      selectFirst: true
    }).render();

    var self = this;
    autocomplete.on('itemSelected', function(data, item) {
      self.$('[data-role=tag-input]').val('');
      self.addTag({id:data.value, name:data.label});
    });

  },

  onDropdown: function(e) {
    if (this._tagOverlay.get('visible')) {
      this._tagOverlay.hide();
    } else {
      this._hideOverlayError();
      this._tagOverlay.show();
      var self = this;
      $.get(this.get('sourceUrl'), function(html) {
        self.$('[data-role=tags-list]').html(html);
        self._setDropdownChoosedTags();
      });
    }
  },

  onTagCancel: function(e) {
    this._tagOverlay.hide();
  },

  onTagConfirm: function(e) {
    var choosedTags = [];
    this.$('.tag-overlay').find('.tag-item-choosed').each(function(index, item) {
      var $item = $(item);
      choosedTags.push($item.data());
    });
    this.set('choosedTags', choosedTags);
    this._tagOverlay.hide();
  },

  onTagRemove: function (e) {
    this.removeTag($(e.currentTarget).parents('.choosed-tag').data('id'));
  },

  onClickTagItem: function(e) {
    var $item = $(e.currentTarget);
    var maxTagNum = this.get('maxTagNum');

    if (maxTagNum > 1) {
      if ($item.hasClass('tag-item-choosed')) {
        $item.removeClass('tag-item-choosed');
        this._hideOverlayError();
      } else {
        if (this.element.find('.tag-item-choosed').length >= maxTagNum) {
          var message = this.get('maxTagMessage').replace(/\{\{num\}\}/g, this.get('maxTagNum'));
          this._showOverlayError(message);

          return ;
        } else {
          $item.addClass('tag-item-choosed');
        }
      }
    } else {
      this.element.find('.tag-item-choosed').removeClass('tag-item-choosed');
      $item.addClass('tag-item-choosed');
    }

  },

  onBlurTagInput: function(e) {
    $(e.currentTarget).val('');
  },

  removeTag: function(id) {
    var choosedTags = [];
    $.each(this.get('choosedTags'), function(i, tag) {
      if (tag.id == id) {
        return ;
      }
      choosedTags.push(tag);
    });
    this.set('choosedTags', choosedTags);
  },

  addTag: function(newTag) {

    var maxTagNum = this.get('maxTagNum');

    if (maxTagNum == 1) {
      this.set('choosedTags', [newTag]);
      return ;
    }

    var choosedTags = $.extend([], this.get('choosedTags'));

    if (choosedTags.length >= maxTagNum) {
      this.trigger('maxlimit');
      return ;
    }

    var exist = false;
    for (var i = 0; i < choosedTags.length; i++) {
      if (newTag.id == choosedTags[i].id) {
        exist = true;
        break;
      }
    }

    if (exist) {
      this.trigger('existed', newTag);
      return ;
    }

    choosedTags.push(newTag);
    this.set('choosedTags', choosedTags);
  },

  _showOverlayError: function(message) {
    this.element.find('[data-role=overlay-error]').html(message).removeClass('hide');
    var self = this;
    setTimeout(function() {
      self._hideOverlayError();
    }, 3000);
  },

  _hideOverlayError: function() {
    this.element.find('[data-role=overlay-error]').html('').addClass('hide');
  },

  _showInputError: function(message) {
    message = '<span class="text-danger">' + message + '</span>';
    this.element.find('[data-role=input-error]').html(message).removeClass('hide');
    var self = this;
    setTimeout(function() {
      self._hideInputError();
    }, 3000);
  },

  _hideInputError: function() {
    this.element.find('[data-role=input-error]').html('').addClass('hide');
  },

  _onChangeChoosedTags: function(tags) {
    var $tags = this.$('.tags-choosed').empty();

    var $tagTemplate = this.$('.choosed-tag-template');

    $.each(tags, function(index, tag) {
      var $tag = $tagTemplate.clone().removeClass('choosed-tag-template');
      $tag.data(tag);

      $tagNamePlaceholder = $tag.find('.tag-name-placeholder');
      $tagNamePlaceholder.attr("data-id",tag.id)
      $tagNamePlaceholder.attr("data-name",tag.name)
      $tagNamePlaceholder.html(tag.name);

      $tags.append($tag);
    });

    this.trigger('change', tags);
  },

  _initDorpdownOverlay: function() {
    var overlayY = this.$('.input-group').height();
    var overlayWidth = this.$('.input-group').width();

    var overlay = new Overlay({
      element: this.$('.tag-overlay'),
      width: overlayWidth,
      height: 300,
      align: {
        baseElement: this.$('.input-group'),
        baseXY: [0, overlayY]
      }

    });

    overlay._blurHide([overlay.element, this.$('.dropdown')]);

    this._tagOverlay = overlay;
  },

  _initChoosedTags: function() {
    var tags = this.get('choosedTags');
    if (!$.isArray(tags) || tags.length == 0) {
      return ;
    }

    var self = this;
    $.getJSON(this.get('queryUrl'), {ids: tags}, function(tags) {
      self.set('choosedTags', tags);

      if (self._tagOverlay.get('visible')) {
        self._setDropdownChoosedTags();
      }
    });

  },

  _setDropdownChoosedTags: function() {
    this.$('.tag-overlay').find('.tag-item-choosed').removeClass('tag-item-choosed');
    var self = this;
    $.each(this.get('choosedTags'), function(index, tag) {
      self.$('.tag-overlay').find('.tag-item-' + tag.id).addClass('tag-item-choosed');
    });

    this._dropdownChoosedTagsInited = true;
  }


});


module.exports = TagChooser;


});
