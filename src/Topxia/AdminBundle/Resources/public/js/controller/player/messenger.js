define(function(require, exports, module) {
  var OriginMessenger = require('messenger');
  var Widget = require('widget');

  var Messenger = Widget.extend({
    attrs: {
      name: '',
      project: '',
      children: [],
      type: '' //enum: parent,child
    },

    events: {

    },

    setup: function() {
      var self = this;
      var messenger = new OriginMessenger(this.get("name"), this.get("project"));

      if (this.get("type") == "child") {
        messenger.addTarget(window.parent, 'parent');
      } else if (this.get("type") == "parent") {
        var children = this.get("children");
        for (var i = children.length - 1; i >= 0; i--) {
          messenger.addTarget(children[i].contentWindow, children[i].id);
        };
      }

      messenger.listen(function(msg) {
        msg = JSON.parse(msg);
        self.trigger(msg.eventName, msg.args);
      })

      this.set("messenger", messenger);
    },

    sendToParent: function(eventName, args) {
      this.get("messenger").targets['parent'].send(
        this.convertToString(eventName, args)
      );
    },

    sendToChild: function(child, eventName, args) {
      this.get("messenger").targets[child.id].send(
        this.convertToString(eventName, args)
      );
    },

    convertToString: function(eventName, args) {
      var msg = {
        "eventName": eventName,
        "args": args
      };
      msg = JSON.stringify(msg);
      return msg;
    }

  });

  module.exports = Messenger;
});