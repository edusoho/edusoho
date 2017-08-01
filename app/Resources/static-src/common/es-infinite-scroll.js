import 'waypoints/lib/jquery.waypoints.min';
import 'waypoints/lib/shortcuts/infinite.min';

class ESInfiniteScroll {
  constructor () {
    let infinite = new Waypoint.Infinite({
      element: $('.infinite-container')[0]
    });
    console.log(infinite);
  }
}

export default ESInfiniteScroll;