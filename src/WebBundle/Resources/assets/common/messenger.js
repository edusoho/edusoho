import Messenger from 'es-messenger';
import Emitter from 'es6-event-emitter';

class EsMessager extends Emitter {
    constructor(options) {
        super();
        this.name = options.name;
        this.project = options.project;
        this.children = options.children;
        this.type = options.type;
        this.options  = options;
        this.setup();
    }

    setup() {
        let self = this;
        var messenger = new Messenger(this.name, this.project);
        if (this.type == "child") {
            messenger.addTarget(window.parent, 'parent');
            console.log('-child',messenger.targets)
        } else if (this.type == "parent") {
            console.log('--parent',messenger.targets, this.children,this.options );
            var children = this.children;
            for (var i = children.length - 1; i >= 0; i--) {
                messenger.addTarget(children[i].contentWindow, children[i].id);
            }
        }
        messenger.listen(function (msg) {
            console.log('listen',msg);
            msg = JSON.parse(msg);
            self.trigger(msg.eventName, msg.args);
        });
        this.messenger = messenger;
    }

    sendToParent(eventName, args) {
        console.log('sendToParent',eventName, args,this.messenger.targets);
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

export default  EsMessager;
