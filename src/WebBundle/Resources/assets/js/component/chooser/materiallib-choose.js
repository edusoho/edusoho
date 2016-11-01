/**
 * Created by Simon on 31/10/2016.
 */

var $parentiframe = $(window.parent.document).find('#task-manage-content-iframe');

class MaterialLibChoose {

    constructor($modal, mediaType) {
        this.modal = $modal;
        this.mediaType = mediaType;
        this.loadShareingContacts = false;
        this._init();
        this._initEvent();
    }

    _init() {
        this._loadList();
        this._initTabs();
    }

    _initEvent() {
        $(this.modal).on('click', '.js-material-type', this._switchFileSource.bind(this));
        $(this.modal).on('change', '.js-file-owner', this._fileterByFileOwner)
        $(this.modal).on('click', '.js-browser-search', this._fileterByFileName.bind(this));
        $(this.modal).on('click', '.pagination a', this._paginationList.bind(this));
        $(this.modal).on('click', '.file-browser-item', this._onSelectFile.bind(this));
        $(this.container).on('click', 'js-choose-trigger',this._open)
    }

    _initTabs() {
        $("#material a").click(function (e) {
            e.preventDefault();
            $(this).tab('show');
            $parentiframe.height($parentiframe.contents().find('body').height());
        });
    }

    _loadList() {
        let url = $('.js-browser-search').data('url');

        let params = {};
        params.sourceFrom = $('input[name=sourceFrom]').val();
        params.page = $('input[name=page]').val();
        $('.js-material-list').load(url, params, function () {
            console.log('page is on loading');
            $parentiframe.height($parentiframe.contents().find('body').height());
        })

    }

    _paginationList(event) {
        event.stopImmediatePropagation();
        let $that = $(event.currentTarget);
        console.log('_paginationList');
        $('input[name=page]').val($that.html());
        this._loadList();
    }

    _switchFileSource(event) {
        let that = event.currentTarget;
        var type = $(that).data('type');
        console.log('type', type)
        $(that).addClass('active').siblings().removeClass('active');
        $('input[name=sourceFrom]').val(type);
        switch (type) {
            case 'my' :
                $('.js-file-name-group').removeClass('hidden');
                $('.js-file-owner-group').addClass('hidden');
                break;
            case 'sharing':
                this._loadSharingContacts.call(this, $(that).data('sharingContactsUrl'));
                $('.js-file-name-group').removeClass('hidden');
                $('.js-file-owner-group').removeClass('hidden');
                break;
            default:
                $('.js-file-name-group').addClass('hidden');
                $('.js-file-owner-group').addClass('hidden');
                break;
        }
        this._loadList();
    }

    _loadSharingContacts(url) {
        if (this.loadShareingContacts == true) {
            console.error('teacher list has been loaded');
            return;
        }
        console.log('teacher list is  loaded');

        $.get(url, function (teachers) {
            if (Object.keys(teachers).length > 0) {
                var html = `<option value=''>${Translator.trans('请选择老师')}</option>`;
                $.each(teachers, function (i, teacher) {
                    html += `<option value='${teacher.id}'>${teacher.nickname} </option>`
                });

                $(".js-file-owner", self.element).html(html);
            }

        }, 'json');
        this.loadShareingContacts = true;
    }


    _fileterByFileName() {
        let keyword = $('.js-file-name').val();

        let params = {};
        params.keyword = keyword
        params.sourceFrom = $('input[name=sourceFrom]').val();
        params.page = $('input[name=page]').val();

        let url = $('.js-browser-search').data('url');
        $('.js-material-list').load(url, params, function () {
            console.log('page is reloading')
        })
    }

    _fileterByFileOwner() {
        let params = {};
        params.keyword = $('.js-file-name').val();
        params.currentUserId = $('.js-file-owner option:selected').val();
        params.sourceFrom = $('input[name=sourceFrom]').val();
        params.page = $('input[name=page]').val();

        let url = $('.js-browser-search').data('url');
        $('.js-material-list').load(url, params, function () {
            console.log('page is reloading')
        })
    }

    _close() {
        $('.file-chooser-main').addClass('hidden');
        $('.file-chooser-bar').removeClass('hidden');
    }

    _open() {
        $('.file-chooser-bar').addClass('hidden');
        $('.file-chooser-main').removeClass('hidden');
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
        $('input[name=mediaId]').val(file.id);
        $('[data-role="placeholder"]').html(file.name);
    }

}


new MaterialLibChoose($('#chooser-material-panel'), 'video');
