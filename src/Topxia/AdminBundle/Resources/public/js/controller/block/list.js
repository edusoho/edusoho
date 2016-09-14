define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  var AutoComplete = require('autocomplete');
  exports.run = function() {
    $('body').on('click', 'button.delete-btn', function() {
      if (!confirm(Translator.trans('确认要删除此编辑区模板吗？'))) return false;
      var $btn = $(this);
      $.post($btn.data('url'), function(response) {
        if (response.status == 'ok') {
          $('#' + $btn.data('target')).remove();
          Notify.success(Translator.trans('删除编辑区模板成功!'));
        } else {
          alert(Translator.trans('服务器错误!'));
        }
      }, 'json');
    });

    function keyUp(e) {　　
      var currKey = 0,
        e = e || event;　　
      currKey = e.keyCode || e.which || e.charCode;
      if (currKey == 191) {
        setTimeout(function() {
          $("#block-input")[0].focus();
        }, 300)
      }　　
    }　　
    document.onkeyup = keyUp;
    var autocomplete = new AutoComplete({
      trigger: '#block-input',
      dataSource: $("#block-input").data('url'),
      filter: {
        name: 'stringMatch',
        options: {
          key: 'title'
        }
      },
      selectFirst: true
    }).render();
    autocomplete.on('itemSelect', function(data) {
      window.location.href = window.location.origin + "/admin/blockTemplate/" + data.id + "/visual/edit";
    });
  };
});