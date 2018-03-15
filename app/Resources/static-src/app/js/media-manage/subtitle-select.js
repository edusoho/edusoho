const convertStatus = (status) => {
  let statusMap = {
    waiting: Translator.trans('subtitle.status.waiting'),
    doing: Translator.trans('subtitle.status.doing'),
    success: Translator.trans('subtitle.status.success'),
    error: Translator.trans('subtitle.status.error'),
    none: Translator.trans('subtitle.status.waiting')
  };
  return statusMap[status];
};

let Select = {
  init(options) {
    this.$el = $(options.id);
    this.options = [];
    this.optionsLimit = options.optionsLimit || false;
    this.eventManager = {};
    this.initParent();
    this.initEvent();
  },
  initParent() {
    let _self = this;
    let $documentFragment = $(document.createDocumentFragment());
    $documentFragment.append(this.templete());
    this.$el.append($documentFragment);
    this.$parentDom = $('.track-select-parent');
    this.$list = $('.track-selcet-list');
    this.$dataShow = this.$parentDom.find('.data-show');
    this.$open = this.$parentDom.find('.track-selcet-open-arrow');
    this.$close = this.$parentDom.find('.track-selcet-close-arrow');
    this.$showBox = this.$parentDom.find('.track-select-show');
  },
  initEvent() {
    let _self = this;
    this.$parentDom
      .delegate('.track-selcet-open-arrow', 'click', this.handleOpen.bind(this))
      .delegate('.track-selcet-close-arrow', 'click', this.handleClose.bind(this))
      .delegate('.delete','click', this.handleDelete.bind(this))
      .delegate('.select-item', 'click', function() {
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
        let name = $(this).find('.value').html();
        let url = $(this).find('.value').attr('url');
        _self.setValue({name:name,url:url});
        _self.handleClose();
      });
    this.$showBox.on('click',this.toggle.bind(this));
    this.on('valuechange',function(){
      this.$dataShow.html(this.getValue().name);
      this.$dataShow.attr('title',this.getValue().name);
    });
    this.on('listchange', function() {
      if(this.optionsLimit && this.options.length >= this.optionsLimit ){
        this.trigger('optionlimit');
      }
      this.$list.html(this.getOptionsStr());
      this.setValue(this.getDefaultOption());
    });
    this.on('optionempty', this.handleOptionEmpty.bind(this));
  },
  templete() {
    return `<div class="track-select-parent">
              <div class="track-select-show">
                <div class="data-show" title="${this.getDefaultOption().name}"></div>
                <span class="track-selcet-open-arrow">
                  <i class="es-icon es-icon-keyboardarrowdown"></i>
                </span>
                <span class="track-selcet-close-arrow" style="display:none;">
                  <i class="es-icon es-icon-keyboardarrowup"></i>
                </span>
              </div>
              <ul class="track-selcet-list" style="display:none;">
                ${this.getOptionsStr()}
              </ul>
            </div>`;
  },
  getDefaultOption() {
    if (this.options.length) {
      return this.options[0];
    } else {
      this.open ? this.handleClose() : '';
      return false;
    }
  },
  getOptionsStr() {
    let _self = this;
    if(!this.options.length){
      this.trigger('optionempty');
    }
    let optionsStr = '';
    this.options.map((option, index) => {
      optionsStr += `<li class="select-item">
                        <div class="value" title="${option.name}" url="${option.url}">
                          ${option.name}
                        </div>
                        <span class="convertStatus convert-${option.convertStatus}">${convertStatus(option.convertStatus)}</span>
                        <i class="es-icon es-icon-close01 delete" data-index="${index}"></i>
                      </li>`;
    });
    return optionsStr;
  },
  setValue(value) {
    if (!value) {
      this.$dataShow.html(Translator.trans('subtitle.no_subtitle_hint'));
      this.trigger('valuechange',false);
      return;
    }
    this.value = value;
    this.trigger('valuechange',this.value);
  },
  getValue() {
    return this.value || { name: Translator.trans('subtitle.no_subtitle_hint')};
  },
  toggle() {
    this.open ? this.handleClose() : this.handleOpen();
  },
  handleOpen() {
    if (!this.options.length) return;
    this.open = true;
    this.$open.hide();
    this.$close.show();
    this.$showBox.addClass('active');
    this.$list.slideDown(200);
  },
  handleClose() {
    this.open = false;
    this.$close.hide();
    this.$open.show();
    this.$showBox.removeClass('active');
    this.$list.slideUp(200);
  },
  handleDelete(e) {
    let el = e.target;
    $(el).parent().remove();
    this.trigger('deleteoption',this.options[$(el).data('index')]);
    this.options.splice($(el).data('index'),1);
    this.trigger('listchange',this.options);
    e.stopPropagation();
  },
  handleOptionEmpty() {
    this.value = '';
    this.trigger('valuechange',false);
  },
  on(event, fn) {
    if (!this.eventManager[event]) {
      this.eventManager[event] = [fn.bind(this)];
    } else {
      this.eventManager[event].push(fn.bind(this));
    }
  },
  trigger(event, data) {
    if (this.eventManager[event]) {
      this.eventManager[event].map(function(fn) {
        fn(data);
      });
    }
  },
  resetOptions(optionsArray) {
    this.options = optionsArray;
    this.trigger('listchange', this.options);
  },
  addOption(option) {
    if (!option.convertStatus) {
      option.convertStatus = 'waiting';
    }
    this.options.push(option);
    this.trigger('listchange');
  }
};

export default Select;