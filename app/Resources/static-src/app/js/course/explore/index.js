import MobileCourse from './MobileCourse';
import Vant from 'vant';

Vue.config.productionTip = false;
Vue.use(Vant);

Vue.filter('trans', function (value, params) {
  if (!value) return '';
  return Translator.trans(value, params);
});

if($('[name="isWeixin"]').val() || $('[name="isMobile"]').val()) {
  new Vue({
    render: createElement => createElement(MobileCourse)
  }).$mount('#app');
}

$('.js-search-type').on('click', event => {
  const $this = $(event.currentTarget);
  window.location.href = $this.val();
});

$('.open-course-list').on('click','.section-more-btn a', event => {
  const url = $(this).attr('data-url');
  $.ajax({
    url: url,
    dataType: 'html',
    success: function(html) {
      const content = $('.open-course-list .course-block,.open-course-list .section-more-btn', $(html)).fadeIn('slow');
      $('.section-more-btn').remove();
      $('.open-course-list').append(content);
      echo.init();
    } 
  });
});

