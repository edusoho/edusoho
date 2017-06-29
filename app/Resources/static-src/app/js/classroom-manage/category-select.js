const initCategorySelect = () => {
  $('[data-role="tree-select"], [name="categoryId"]').select2({
    treeview: true,
    dropdownAutoWidth: true,
    treeviewInitState: 'collapsed',
    placeholderOption: 'first'
    // treeviewInitState: 'expanded'
  });
}

export default initCategorySelect();