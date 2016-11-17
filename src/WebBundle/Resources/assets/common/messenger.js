import Messenger from 'es-messenger';
import Emitter from 'es6-event-emitter';

class EsMessager extends Emitter {
    constructor(name, project, children, type) {
        super();
        this.name = name | '';
        this.project = project | '';
        this.children = children | [];
        this.type = type | '';
        this.messenger = ''

        this.setup();
    }

    setMessenger(messenger) {
        this.messenger = messenger
    }

    getMessenger() {
        return this.messenger;
    }

    setup() {
        let self = this;
        var messenger = new Messenger(this.name, this.project);
        if (this.type == "child") {
            messenger.addTarget(window.parent, 'parent');
        } else if (this.type == "parent") {
            var children = this.children;
            for (var i = children.length - 1; i >= 0; i--) {
                messenger.addTarget(children[i].contentWindow, children[i].id);
            }
        }
        messenger.listen(function (msg) {
            msg = JSON.parse(msg);
            self.trigger(msg.eventName, msg.args);
        })
        this.setMessenger(messenger);
    }

    sendToParent(eventName, args) {
        this.getMessenger().targets['parent'].send(
            this.convertToString(eventName, args)
        );
    }


    sendToChild(child, eventName, args) {
        this.getMessenger().targets[child.id].send(
            this.convertToString(eventName, args)
        );
    }

    convertToString(eventName, args) {
        var msg = {"eventName": eventName, "args": args};
        msg = JSON.stringify(msg);
        return msg;
    }

}

module.exports = EsMessager;
