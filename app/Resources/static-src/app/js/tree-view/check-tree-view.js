import Treeview from './tree-view';

// When you check a node you will check all nodes of subtree. When you
// uncheck node, you will uncheck all nodes of subtree and the ancestors of
// this node.

export default class CheckTreeview extends Treeview {
  static getDefaultOpts() {
    return {
      showIcon: false,
      showCheckbox: true,
      highlightSelected: false,
    };
  }

  constructor($elem, opts) {
    super();

    let defaultopts = CheckTreeview.getDefaultOpts();
    this.$elem = $elem;
    opts = Object.assign({}, defaultopts, opts);

    this.init(opts);
    this.silent = true;
    this.unCheckParent = opts.unCheckParent;
  }

  init(opts) {
    this.initEvent(opts);
    this.$elem.treeview(opts);
  }

  initEvent(opts) {
    if (opts.disableNodeCheck) {
      return;
    }

    opts.onNodeChecked = (e, node) => { this.OnNodeChecked(e, node); };
    opts.onNodeUnchecked = (e, node) => { this.OnNodeUnChecked(e, node); };
  }

  OnNodeChecked(e, node) {
    this.checksubTreeNode(e, node);
  }

  OnNodeUnChecked(e, node) {
    this.UnCheckedParentLinkTreeNode(node);

    this.unchecksubTreeNode(e, node);
  }

  UnCheckedParentLinkTreeNode(node){
    if (node.parentId !== undefined) {
      let parentNode = this.getParentNode(node);
      if(parentNode.selectable){
        this.$elem.treeview('uncheckNode', [parentNode, { silent: true }]);
        this.UnCheckedParentLinkTreeNode(parentNode);
      }

    }else{
      return;
    }

  }

  getCheckNodes() {
    let checkNodes = this.$elem.treeview('getChecked');
    return checkNodes;
  }

  getTreeObject() {
    return this.$elem.data('treeview');
  }

  checkParentNode(e, node) {
    if (node.parentId == undefined || node.parentId == '0') {
      return false;
    }

    let parentNode = this.getParentNode(node);
    let childNodes = parentNode.children;

    for (let i = 0; i < childNodes.length; i++) {
      if (!childNodes[i].state.checked) {
        return false;
      }
    }

    this.$elem.treeview('checkNode', [parentNode, { silent: true }]);
    this.checkParentNode(e, parentNode);
  }
}