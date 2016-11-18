/**
 * Created by Simon on 31/10/2016.
 */

import Chooser from '../chooser';

class CourseFileChoose extends Chooser {

    constructor($container) {
        super();
        this.container = $container;
        this._init();
        this._initEvent();
        console.log("CourseFileChoose");
        $('.chooser-list').perfectScrollbar();
    }

    _init() {
        this._loadList();
    }

    _initEvent() {
        $(this.container).on('click', '.pagination a', this._paginationList.bind(this));
        $(this.container).on('click', '.file-browser-item', this._onSelectFile.bind(this));

        $('.js-choose-trigger').on('click', this._open.bind(this))
    }

    _loadList() {
        let $containter = $('[data-role=course-file-browser]');
        let url = $containter.data('url');
        $.get(url, {'type': $("input[name=type]").val()}, function (html) {
            $containter.html(html);
        });
    }

    _paginationList(event) {
        event.stopImmediatePropagation();
        this._loadList();
    }


    _onSelectFile(event) {
        var $that = $(event.currentTarget);
        var file = $that.data();
        this._onChange(file);
        this._close();
    }

    _onChange(file) {
        var value = file ? JSON.stringify(file) : '';
        console.log('begin courseFileChoose:select');
        this.trigger('select', file);
        $('[data-role="placeholder"]').html(file.name);

        this._fillMinuteAndSecond(file.length);
    }

    _fillMinuteAndSecond(fileLength) {
        let minute = parseInt(fileLength / 60);
        let second = Math.round(fileLength % 60);
        $("#minute").val(minute);
        $("#second").val(second)
    }

}

export default CourseFileChoose;

