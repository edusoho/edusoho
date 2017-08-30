import 'waypoints/lib/jquery.waypoints.min';
import 'waypoints/lib/shortcuts/infinite.min';
import Emitter from "common/es-event-emitter";

export default class ESInfiniteScroll extends Emitter {

  constructor (options) {
    super();

    this.options = options;

    this.initDownInfinite();
  }

  initDownInfinite() {
    let defaultDownOptions = {
      element: $('.infinite-container')[0],
    };

    defaultDownOptions = Object.assign(defaultDownOptions, this.options);

    this.downInfinite = new Waypoint.Infinite(defaultDownOptions);
  }
}