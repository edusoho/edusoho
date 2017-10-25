class Radio {
  constructor(props) {
    Object.assign(this, {
      parent: document
    }, props);

    this.init();
  }

  init() {
    this.event();
  }

  event() {
    $(this.parent).on('click.cd.radio', this.el, event => this.clickHandle(event));
  }

  clickHandle(event) {
    event.stopPropagation();
    let $this = $(event.currentTarget);

    $this.parent().addClass('checked')
         .siblings().removeClass('checked');
         
    this.cb(event);
  }

  cb() {

  }
}

function radio(props) {
  return new Radio(props);
}

// DATA-API
$(document).on('click.cd.radio.data-api', '[data-toggle="cd-radio"]', function(event) {
  event.stopPropagation();
  let $this = $(event.currentTarget);

  $this.parent().addClass('checked')
       .siblings().removeClass('checked');

});

// HOW TO USE 
// cd.radio({
//   el: '[data-toggle="cd-radio"]',
//   cb() {
//     console.log('这是回调函数')
//   }
// });

export default radio;

