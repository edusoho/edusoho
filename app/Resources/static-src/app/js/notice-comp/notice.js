import Component from './comp';

class Notification extends Component {
  constructor(props) {
    super();

    this.options = {
      positionClass: 'bottomRight',
      template: '',
      title: '',
      zIndex: 9999,
    };
    
    Object.assign(this.options, props);

    this.$notification = null;
    this.$body = $(document.body);

    this.init();
  }

  init() {
    this.template();
    this.events();
  }

  events() {
    $(document).on('click', '.cd-notification-close', (event) => this.closeEvent(event));
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
    this.$notification = $(document.createElement('div')).addClass('cd-notification-warp');

    const html = `
      <div class="cd-notification ${this.options.positionClass}">
        <div class="cd-notification-title">${this.options.title}</div>
        <div class="cd-notification-content">${this.options.template}</div>
        <button type="button" class="cd-notification-close">
          <i class="cd-icon cd-icon-close"></i>
        </button>
      </div>
    `;

    let topRightData = {
      top: '80px',
      right: '36px'
    };
    let topLeftData = {
      top: '80px',
      left: '16px'
    };

    let bottomLeftData = {
      bottom: '40px',
      left: '16px'
    };

    let bottomRightData = {
      bottom: '40px',
      right: '36px'
    };

    let test = {};
    switch (this.options.positionClass) {
    case 'rightTop':
      test = Object.assign({}, topRightData);
      break;
    case 'leftTop':
      test = Object.assign({}, topLeftData);
      break;
    case 'leftBottom':
      test = Object.assign({}, bottomLeftData);
      break;
    case 'rightBottom':
      test = Object.assign({}, bottomRightData);
      break;
    default:
      break;
    }
    const data = {
      'z-index': this.options.zIndex,
      'position': 'fixed',
    };

    const finalData = Object.assign(data, test);
    this.$notification.css(finalData);

    this.$notification.html(html).appendTo(this.$body);
    clearInterval(this.timeout);
  }
}

export default Notification;

