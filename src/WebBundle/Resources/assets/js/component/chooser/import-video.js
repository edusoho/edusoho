/**
 * Created by Simon on 31/10/2016.
 */


class FileImport {
    constructor(container) {
        this.container = container;
        this.initEvent();
    }

    initEvent() {
        $(this.container).on('click', '.js-video-import', this._onImport.bind(this));
        $(this.container).on('click', 'js-choose-trigger',this._open)
    }


    _onImport(e) {
        var self = this,
            $btn = $(e.currentTarget),
            $urlInput = $btn.parent().siblings('input'),
            url = $urlInput.val();

        if (url.length == 0) {
            alert(Translator.trans('请输入视频页面地址'));
            return;
        }

        if (!/^[a-zA-z]+:\/\/[^\s]*$/.test(url)) {
            alert(Translator.trans('请输入正确的视频网址'));
            return;
        }

        $btn.button('loading');

        $.get($btn.data('url'), {url: url}, function (video) {
            var media = {
                status: 'none',
                type: video.type,
                source: video.source,
                name: video.name,
                uri: video.files[0].url
            };
            self._onChange(media);
            $urlInput.val('');
        }, 'json').error(function (jqXHR, textStatus, errorThrown) {
            Notify.danger(Translator.trans('读取视频页面信息失败，请检查您的输入的页面地址后重试'));
        }).always(function () {
            $btn.button('reset');
        });

        return;
    }


    _close() {
        $('.file-chooser-main').addClass('hidden');
        $('.file-chooser-bar').removeClass('hidden');
    }

    _open() {
        $('.file-chooser-bar').addClass('hidden');
        $('.file-chooser-main').removeClass('hidden');
    }

    _onChange(file) {
        this._close();
        var value = file ? JSON.stringify(file) : '';
        $('[name="media"]').val(value);
        $('[data-role="placeholder"]').html(file.name);
    }
}

new FileImport($('#chooser-import-panel'));