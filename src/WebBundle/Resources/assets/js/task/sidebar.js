import NotePlugin from './plugins/note/plugin';
import QuestionPlugin from './plugins/question/plugin';

class SideBar {
  constructor(option) {
    this.courseId = option.courseId;
    this.taskId = null;
    this.task = null;
    this.activePlugins = option.activePlugins;
    this.plugins = {};
    this._tasks = {};
    this._currentPane = null;
    this.$dashboardsidebar = $('#dashboard-sidebar');
    this.$dashboardcontent = $('#dashboard-content');
    this._init();
  }

  _init() {
    this.taskId= 1;//@TODO 获取当前任务的ID
    this._registerPlugin(new NotePlugin(this));
    this._registerPlugin(new QuestionPlugin(this));
    this._initPlugin();
  }

  _registerPlugin(plugin) {
    this.plugins[plugin.code] = plugin;
    if (plugin.onRegister) {
      plugin.onRegister();
    }
  }

  _initPlugin() {
    let html = '';
    $.each(this.activePlugins, (i,name)=>{
      let plugin = this.plugins[name];
      html += '<li data-plugin="' + plugin.code + '" data-noactive="' + plugin.noactive + '"><a href="#"><p class="' + plugin.iconClass + '"> </p>' + plugin.name + '</a></li>'
    });
    $('#dashboard-toolbar-nav').html(html).on('click', 'li[data-plugin]',(event)=>{
      let $this = $(event.currentTarget);
      if ($this.hasClass('active')) {
        this._rendBar($this,false);
        this._renderSiderBar(false);
        return;
      }
      if(!this._currentPane || $this.data('plugin') != this._currentPane ) {
        this.plugins[$this.data('plugin')].execute();
      }
      this._rendBar($this,true);
      this._renderSiderBar(true);
    });
  }

  _renderSiderBar(show) {
    let sider_right = '0px';
    let content_right = '461px';
    if(!show) {
      sider_right = '-'+this.$dashboardsidebar.width()+'px';
      content_right = '26px';
    }
    this.$dashboardsidebar.animate({
      right: sider_right,
    });
    this.$dashboardcontent.animate({
      right: content_right,
    });
  }

  _rendBar($item,show) {
    show ? $item.addClass('active').siblings('li').removeClass('active') : $item.removeClass('active');
  }

  _getPaneContainer() {
    return $('.dashboard-sidebar-content');
  }

  _getPane(name) {
    let $pane = this._getPaneContainer().find('[data-pane=' + name + ']');
    if ($pane.length === 0) {
      return undefined;
    }
    return $pane;
  }

  createPane(name) {
    let $pane = this._getPane(name);
    if (!$pane) {
      $pane = $('<div data-pane="' + name + '" class="dashboard-pane ' + name +'-pane"></div>').appendTo(this._getPaneContainer());
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