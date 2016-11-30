import Emitter from "common/es-event-emitter";
import ActivityEmitter from "../activity-emitter";

class Text extends Emitter  {
  constructor({element}) {
    super();
    this.element = $(element);

    this.emitter = new ActivityEmitter();
    this.emitter.receive('doing', (data) => {
      console.log(data);
      let finishTime = parseInt(this.element.data('finishTime'));
      console.log(finishTime);

      if(!finishTime){
        return;
      }

      if(data.learnedTime >= finishTime){
        console.log('text.finish');
        this.emitter.emit('finish');
      }
    })
  }

}


new Text({
  element: '#text-activity'
});