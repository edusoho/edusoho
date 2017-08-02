import 'waypoints/lib/jquery.waypoints.min';
import 'waypoints/lib/shortcuts/infinite.min';

class ESInfiniteScroll {

  UP_MORE_LINK_ID = 'up-more-link';

  constructor (options) {
    this.options = options;

    this.initDownInfinite();
    this.initUpInfinite();
  }

  initDownInfinite() {
    let defaultDownOptions = {
      element: $('.infinite-container')[0],
    };

    defaultDownOptions = Object.assign(defaultDownOptions, this.options);

    this.downInfinite = new Waypoint.Infinite(defaultDownOptions);
  }

  initUpInfinite() {
    let instance = this;
    if ($('#up-more-link').length > 0) {
      let defaultUpOptions = {
        element: document.getElementById(this.UP_MORE_LINK_ID),
        handler: function(direction) {
          if (direction === 'up') {
            instance.handleUpAction();
          }
        }
      };

      defaultUpOptions = Object.assign(defaultUpOptions, this.options);

      this.upInfinite = new Waypoint(defaultUpOptions);
    }
  }

  handleUpAction() {
    let upInfinite = this.upInfinite,
        upId = this.UP_MORE_LINK_ID,
        downInfinite = this.downInfinite;

    upInfinite.disable();
    downInfinite.$container.addClass('infinite-loading-top');
    $.get($(upInfinite.element).data('url'), function (html) {

      $(html).find(downInfinite.options.items).prependTo(downInfinite.$container);
      let $upLink = $(html).find('#'+upId);
      if ($upLink.length > 0) {
        $(upInfinite.element).data('url', $upLink.data('url'));
        upInfinite.enable();
      } else {
        upInfinite.element.remove();
        upInfinite.destroy();
      }

      downInfinite.$container.removeClass('infinite-loading-top');

    });
  }
}

export default ESInfiniteScroll;