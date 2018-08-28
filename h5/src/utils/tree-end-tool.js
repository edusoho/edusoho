// 转换子元素的空数组
const treeEndTool = (tree, childKey, handler, originTree, first) => {
  const isFirst = first === undefined ? true : first;
  for (let i = 0; i < tree.length; i += 1) {
    originTree = isFirst ? tree : originTree;
    tree[i][childKey] = treeEndTool(tree[i][childKey], childKey, handler, originTree, false);
  }
  if (!tree.length) {
    tree = undefined;
  }
  return tree;
};

export default treeEndTool;
