/**
 * Created by Simon on 31/10/2016.
 */
var $parentiframe = $(window.parent.document).find('#task-manage-content-iframe');

class CourseFileChoose {

    constructor($container, mediaType) {
        this.container = $container;
        this.mediaType = mediaType;
        this._init();
        this._initEvent();
    }

    _init() {
        this._loadList();
    }

    _initEvent() {
        $(this.container).on('click', '.pagination a', this._paginationList.bind(this));
        $(this.container).on('click', '.file-browser-item', this._onSelectFile.bind(this));

        $('.js-choose-trigger').on('click', this._open)
    }

    _loadList() {
        let $containter = $('[data-role=course-file-browser]');
        let url = $containter.data('url');

        $containter.load(url, function () {
            console.log('page is on loading');
        })
    }

    _paginationList(event) {
        event.stopImmediatePropagation();
        let $that = $(event.currentTarget);
        console.log('_paginationList');
        $('input[name=page]').val($that.html());
        this._loadList();
    }


    _close() {
        $('.file-chooser-main').addClass('hidden');
        $('.file-chooser-bar').removeClass('hidden');
        $parentiframe.height($parentiframe.contents().find('body').height());
    }

    _open() {
        $('.file-chooser-bar').addClass('hidden');
        $('.file-chooser-main').removeClass('hidden');
        $parentiframe.height($parentiframe.contents().find('body').height());
    }

    _onSelectFile(event) {
        var $that = $(event.currentTarget);
        var file = $that.data();
        this._onChange(file);
        this._close();
        console.log($that, $that.data())
    }

    _onChange(file) {
        var value = file ? JSON.stringify(file) : '';
        $('[name="media"]').val(value);
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


new CourseFileChoose($('#chooser-course-panel'), 'video');
