// extend treeview

export default class Treeview {

  constructor(props) {

  }

  setTreeNodeState({ state = 'checkNode', nodeId }) {
    this.$elem.treeview(state, [nodeId, { silent: true }]);
  }

  checkTreeNode(nodeId) {
    this.setTreeNodeState({ state: 'checkNode', nodeId });
  }

  uncheckTreeNode(nodeId) {
    this.setTreeNodeState({ state: 'uncheckNode', nodeId });
  }

  checksubTreeNode(e, node) {
    let childNodes = node.children;
    let nodeIds = [];

    if (childNodes && childNodes.length) {
      nodeIds = this.iteratorCheckChildNodes(childNodes);
    }

    if (nodeIds.length) {
      this.checkTreeNode(nodeIds);
    }
  }

  unchecksubTreeNode(e, node, parentCheckState) {
    let childNodes = node.children;
    let nodeIds = [];

    if (childNodes) {
      nodeIds = this.iteratorCheckChildNodes(childNodes);
    }

    let ancestorsNodeIds = this.uncheckParentTreeNode(node, parentCheckState);
    ancestorsNodeIds = typeof ancestorsNodeIds == 'undefined' ? [] : ancestorsNodeIds;

    const allNodeIds = [...nodeIds, ...ancestorsNodeIds];

    this.uncheckTreeNode(allNodeIds);
  }

  uncheckParentTreeNode(node, parentCheckState) {
    if (!parentCheckState) {
      return;
    }

    return this.getAncestorsNodeId(node);
  }

  getParentNode(node) {
    if (node.parentId !== undefined) {
      return this.$elem.treeview('getNode', node.parentId);
    }

    return false;
  }

  getAncestorsNodeId(node) {
    let nodeList = [];
    let tmp = node;
    while (tmp.parentId !== undefined) {
      tmp = this.$elem.treeview('getNode', tmp.parentId);
      nodeList.push(tmp.nodeId);
    }
    return nodeList;
  }

  getAncestorsNode(node) {
    let nodeList = [];
    let tmp = node;
    while (tmp.parentId !== undefined) {
      tmp = this.$elem.treeview('getNode', tmp.parentId);
      nodeList.push(tmp);
    }
    return nodeList;
  }

  iteratorCheckChildNodes(nodes, nodeIds = []) {
    for (let node of nodes) {
      if (node) {
        nodeIds.push(node.nodeId);

        if (node.children && node.children.length) {
          nodeIds.concat(this.iteratorCheckChildNodes(node.children, nodeIds));
        }
      }
    }

    return nodeIds;
  }
}
