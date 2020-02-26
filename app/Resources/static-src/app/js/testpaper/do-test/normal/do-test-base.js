import DoTestBase from 'app/js/testpaper/widget/do-test-base';

import {
    initScrollbar,
    testpaperCardFixed,
    testpaperCardLocation,
    initWatermark,
    onlyShowError
} from 'app/js/testpaper/widget/part';

class DoTestpaper extends DoTestBase {
    constructor($container) {
        super($container);
        this.$timePauseDialog = this.$container.find('#time-pause-dialog');
        this.$timer = $container.find('.js-testpaper-timer');
        this._init();
    }

    _init() {
        initScrollbar();
        initWatermark();
        testpaperCardFixed();
        testpaperCardLocation();
        onlyShowError();
        this._initTimer();
        this.$container.on('click', '.js-btn-pause', event => this._clickBtnPause(event));
        this.$container.on('click', '.js-btn-resume', event => this._clickBtnReume(event));
    }

    _initTimer() {
        if (this.$timer) {
            this.$timer.timer({
                countdown: true,
                duration: this.$timer.data('time'),
                format: '%H:%M:%S',
                callback: () => {
                    this.$container.find('#time-finish-dialog').modal('show');
                    clearInterval(this.$usedTimer);
                    if ($('input[name="preview"]').length == 0) {
                        this._submitTest(this.$container.find('[data-role="paper-submit"]').data('url'));
                    }
                },
                repeat: true,
                start: () => {
                    this.usedTime = 0;
                }
            });
        }
    }

    _clickBtnPause(event) {
        let $btn = $(event.currentTarget).toggleClass('active');
        if ($btn.hasClass('active')) {
            this.$timer.timer('pause');
            clearInterval(this.$usedTimer);
            this.$timePauseDialog.modal('show');
        } else {
            this.$timer.timer('resume');
            this._initUsedTimer();
            this.$timePauseDialog.modal('hide');
        }
    }

    _clickBtnReume() {
        this.$timer.timer('resume');
        this._initUsedTimer();
        this.$container.find('.js-btn-pause').removeClass('active');
        this.$timePauseDialog.modal('hide');
    }
}

export default DoTestpaper;

