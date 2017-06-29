define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');

  exports.run = function(options) {
    var $table = $('#product-table');

    $table.on('click', '.js-downShelves', function() {
      if (!confirm(Translator.trans('确定下架该商品?'))) {
        return false;
      }

      $.post($(this).data('url'), function(html) {
        var $tr = $(html);
        $table.find('#' + $tr.attr('id')).replaceWith(html);
        Notify.success(Translator.trans('商品下架成功！'));
      });
    });

    $table.on('click', '.js-upShelves', function() {
      if (!confirm(Translator.trans('确定上架该商品?'))) {
        return false;
      }

      $.post($(this).data('url'), function(html) {
        var $tr = $(html);
        $table.find('#' + $tr.attr('id')).replaceWith(html);
        Notify.success(Translator.trans('商品上架成功！'));
      });
    });

  };

});