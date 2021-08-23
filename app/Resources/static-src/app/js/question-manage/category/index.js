
$('.js-category-set').click((element) => {
  let categoryId = $('#select_category').val();
  $('#category_Id').val(categoryId);
});

$('.js-item-create').click((element) => {
  let categoryId = $('#select_category').val();
  let importUrl = $(element.currentTarget).data('url')
  location.href = importUrl + '&categoryId=' + categoryId;
});