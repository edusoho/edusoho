import 'waypoints/lib/jquery.waypoints.min';
import 'waypoints/lib/shortcuts/infinite.min';
import Emitter from 'common/es-event-emitter';

export default class ESInfiniteScroll extends Emitter {

  constructor(options) {
    super();

    this.options = options;

    this.initDownInfinite();
    this.initUpLoading();
  }

  initUpLoading() {
    $('.js-up-more-link').on('click', event => {
      let $target = $(event.currentTarget);
      $.ajax({
        method: 'GET',
        url: $target.data('url'),
        async: false,
        success: html => {
          $(html).find('.infinite-item').prependTo($('.infinite-container'));
          let $upLink = $(html).find('.js-up-more-link');
          if ($upLink.length > 0) {
            $target.data('url', $upLink.data('url'));
          } else {
            $target.remove();
          }
        }
      });

    });
  }

  initDownInfinite() {
    let defaultDownOptions = {
      element: $('.infinite-container')[0],
    };

    defaultDownOptions = Object.assign(defaultDownOptions, this.options);

    this.downInfinite = new Waypoint.Infinite(defaultDownOptions);
  }
}