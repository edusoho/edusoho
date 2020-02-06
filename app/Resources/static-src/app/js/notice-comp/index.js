import Component from './comp';

class Notification extends Component {
  constructor(props) {
    super();

    this.options = {
      positionClass: '',
      template: '',
      animate: {
        enter: 'cd-animated cd-fadeInDownSmall',
        exit: 'cd-animated cd-fadeOutUp'
      },
      offset: 80,
      zIndex: 9999,
    };
    
    Object.assign(this.options, props);

    this.$message = null;
    this.$body = $(document.body);

    this.init();
  }

  init() {
    this.template();
    this.events();
    // this.timeout = setTimeout(() => this.close(), this.options.delay);
  }

  events() {
    $(this.options.parent).on('click', `${this.options.el} .cd-notification-close`, (event) => this.closeEvent(event));
  }

  closeEvent(event) {
    let $this = $(event.currentTarget);
    let $parent = $this.parent();
    $parent.addClass('cd-hide');
    
    setTimeout(() => {
      $parent.remove();
    }, 300);

    this.emit('close', $parent);
  }

  template() {
    this.$message = $(document.createElement('div')).addClass('cd-notification-warp');

    const html = `
      <div class="cd-notification cd-notification-${this.options.positionClass}">
        <div class="cd-notification-title">${this.options.title}</div>
        <div class="cd-notification-content">${this.options.template}</div>
        <button type="button" class="cd-notification-close">
          <i class="cd-icon cd-icon-close"></i>
        </button>
      </div>
    `;

    this.$message.addClass(this.options.animate.enter).css({
      top: this.options.offset + 'px',
      left: 0,
      right: '16px',
      'z-index': this.options.zIndex,
      position: 'fixed',
    });

    this.$message.html(html).appendTo(this.$body);

    clearInterval(this.timeout);
  }
}

export default Notification;


new Notification({
  positionClass: 'right',
  title: '直播课程提醒',
  template: '<div>我来测试一下</div>',
});