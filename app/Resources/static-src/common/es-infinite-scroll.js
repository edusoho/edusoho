import 'waypoints/lib/jquery.waypoints.min';
import 'waypoints/lib/shortcuts/infinite.min';

class ESInfiniteScroll {

  UP_MORE_LINK_ID = 'up-more-link';

  constructor () {
    this.infinite = new Waypoint.Infinite({
      element: $('.infinite-container')[0],
      offset: 20
    });

    this.initUpAction();
  }

  initUpAction() {
    let instance = this;
    if ($('#up-more-link').length > 0) {
      this.upWaypoint = new Waypoint({
        element: document.getElementById(this.UP_MORE_LINK_ID),
        handler: function(direction) {
          if (direction === 'up') {
            instance.handleUpAction();
          }
        },
        offset: 20
      });
    }
  }

  handleUpAction() {
    let upWaypoint = this.upWaypoint,
        upId = this.UP_MORE_LINK_ID,
        infinite = this.infinite;

    upWaypoint.disable();
    infinite.$container.addClass('infinite-loading-top');
    $.get($(upWaypoint.element).data('url'), function (html) {

      $(html).find(infinite.options.items).prependTo(infinite.$container);

      if ($(html).find('#'+upId).length > 0) {
        $(upWaypoint.element).data('url', $(html).find('#'+upId).data('url'));
        upWaypoint.enable();
      } else {
        upWaypoint.element.remove();
        upWaypoint.destroy();
      }

      infinite.$container.removeClass('infinite-loading-top');

    });
  }
}

export default ESInfiniteScroll;