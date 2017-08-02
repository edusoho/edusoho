import 'waypoints/lib/jquery.waypoints.min';
import 'waypoints/lib/shortcuts/infinite.min';

class ESInfiniteScroll {

  UP_MORE_LINK_ID = 'up-more-link';

  constructor () {
    this.infinite = new Waypoint.Infinite({
      element: $('.infinite-container')[0]
    });

    this.initUpAction();
  }

  initUpAction() {
    let infinite = this.infinite;
    let instance = this;
    if ($('#up-more-link').length > 0) {
      let waypoint = new Waypoint({
        element: document.getElementById(this.UP_MORE_LINK_ID),
        handler: function(direction) {
          if (direction === 'up') {
            let $upLink = $(waypoint.element);
            $.get($upLink.data('url'), function (html) {

              $(html).find(infinite.options.items).prependTo(infinite.$container);

              if ($(html).find('#'+instance.UP_MORE_LINK_ID).length > 0) {
                $upLink.data('url', $(html).find('#'+instance.UP_MORE_LINK_ID).data('url'));
              } else {
                waypoint.element.remove();
                waypoint.destroy();
              }
            });
          }
        }
      });
    }
  }
}

export default ESInfiniteScroll;