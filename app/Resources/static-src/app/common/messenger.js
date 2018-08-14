import Messenger from 'es-messenger';
import Emitter from 'component-emitter';

export default class EsMessenger extends Emitter {
  constructor(options) {
    super();
    this.name = options.name;
    this.project = options.project;
    this.children = options.children;
    this.type = options.type; //enum: parent,child
    this.setup();
  }

  setup() {
    var messenger = new Messenger(this.name, this.project);
    if (this.type == 'child') { //同时广播同域和者跨域
      messenger.addTarget(window.parent, 'parent');
      messenger.addTarget(window.self, 'partner');
    } else if (this.type == 'parent') {
      messenger.addTarget(window.self, 'child');
      var children = this.children;
      for (var i = children.length - 1; i >= 0; i--) {
        messenger.addTarget(children[i].contentWindow, children[i].id);
      }
    }

    messenger.listen((msg) => {
      msg = JSON.parse(msg);
      this.emit(msg.eventName, msg.args);
    });
    this.messenger = messenger;

  }

  sendToParent(eventName, args) {
    for (var target in this.messenger.targets) {
      this.messenger.targets[target].send(
        this.convertToString(eventName, args)
      );
    }
  }

  sendToChild(child, eventName, args) {
    this.messenger.targets[child.id].send(
      this.convertToString(eventName, args)
    );
  }

  convertToString(eventName, args) {
    var msg = {'eventName': eventName, 'args': args};
    msg = JSON.stringify(msg);
    return msg;
  }

}