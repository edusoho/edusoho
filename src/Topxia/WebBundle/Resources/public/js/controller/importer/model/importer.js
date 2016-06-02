define(function (require, exports, module) {
    "use strict";

    var Backbone = require('backbone');
    var _ = require('underscore');

    var STATUS = {
        WAIT: 'wait',
        COMPLETE: 'complete',
        ERROR: 'error',
        PROGRESS: 'progress'
    };

    module.exports = Backbone.Model.extend({
        defaults: {
            'checkType': "ignore",
            'chunkSize': 8,
            '__progress': 0,
            '__current': 0,
            '__total': 0,
            '__status': STATUS.WAIT,
            '__quantity': 0
        },

        chunkImport: function () {

            var self = this;

            var chunkData = [].concat.apply([], _.map(this.get('importData'), function (elem, i) {
                return i % self.get('chunkSize')? [] : [self.get('importData').slice(i, i + self.get('chunkSize'))];
            }));
            this.set('__total', chunkData.length);

            if(chunkData.length === 0){
                this.set('__progress', 100);
                this.set('__status', STATUS.COMPLETE);
                return;
            }

            var privateAttr = ['__quantity', '__total', '__current', 'chunkSize', 'status', '__progress', '__status', 'checkInfo', 'checkUrl', 'importUrl'];
            var postData = self.toJSON();

            _.each(privateAttr, function (attr) {
                delete postData[attr];
            });

            (function importFunc(index) {
                postData.importData = chunkData[index];
                $.ajax(self.get('importUrl'),
                    {
                        'data': postData,
                        'method': 'post',
                        'dataType': 'json'
                    }
                ).then(function (response) {
                    self.set('__current', self.get('__current') + 1);
                    var current = self.get('__current');
                    var total = self.get('__total');
                    var quantity = self.get('__quantity');
                    self.set('__progress', current / total * 100);
                    self.set('__quantity', quantity + chunkData[index].length);
                    if(current === total){
                        self.set('__status', STATUS.COMPLETE);
                    }else{
                        self.set('__status', STATUS.PROGRESS);
                        importFunc(index + 1);
                    }
                }, function (error) {
                    self.set('__status', STATUS.ERROR);
                });
            })(0);
        }
    });
});
