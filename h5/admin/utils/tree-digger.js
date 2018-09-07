// 转换子元素的空数组
const treeDigger = (tree, handler, originTree=undefined, first=undefined) => {
  const isFirst = first === undefined ? true : first;
  if (!tree) return tree;

  for (let i = 0; i < tree.length; i += 1) {
    originTree = isFirst ? tree : originTree;
    tree[i].children = treeDigger(tree[i].children, handler, originTree, false);
    tree = handler(tree, tree[i].id);
  }

  tree = handler(tree);
  return tree;
};

export default treeDigger;
