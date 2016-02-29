define(function(require, exports, module) {

    require('jquery.select2');
    require('jquery.select2-css');
    
    exports.run = function() {

        $("#js-course-search").select2({
            placeholder: "选择课程",
            minimumInputLength: 1,
            ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
                url: $("#js-course-search").data('url'),
                dataType: 'json',
                quietMillis: 250,
                data: function (term, page) {
                    return {
                        q: term, // search term
                    };
                },
                results: function (data, page) { // parse the results into the format expected by Select2.
                    var results = [];

                    $.each(data, function(index, item) {

                        results.push({
                            id: item.id,
                            name: item.title
                        });
                    });

                    return {
                        results: results
                    };
                },
                cache: true
            },
            initSelection: function(element, callback) {
                var id = $(element).val();
                if (id !== "") {
                    $.ajax("https://api.github.com/repositories/" + id, {
                        dataType: "json"
                    }).done(function(data) { callback(data); });
                }
            },
             formatSelection: function(item) {
                return item.name;
            },
            formatResult: function(item) {
                return item.name;
            },
            dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
            escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
        });

        $("#js-user-search").select2({
            placeholder: "选择用户",
            minimumInputLength: 1,
            ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
                url: $("#js-user-search").data('url'),
                dataType: 'json',
                quietMillis: 250,
                data: function (term, page) {
                    return {
                        q: term, // search term
                    };
                },
                results: function (data, page) { // parse the results into the format expected by Select2.
                    var results = [];

                    $.each(data, function(index, item) {

                        results.push({
                            id: item.id,
                            name: item.nickname
                        });
                    });

                    return {
                        results: results
                    };
                },
                cache: true
            },
            initSelection: function(element, callback) {
                var id = $(element).val();
                if (id !== "") {
                    $.ajax("https://api.github.com/repositories/" + id, {
                        dataType: "json"
                    }).done(function(data) { callback(data); });
                }
            },
             formatSelection: function(item) {
                return item.name;
            },
            formatResult: function(item) {
                return item.name;
            },
            dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
            escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
        });

    }

});