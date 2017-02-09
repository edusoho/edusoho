import { isMobileDevice } from 'common/utils'

const Card = () => {
  if (!isMobileDevice()) {
    $(".js-user-card").on("mouseenter", function () {
      let _this = $(this);
      let userId = _this.data('userId');
      let loadingHtml =
        '<div class="card-body">' +
        '<div class="card-loader">' +
        '<div class="loader-inner">' +
        '<span></span>' +
        '<span></span>' +
        '<span></span>' +
        '</div>' + Translator.trans('名片加载中') +
        '</div>' +
        '</div>'
      let timer = setTimeout(function () {
        if ($('#user-card-' + userId).length == 0 || !_this.data('popover')) {
          $.ajax({
            type: "GET",
            url: _this.data('cardUrl'),
            dataType: "html",
            beforeSend: beforeSend(_this),
            success: function (html) {
              bindCardEvent('.js-card-content');
              callback(html, _this)
            }
          });
        } else {
          let html = $('#user-card-' + userId).clone();
          callback(html, _this);
        }
      }, 300);
      _this.data('timerId', timer);
    }).on("mouseleave", function () {
      let _this = $(this);
      setTimeout(function () {
        if (!$(".popover:hover").length) {
          _this.popover("hide");
        }
      }, 100);
      clearTimeout(_this.data('timerId'));
    });
  }
}

function callback(html, _this) {
  if ($('#user-card-' + _this.data('userId')).length == 0) {
    if ($('body').find('#user-card-store').length > 0) {
      $('#user-card-store').append(html);
    } else {
      $('body').append('<div id="user-card-store" class="hidden"></div>');
      $('#user-card-store').append(html);
    }
  }
  if (!_this.data('popover')) {
    _this.popover('destroy');
    _this.popover({
      trigger: 'manual',
      placement: 'auto top',
      html: 'true',
      content: function () {
        return html;
      },
      template: '<div class="popover es-card"><div class="arrow"></div><div class="popover-content"></div></div>',
      container: 'body',
      animation: true
    });
  }
  _this.popover("show");
  _this.data('popover', true);
  $(".popover").on("mouseleave", function () {
    $(_this).popover('hide');
  });
}

function bindCardEvent(selector) {
  $('body').on('click', '.js-card-content .follow-btn', function () {
    let $btn = $(this).button('loading');
    $.post($btn.data('url'), function () {
      $btn.button('reset').hide();
      $btn.siblings('.unfollow-btn').show();
    });
  })

  $('body').on('click', '.js-card-content .unfollow-btn', function () {
    let $btn = $(this).button('loading');
    $.post($btn.data('url'), function () {
      $btn.button('reset').hide();
      $btn.siblings('.follow-btn').show();
    });
  })
}

function beforeSend(_this) {
  _this.popover({
    trigger: 'manual',
    placement: 'auto top',
    html: 'true',
    content: function () {
      return loadingHtml;
    },
    template: '<div class="popover es-card"><div class="arrow"></div><div class="popover-content"></div></div>',
    container: 'body',
    animation: true
  });
};



export default Card();

