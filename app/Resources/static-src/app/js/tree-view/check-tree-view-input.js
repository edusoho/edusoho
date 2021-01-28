import CheckTreeview from './check-tree-view';

export default class CheckTreeviewInput {

  static getDefaultOpts() {
    return {
      treeviewText: '.js-treeview-text',
      treeviewSelect: '.js-treeview-select',
      treeviewSelectMenu: '.js-treeview-select-menu',
      treeviewIpt: '.js-treeview-ipt',
      treeviewData: '.js-treeview-data',
      disableNodeCheck: false,
      saveColumn: 'id',
      showCheckbox: true,
      transportChildren: false,
      transportParent: false,
    };
  }

  constructor(opts) {
    if ('single' == opts.selectType) {
      opts.showCheckbox = false;
    }

    Object.assign(this, CheckTreeviewInput.getDefaultOpts(), opts);
    if (!this.$elem.length) {
      return;
    }

    if ('single' == this.selectType) {
      this.disableNodeCheck = true;
    }
    this.excludeIds = {};
    this.init();
  }

  init() {
    this.initTreeview();
    this.initEvent();
    this.hideEvent();
  }

  initTreeview() {
    let data = this.getData() ? this.getData() : '{}';

    let nodeArr = JSON.parse(data);

    let checkStr = this.$elem.find(this.treeviewIpt).val() ?  this.$elem.find(this.treeviewIpt).val() : '';
    let columnName = this.$elem.data('checkColumnName');
    let checkArr = checkStr.split(',');
    let checkMap = {};
    let tmpArr = [].concat(nodeArr);

    for (let i = 0; i < checkArr.length; i++) {
      checkMap[checkArr[i]] = true;
    }

    while (tmpArr.length > 0) {
      let node = tmpArr.pop();

      node.state = {
        expanded: false
      };

      if (node.selectable != undefined && !node.selectable) {
        node.state.disabled = true;
        node.state.checked = false;
        node.hideCheckbox = true;
      }

      if (checkMap[node[columnName]]) {
        node.hideCheckbox = false;
        node.state.checked = true;
      }

      if (node.expanded) {
        node.state.expanded = true;
      }

      node.state.selected = false;

      if(node.children) {
        tmpArr = tmpArr.concat(node.children);
      }
    }

    this.checkTreeview = new CheckTreeview(this.$elem.find(this.treeviewSelectMenu), {
      data: nodeArr,
      disableNodeCheck: this.disableNodeCheck,
      showCheckbox: this.showCheckbox,
      transportParent: this.transportParent,
    });
    const node = this.checkTreeview.getCheckNodes();

    if (node.length) {
      let name = node.reduce(function(tot, item) {
        return tot + (tot && ',') + item.name + ' ';
      }, '');
      this.$elem.find(this.treeviewText).val(name);
    }
  }

  initEvent() {
    let _self = this;
    this.$elem.on('focus', _self.treeviewText, (e) => {
      $(_self.treeviewSelectMenu).removeClass('is-active');
      $(e.currentTarget).parents(_self.treeviewSelect).find(_self.treeviewSelectMenu).addClass('is-active');
    });

    this.$elem.find(_self.treeviewSelect).on('click', (e) => {
      const node = _self.checkTreeview.getCheckNodes();

      let name = '';

      let len = Math.min(node.length, 10);

      for (let i = 0 ; i < len; i++) {
        if (!node[i]['disable'] && !node[i]['exclude']) {
          name = name + (name && ',') + node[i].name + ' ';
        }
      }

      if (len != node.length) {
        name = name + '...';
      }

      let $queryIds = [];
      let id = node.reduce(function(tot, item) {
        if (!item['disable'] && !item['exclude']) {

          if ($queryIds.indexOf(item['parentId']) >= 0 && !_self.transportChildren) {
            $queryIds.push(item['nodeId']);
            return tot;
          }

          $queryIds.push(item['nodeId']);
          return tot + (tot && ',') + item[_self.saveColumn];
        }

        return tot;
      },'');

      if(_self.nodeChange) {
        const treeviewIptVal = $(e.currentTarget).find(_self.treeviewIpt).val();

        if(treeviewIptVal == '' || treeviewIptVal !=id) {
          _self.nodeChange(id);
        }

      }

      $(e.currentTarget).find(_self.treeviewText).val(name);
      $(e.currentTarget).find(_self.treeviewIpt).val(id);
      $(e.currentTarget).find(_self.treeviewIpt).trigger('change');
      e.stopPropagation();
    });

    if ('single' == this.selectType) {
      this.$elem.on('nodeElementSelect', (e, node) => {
        if (!node.selectable) {
          return;
        }

        if (node.exclude) {
          return false;
        }

        let tree = this.checkTreeview.getTreeObject();
        tree.uncheckAll();
        this.$elem.find(this.treeviewIpt).val('');
        tree.checkNode(node.nodeId);
        $(this.treeviewSelectMenu).removeClass('is-active');
      });
    } else {
      this.$elem.on('nodeElementSelect', (e, node) => {
        let $nodeElemet = _self.$elem.find(`[data-nodeid=${node.nodeId}]`);

        if (node.exclude && node.selectable) {
          let tree = this.checkTreeview.getTreeObject();
          if (_self.excludeIds[node.nodeId]) {
            tree.uncheckNode(node.nodeId);
            _self.excludeIds[node.nodeId] = false;
          } else {
            tree.checkNode(node.nodeId);
            tree.expandNode(node.nodeId);
            _self.excludeIds[node.nodeId] = true;
          }
        }

      });
    }

  }

  getData() {
    let text = this.$elem.find(this.treeviewData).text();

    return text ? text : this.$elem.find(this.treeviewData).val();
  }

  hideEvent() {
    $(document).on('click', 'body', (e) => {
      $('.js-treeview-select-menu.is-active').each(function(e, elment) {
        $(elment).removeClass('is-active').closest('.js-treeview-select-wrap').trigger('treeHide');
      });
    });
  }
}