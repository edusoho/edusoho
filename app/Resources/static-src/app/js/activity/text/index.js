import Emitter from "common/es-event-emitter";
import ActivityEmitter from "../activity-emitter";
import DataReporter from '../data-reporter';

class Text extends Emitter  {
  constructor({element}) {
    super();
    this.element = $(element);

    this.emitter = new ActivityEmitter();
    this.emitter.receive('doing', (data) => {
      let finishTime = parseInt(this.element.data('finishTime'));

      if(!finishTime){
        return;
      }

      if(data.learnedTime >= finishTime){
        this.emitter.emit('finish');
      }
    })
    let dataReporter = new DataReporter();
  }

}


new Text({
  element: '#text-activity'
});