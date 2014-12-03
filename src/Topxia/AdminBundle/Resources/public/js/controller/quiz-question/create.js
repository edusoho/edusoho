define(function(require, exports, module) {

    var QuestionBase = require('./creator/question-base');
    var Choice = require('./creator/question-choice');
    var Determine = require('./creator/question-determine');
    var Essay = require('./creator/question-essay');
    var Fill = require('./creator/question-fill');
    var TagChooser = require('tag-chooser');
    var TagTreeChooser = require('tag-tree-chooser');
    
    exports.run = function() {
        var type = $('#question-creator-widget').find('[name=type]').val().replace(/\_/g, "-");
        var QuestionCreator;
        switch (type) {
            case 'single-choice':
            case 'uncertain-choice':
            case 'choice':
                QuestionCreator = Choice;
                break;
            case 'determine':
                QuestionCreator = Determine;
                break;
            case 'essay':
                QuestionCreator = Essay;
                break;
            case 'fill':
                QuestionCreator = Fill;
                break;
            default:
                QuestionCreator = QuestionBase;
        }

        var creator = new QuestionCreator({
            element: '#question-creator-widget'
        });

        _initTagChooer();
        _initMainknowledgeTagChooer();
        _initRelatedknowledgeTagChooer();

        function _initTagChooer()
        {
            var choosedTags = [];
            if (creator.$('input[name=tagIds]').val().length >0) {
                choosedTags = creator.$('input[name=tagIds]').val().split(',');
            }

            var chooser = new TagChooser({
                element: '#tag-chooser',
                sourceUrl: $('#tag-chooser').data('sourceUrl'),
                queryUrl: $('#tag-chooser').data('queryUrl'),
                matchUrl: $('#tag-chooser').data('matchUrl'),
                maxTagNum: 15,
                choosedTags: choosedTags
            });

            chooser.on('change', function(tags) {
                var tagIds = [];
                $.each(tags, function(i, tag) {
                    tagIds.push(tag.id);
                });
                creator.$('input[name=tagIds]').val(tagIds.join(','));
            });
        }

        function _initMainknowledgeTagChooer()
        {
            var choosedTags = [];
            if (creator.$('input[name=mainKnowledgeId]').val().length >0) {
                choosedTags = creator.$('input[name=mainKnowledgeId]').val().split(',');
            }


            var chooserTreeForMainKnowlege = new TagTreeChooser({
              element: '#mainknowledge-chooser',
              sourceUrl: $('#mainknowledge-chooser').data('sourceUrl'),
              queryUrl: $('#mainknowledge-chooser').data('queryUrl'),
              matchUrl: $('#mainknowledge-chooser').data('matchUrl'),
              maxTagNum: 1,
              choosedTags: choosedTags
            });

            chooserTreeForMainKnowlege.on('change', function(tags) {
                var tagIds = [];
                $.each(tags, function(i, tag) {
                    tagIds.push(tag.id);
                });
                creator.$('input[name=mainKnowledgeId]').val(tagIds.join(','));
            });

        }

        function _initRelatedknowledgeTagChooer()
        {

            var choosedTags = [];
            if (creator.$('input[name=relatedKnowledgeIds]').val().length >0) {
                choosedTags = creator.$('input[name=relatedKnowledgeIds]').val().split(',');
            }


            var chooserTreeForRelatedKnowlege = new TagTreeChooser({
                element: '#relatedknowledges-chooser',
                sourceUrl: $('#relatedknowledges-chooser').data('sourceUrl'),
                queryUrl: $('#relatedknowledges-chooser').data('queryUrl'),
                matchUrl: $('#relatedknowledges-chooser').data('matchUrl'),
                maxTagNum: 15,
                choosedTags: choosedTags
            });

            chooserTreeForRelatedKnowlege.on('change', function(tags) {
                var tagIds = [];
                $.each(tags, function(i, tag) {
                    tagIds.push(tag.id);
                });
                creator.$('input[name=relatedKnowledgeIds]').val(tagIds.join(','));
            });

        }

    };

});