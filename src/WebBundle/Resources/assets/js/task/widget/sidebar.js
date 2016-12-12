import Emitter from 'es6-event-emitter'

export default class TaskSidebar extends Emitter{
  constructor({element, url}){
    super();
    this.url = url;
    this.element = $(element);

    this.init();
  }

  init() {
    this.fetchPlugins()
        .then((plugins) => {
          this.plugins = plugins;
          this.renderToolbar();
          this.renderPane();
          this.bindEvent();
        })
        .fail(error => {
          console.log(error);
        })
  }

  fetchPlugins() {
    return $.post(this.url);
  }

  renderToolbar() {
    let html = `
    <div class="dashboard-toolbar">
      <ul class="dashboard-toolbar-nav" id="dashboard-toolbar-nav">
        ${this.plugins.reduce((html, plugin) => {
          return html += `<li data-plugin="${plugin.code}" data-url="${plugin.url}"><a href="#"><div class="mbs es-icon ${plugin.icon}"></div>${plugin.name}</a></li>`;
        }, '')}
      </ul>
    </div>
`;
    this.element.html(html);
  }

  renderPane() {
    let html = this.plugins.reduce((html, plugin) => {
      return html += `<div data-pane="${plugin.code}" class="task-pane"></div>`
    }, '');

    this.element.append(html);
  }

  bindEvent(){

    this.element.find('#dashboard-toolbar-nav').on('click', 'li', (event) => {
      let $btn = $(event.currentTarget);
      let pluginCode = $btn.data('plugin');
      let url = $btn.data('url');
      let $pane = this.element.find(`[data-pane="${pluginCode}"]`);

      if(pluginCode === undefined || url === undefined){
        return;
      }

      if($btn.data('loaded')){
        this.operationContent($btn);
        return;
      }

      $.get(url)
          .then(html => {
            $pane.html(html);
            $btn.data('loaded', true);
            this.operationContent($btn);
          })
    });
  }

  operationContent($btn){
    if($btn.hasClass('active')){
      this.foldContent();
      $btn.removeClass('active');
    }else {
      this.element.find('#dashboard-toolbar-nav li').removeClass('active');
      $btn.addClass('active');
      this.element.find('[data-pane]').hide();
      this.element.find(`[data-pane="${$btn.data('plugin')}"]`).show();
      this.popupContent();
    }
  }

  popupContent(time=0) {
    let side_right = '0px';
    let content_right = '379px';

    this.trigger('popup', content_right, time);
    this.element.animate({
      right: side_right,
    }, time);
  }

  foldContent(time=0){
    let side_right = '-' + this.element.width() + 'px';
    let content_right = '26px';

    this.trigger('fold', content_right, time);
    this.element.animate({
      right: side_right
    }, time)
  }
}