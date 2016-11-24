import Messenger from 'es-messenger';
import Emitter from 'es6-event-emitter';

class EsMessenger extends Emitter {
    constructor(options) {
        super();

        this.name = options.name;
        this.project = options.project;
        this.children = options.children;
        this.type = options.type;
        this.setup();
    }

    setup() {
        let self = this;
        var messenger = new Messenger(this.name, this.project);
        if (this.type == 'child') {
            messenger.addTarget(window.self, 'parent');
        } else if (this.type == 'parent') {
            messenger.addTarget(window.self, 'child');
            var children = this.children;
            for (var i = children.length - 1; i >= 0; i--) {
                messenger.addTarget(children[i].contentWindow, children[i].id);
            }
        }
        messenger.listen(function (msg) {
            msg = JSON.parse(msg);
            self.trigger(msg.eventName, msg.args);
        });
        this.messenger = messenger;
    }

    sendToParent(eventName, args) {
        this.messenger.targets['parent'].send(
            this.convertToString(eventName, args)
        );
    }


    sendToChild(child, eventName, args) {
        this.messenger.targets[child.id].send(
            this.convertToString(eventName, args)
        );
    }

    convertToString(eventName, args) {
        var msg = {"eventName": eventName, "args": args};
        msg = JSON.stringify(msg);
        return msg;
    }

}

export default  EsMessenger;
