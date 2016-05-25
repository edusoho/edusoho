define(function (require, exports, module) {
    "use strict";

    var Backbone = require('backbone');
    var _ = require('underscore');

    module.exports = Backbone.Model.extend({
        url: '/excel/importer',
        defaults: {
            "checkType": "ignore",
            "chunkSize": 100
        },

        chunkUpload: function () {
            if (_.isEmpty(this.get('importData'))) {
                return;
            }

            var self = this;

            var chunkData = [].concat.apply([], _.map(this.get('importData'), function (elem, i) {
                return i % self.get('chunkSize')? [] : [self.get('importData').slice(i, i + self.get('chunkSize'))];
            }));

            _.each(chunkData, function (data) {
                var postData = _.extend(self.toJSON(), {importerData: data});
                delete postData['importData'];
                delete postData['chunkSize'];
                delete postData['status'];
                $.post(self.url, postData).done(function () {

                });
            });

        }
    });
});
