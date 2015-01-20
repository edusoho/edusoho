define("chineserp-jquery/1.0.0/chineserp-jquery-debug", ["$", "chineserp/1.0.0/chineserp-debug"], function(require, exports, module) {
  /*! Chinese region picker for jQuery plugin - v0.0.4 - 2013-12-13
   * https://github.com/xixilive/chineserp-jquery
   * Copyright (c) 2013 xixilive; Licensed MIT */
  var $ = require("$");
  var cr = require("chineserp/1.0.0/chineserp-debug");
  'use strict';
  /**
   * RegionPicker
   * @param {Object} el, source element
   * @param {Object} options
   * @return {Object} RegionPicker object
   */
  var RegionPicker = function(el, options) {
    this.el = el;
    this.options = $.extend({
      remote: '',
      picked: '',
      visible: 10,
      animate: 0
    }, options || {});
    this.picker = null;
    this.options.picked = String(this.options.picked);
    this.options.visible = parseInt(this.options.visible, 10) || 10;
    this.options.animate = parseInt(this.options.animate, 10) || 0;
    this.data = {
      regions: [],
      collections: []
    };
    this.el.on('initializing.rp', $.proxy(this._onInitializing, this));
    this.el.on('initialized.rp', $.proxy(this._onInitialized, this));
    $(document).on('keydown', $.proxy(this._onCloserClick, this));
    new cr.RegionPicker({
      remote: this.options.remote,
      initialized: $.proxy(this._preInitialize, this)
    });
  };
  RegionPicker.prototype = {
    constructor: RegionPicker,
    /**
     * _preInitialize, fired when ChineseRegion.RegionPicker initialized
     * @param {Object} picker, ChineseRegion.RegionPicker object
     * @return undefined
     */
    _preInitialize: function(picker) {
      this.picker = picker;
      this.el.trigger('initializing.rp', [this]);
      this.picker.pick(this.options.picked, $.proxy(function(regions, collections) {
        this.data.regions = regions;
        this.data.collections = collections;
        this.el.trigger('initialized.rp', [this]);
      }, this));
    },
    /**
     * _onInitializing, fired after ChineseRegion.RegionPicker initialized,
     * before the picker has picked the initial value that specified in options.picked
     * @return undefined
     */
    _onInitializing: function() {
      this.renderer = $('<div id="region-picker" style="position:absolute;display:none;"></div>').append('<a href="javascript:;" class="region-picker-closer" title="cancel">&#215;</a>').append('<div class="regions"></div>').appendTo('body');
      this.renderer.on('rendered', '.region-list', $.proxy(this._onListRendered, this));
      this.renderer.on('reveal', $.proxy(this._render, this));
      this.renderer.on('picked', '.region-list li', $.proxy(this._onItemPicked, this));
      this.renderer.on('click', '.region-list li', $.proxy(this._onItemClick, this));
      this.renderer.on('click', '.region-picker-closer', $.proxy(this._onCloserClick, this));
    },
    /**
     * _onInitialized, fired after the picker has picked the initial value that specified in options.picked
     * @return undefined
     */
    _onInitialized: function() {
      this.el.on('click', $.proxy(this._onClick, this));
    },
    /**
     * _onClick, fired when the source element clicked
     * @param {Object} e, event object
     * @return undefined
     */
    _onClick: function(e) {
      e.preventDefault();
      if (this.renderer.css('display') !== 'none') {
        return;
      }
      var offset = this.el.offset();
      this.renderer.css({
        top: offset.top + this.el.outerHeight(true),
        left: offset.left
      }).fadeIn(this.options.animate).trigger('reveal');
    },
    /**
     * _onItemClick, fired when a region item clicked
     * @param {Object} e, event object
     * @return undefined
     */
    _onItemClick: function(e) {
      var el = $(e.currentTarget),
        id = el.attr('data-id');
      e.preventDefault();
      this.el.trigger('loading.rp', this);
      this.picker.pick(id, $.proxy(function(regions, collections) {
        this.data.regions = regions;
        this.data.collections = collections;
        this.el.trigger('loaded.rp', [this.data, this]);
        var leafNode = (regions[regions.length - 1] && id === regions[regions.length - 1].i);
        if (leafNode) {
          this.el.trigger('picked.rp', [regions, this]);
          this._onCloserClick(e);
        } else {
          this.renderer.trigger('reveal');
        }
      }, this));
    },
    /**
     * _onItemPicked, fired when a region item get a 'picked' css class
     * @param {Object} e, event object
     * @param {Object} list, the list which contains current element
     * @param {Number} index, current element index of it's parent
     * @param {Number} height, the height in pixel of region item
     * @return undefined
     */
    _onItemPicked: function(e, list, index, height) {
      e.stopPropagation();
      //scroll picked item into view
      var offset = index - this.options.visible + 1;
      if (offset >= 0) {
        list.animate({
          scrollTop: (offset + 1) * height
        }, Math.max(this.options.animate * 1.5, 200));
      }
    },
    /**
     * _onCloserClick, fired when close button clicked
     * @param {Object} e, event object
     * @return undefined
     */
    _onCloserClick: function(e) {
      if (e.type === 'click' || (e.type === 'keydown' && e.which === 27)) {
        e.stopPropagation();
        if (this.renderer.css('display') !== 'none') {
          this.renderer.fadeOut(this.options.animate);
        }
      }
    },
    /**
     * _onListRendered, fired when a region list has appended into it's parent element
     * @param {Object} e, event object
     * @param {Object} region, region object which has the same index number of current list in a collection
     * @return undefined
     */
    _onListRendered: function(e, region) {
      var list = $(e.currentTarget);
      var h = $('li:first', list).outerHeight(true);
      list.height(this.options.visible * h);
      $('li', list).each(function(i) {
        if ($(this).attr('data-id') === region.i) {
          $(this).addClass('picked').trigger('picked', [list, i, h]);
        }
      });
    },
    /**
     * _render, fired when current plugin has shown
     * @return undefined
     */
    _render: function() {
      var container = $('.regions', this.renderer).empty();
      for (var i = 0; i < this.data.collections.length; i++) {
        $('<ul class="region-list"></ul>').append(this._renderItems(this.data.collections[i])).appendTo(container).trigger('rendered', [this.data.regions[i]]);
      }
    },
    _renderItems: function(collection) {
      var items = [],
        r;
      for (var i = 0; i < collection.length; i++) {
        r = collection[i];
        items.push($('<li data-id="' + r.i + '">' + r.n + '</li>').fadeIn(this.options.animate));
      }
      return items;
    }
  };
  $.fn.regionPicker = function(options) {
    return this.each(function() {
      var self = $(this),
        data = self.data("regionpicker");
      if (!data) {
        var opts = $.extend(self.data(), options || {});
        self.data('regionpicker', (data = new RegionPicker(self, opts)));
      }
    });
  };
});