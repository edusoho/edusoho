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
        url: '/importer/{type}/import',
        defaults: {
            'checkType': "ignore",
            'chunkSize': 50,
            '__progress': 0,
            '__current': 0,
            '__total': 0,
            '__status': STATUS.WAIT
        },

        chunkUpload: function () {

            var self = this;

            var chunkData = [].concat.apply([], _.map(this.get('importData'), function (elem, i) {
                return i % self.get('chunkSize')? [] : [self.get('importData').slice(i, i + self.get('chunkSize'))];
            }));
            this.set('__total', chunkData.length);

            if(chunkData.length === 0){
                this.set('__progress', 100);
                this.set('__status', STATUS.COMPLETE);
            }

            var privateAttr = ['__total', '__current', 'chunkSize', 'status', '__progress', '__status', 'checkInfo'];
            var postData = self.toJSON();

            _.each(privateAttr, function (attr) {
                delete postData[attr];
            });

            _.each(chunkData, function (data) {
                postData.importData = data;
                $.post(self.url.replace(/\{type\}/, self.get('type')), postData).then(function (response) {
                    self.set('__current', self.get('__current') + 1);
                    var current = self.get('__current');
                    var total = self.get('__total');
                    self.set('__progress', current / total * 100);
                    if(current === total){
                        self.set('__status', STATUS.COMPLETE);
                    }else{
                        self.set('__status', STATUS.PROGRESS);
                    }
                }, function (error) {
                    self.set('__status', STATUS.ERROR);
                });
            });

        }
    });
});
