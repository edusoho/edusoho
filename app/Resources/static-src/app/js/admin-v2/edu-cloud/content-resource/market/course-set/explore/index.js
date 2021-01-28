import notify from 'common/notify';

// 分页
$('div[data-role="market"]').on('click', 'li', function () {
  let url = $(this).data('url');
  let title = 'title=' + $('#title').val();
  if (url.split('?').length === 1) {
    url = url + title + '&' + $('#urlParameter').val();
  } else {
    url = url + '&' + title + '&' + $('#urlParameter').val();
  }
  getProductList(url);
});

// 分类
$('.nav-link').on('click', function () {
  let label = $(this).parent().parent().data('label');
  $('ul[data-label="' + label + '"] li').removeClass('active');
  $(this).parent().addClass('active');
  let url = $(this).attr('data-url');
  $('#urlParameter').attr('value', url.split('?')[1]);
  getProductList(url + '&' + 'title=' + $('#title').val());
});


$('div[data-role="market"]').on('click', '.selected_btn button', function (e) {
  var chosenCourse = $(this);
  chosenCourse.attr('disabled', 'true');
  chosenCourse.text('处理中...');

  $.post($(this).data('chooseUrl'), {courseSetData: $(this).data('courseSet')}, function (response) {
    if (response.status === 'repeat') {
      notify('danger', '已选择过该课程');
    } else if (response.status) {
      notify('success', '已选择，请到“课程管理”查看并进行营销配置');
    } else {
      notify('danger', Translator.trans('意外错误，操作失败，请联系管理员处理！'));
      return;
    }

    $('.ax_default').attr('style', '');
    chosenCourse.attr('disabled', 'true');
    chosenCourse.attr('style', 'width: 100%; background-color: #CCCCCC; border-color: #CCCCCC');
    chosenCourse.text('已选择');
  }).error(function () {
    chosenCourse.text('选择');
    chosenCourse.attr('disabled', false);
    notify('danger', Translator.trans('网络波动，请重试！'));
  });
});

//   window.num = 0;
//
//   function clock() {
//     if (window.num > 0) {
//       window.num--;
//     } else {
//       $('.ax_default').attr('style', 'visibility: hidden; display: none; ');
//       clearInterval(window.timer);
//       window.num = 3;
//     }
//   }

function getProductList(url, conditions = {}) {
  let content = $('div[data-role="market"]');
  const loading = '<div class="empty" colspan="10" style="color:#999;padding:80px;">' + Translator.trans('admin.cloud_file.detail_loading_hints') + '</div>';
  content.html(loading);
  $.get(url, conditions, function (data) {
    content.html(data);
  }).fail(function () {
    let loading = '<div class="empty" colspan="10" style="color:#999;padding:80px;">' + Translator.trans('site.loading_error') + '</div>';
    content.html(loading);
  });
}

/*一级分类全部*/
$('.js-all-categories').on('click', function () {
  resetTopCategoryActiveClass();
  resetSubCategoryActiveClass();
  resetThirdCategoryActiveClass();
  hideSubCategory();
  hideThirdCategory();
});

/*一级分类点击*/
$('.js-categories').on('click', function () {
  let selectedCategory = $(this).data('categoryId');
  hideSubCategory();
  hideThirdCategory();
  let subCategories = $('#categoryDataList').data('subCategories');
  if (Object.keys(subCategories).length > 0) {
    $('.js-all-sub-categories-search').attr('data-url', $('#categoryDataList').data('searchUrl') + '&categoryId=' + selectedCategory);
    resetSubCategoryActiveClass();
    hideCategory('js-sub-categories');
    showCategory('js-sub-category-parent-' + selectedCategory);
    if (subCategories[selectedCategory] !== undefined) {
      showSubCategory();
    }
  }
});

/*二级分类全部*/
$('.js-all-sub-categories').on('click', function () {
  hideThirdCategory();
});

/*二级分类点击*/
$('.js-sub-categories').on('click', function () {
  let selectedCategory = $(this).data('categoryId');
  hideThirdCategory();
  let thirdCategories = $('#categoryDataList').data('thirdLevelCategories');
  if (Object.keys(thirdCategories).length > 0) {
    $('.js-all-third-categories-search').attr('data-url', $('#categoryDataList').data('searchUrl') + '&categoryId=' + selectedCategory);
    resetThirdCategoryActiveClass();
    hideCategory('js-third-categories');
    showCategory('js-third-category-parent-' + selectedCategory);
    if (thirdCategories[selectedCategory] !== undefined) {
      showThirdCategory();
    }
  }
});

function showCategory(className) {
  $('.' + className).show();
}

function hideCategory(className) {
  $('.' + className).hide();
}

function addActiveClass(className) {
  $('.' + className).removeClass('active');
}

function removeActiveClass(className) {
  $('.' + className).removeClass('active');
}

function resetTopCategoryActiveClass() {
  $('.js-all-categories').addClass('active');
  $('.js-categories').removeClass('active');
}

function resetSubCategoryActiveClass() {
  $('.js-all-sub-categories').addClass('active');
  $('.js-sub-categories').removeClass('active');
}

function resetThirdCategoryActiveClass() {
  $('.js-all-third-categories').addClass('active');
  $('.js-third-categories').removeClass('active');
}

function showSubCategory() {
  $('.js-sub-group').show();
}

function showThirdCategory() {
  $('.js-third-level-group').show();
}

function hideSubCategory() {
  $('.js-sub-group').hide();
}

function hideThirdCategory() {
  $('.js-third-level-group').hide();
}

$('.js-search-product').on('click', () => {
  let conditions = {
    categoryId: $('.js-categories.active').data('categoryId'),
    title: $('input[name="title"]').val(),
  };

  getProductList($('.js-search-product').data('url'), conditions);
});