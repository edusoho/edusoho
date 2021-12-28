import Emitter from 'component-emitter';
import { chapterAnimate } from 'app/common/widget/chapter-animate';
import Cookies from 'js-cookie';
export default class TaskSidebar extends Emitter {
  constructor({element, url}) {
    super();
    this.url = url;
    this.element = $(element);
    this.init();
  }

  init() {
    // this.fixIconInChrome();
    this.fetchPlugins()
      .then((plugins) => {
        this.plugins = plugins;
        this.renderToolbar();
        this.renderPane();
        this.element.hide().show();
        this.bindEvent();
      })
      .fail(() => {
      });
  }

  fetchPlugins() {
    return $.post(this.url);
  }

  // 修复字体图标在chrome下，加载两次从而不能显示的问题
  fixIconInChrome() {
    let html = '<i class="es-icon es-icon-chevronleft"></i>';
    this.element.html(html);
  }

  renderToolbar() {
    const showSidebar = Cookies.get('show-sidebar');
    let className = showSidebar != 0 ? 'active' : '';
    let html = `
      <div class="dashboard-toolbar js-dashboard-toolbar ${className}">
        <i class="es-icon es-icon-angledoubleleft"></i>
      </div>
    `;
    this.element.html(html);
  }

  renderPane() {
    let navItemWidth = 100 / this.plugins.length;

    let html = `
      <ul class="dashboard-nav js-dashboard-nav clearfix">
        ${this.plugins.reduce((html, plugin) => {
          return html += `<li class="dashboard-nav__item pull-left" style="width: ${navItemWidth}%" data-plugin="${plugin.code}" data-url="${plugin.url}">${plugin.name}</li>`; 
        }, '')}
      </ul>`;

    html += this.plugins.reduce((html, plugin) => {
      return html += `<div data-pane="${plugin.code}" class=" ${plugin.code}-pane js-sidebar-pane" ><div class="${plugin.code}-pane-body js-sidebar-pane-body"></div></div>`;
    }, '');
    this.element.append(html);
  }

  bindEvent() {
    this.element.find('.js-dashboard-toolbar').on('click', (event) => {
      let $btn = $(event.currentTarget);
      this.operationContent($btn);
    });

    this.element.find('.js-dashboard-nav').on('click', 'li', (event) => {
      let $btn = $(event.currentTarget);
      let pluginCode = $btn.data('plugin');
      let url = $btn.data('url');
      let $pane = this.element.find(`[data-pane="${pluginCode}"]`);
      let $paneBody = $pane.find('.js-sidebar-pane-body');
      if (pluginCode === undefined || url === undefined) {
        return;
      }

      this.element.find('.js-dashboard-nav li').removeClass('active');
      $btn.addClass('active');
      this.element.find('[data-pane]').hide();
      this.element.find(`[data-pane="${pluginCode}"]`).show();
  
      if ($btn.data('loaded')) {
        return;
      }

      $.get(url)
        .then(html => {
          $paneBody.html(html);
          $pane.perfectScrollbar();
          $btn.data('loaded', true);
          this.emit($btn.data('plugin')+'-loaded', $paneBody);
        });
    });

    this.element.find('.js-dashboard-nav li')[0].click();
  }

  operationContent($btn) {
    if ($btn.hasClass('active')) {
      this.foldContent();
      Cookies.set('show-sidebar', 0);
      $btn.removeClass('active');
    } else {
      $btn.addClass('active');
      Cookies.set('show-sidebar', 1);
      this.popupContent();
    }
  }

  popupContent(time = 500) {
    let side_right = '0px';
    let width = $('#dashboard-sidebar').width();
    
    let content_right = width +  35 +'px';

    this.emit('popup', content_right, time);
    this.element.animate({
      right: side_right
    }, time);
  }

  foldContent(time = 500) {
    let side_right = '-' + this.element.width() + 'px';
    let content_right = '35px';

    this.emit('fold', content_right, time);
    this.element.animate({
      right: side_right
    }, time);
  }

  reload() {
    const $currentPane = this.element.find('.js-sidebar-pane:visible');
    const pluginCode = $currentPane.data('pane');
    $currentPane.undelegate();
    this.element.find('#dashboard-toolbar-nav').children(`[data-plugin="${pluginCode}"]`)
      .data('loaded', false)
      .click();
  }

  listEvent() {
    if($('.js-sidebar-pane:visible .task-list-pane-body').length) {
      chapterAnimate();
    }
  }
}
