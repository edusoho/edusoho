import NotePlugin from '../plugins/note/plugin';
import QuestionPlugin from '../plugins/question/plugin';
import TaskPlugin from '../plugins/task/plugin';
import 'store';

class SideBar {

  constructor({ element, courseId, taskId, activePlugins }) {
    this.$element = $(element);
    this.courseId = courseId;
    this.taskId = taskId;
    this.activePlugins = activePlugins;
    this.plugins = {};
    this._currentPane = null;
    console.log($(element));
    this.$dashboardsidebar = this.$element.find('#dashboard-sidebar');
    // this.$dashboardPane = this.$element.find('#dashboard-pane');
    this.$dashboardcontent = $('#dashboard-content');
    this._init();
  }

  _init() {
    this._registerPlugin(new TaskPlugin(this));
    this._registerPlugin(new NotePlugin(this));
    this._registerPlugin(new QuestionPlugin(this));
    this._initPlugin();
    this._isRenderSiderBar();
  }

  _registerPlugin(plugin) {
    this.plugins[plugin.code] = plugin;
    if (plugin.onRegister) {
      plugin.onRegister();
    }
  }

  _initPlugin() {
    let html = '';
    $.each(this.activePlugins, (i, name) => {
      let plugin = this.plugins[name];
      html += '<li data-plugin="' + plugin.code + '" data-noactive="' + plugin.noactive + '"><a href="#"><div class="mbs ' + plugin.iconClass + '"></div>' + plugin.name + '</a></li>'
    });
    $('#dashboard-toolbar-nav').append(html).on('click', 'li[data-plugin]', (event) => {
      let $this = $(event.currentTarget);
      if ($this.hasClass('active')) {
        this._rendBar($this, false);
        this._renderSiderBar(false);
        return;
      }
      if (!this._currentPane || $this.data('plugin') != this._currentPane) {
        this.plugins[$this.data('plugin')].execute();
      }
      this._rendBar($this, true);
      this._renderSiderBar(true);
    });
  }

  _renderSiderBar(show, time = '') {
    let sider_right = '0px';
    let content_right = '379px';
    if (!show) {
      sider_right = '-' + this.$dashboardsidebar.width() + 'px';
      content_right = '26px';
    }
    this.$dashboardsidebar.animate({
      right: sider_right,
    }, time);
    this.$dashboardcontent.animate({
      right: content_right,
    }, time);
  }

  _rendBar($item, show) {
    show ? $item.addClass('active').siblings('li').removeClass('active') : $item.removeClass('active');
  }

  _getPaneContainer() {
    return this.$element;
  }

  _getPane(name) {
    let $pane = this._getPaneContainer().find('[data-pane=' + name + ']');
    if ($pane.length === 0) {
      return undefined;
    }
    return $pane;
  }

  _isRenderSiderBar() {
    if (!store.get('USER-START-LEARN')) {
      store.set('USER-START-LEARN', true);
      this._renderSiderBar(true, '2000');
      window.setTimeout(() => {
        this._renderSiderBar(false, '2000');
      }, 2000);
    }
  }

  createPane(name) {
    let $pane = this._getPane(name);
    if (!$pane) {
      $pane = $('<div data-pane="' + name + '" class="dashboard-pane ' + name + '-pane"></div>').appendTo(this.$dashboardsidebar);
    }
    return $pane;
  }

  showPane(name) {
    this._getPaneContainer().find('[data-pane]').hide();
    this._getPaneContainer().find('[data-pane=' + name + ']').show();
    this._getPaneContainer().show();
    this._currentPane = name;
    $('.hide-pane').show();
  }
}

export default SideBar;
