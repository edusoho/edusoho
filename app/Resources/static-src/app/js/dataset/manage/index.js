import Axios from 'axios';
import ElementUI from 'element-ui';
import Info from './index.vue';
import qs from 'qs';
import notify from 'common/notify';
import 'vue-tree-halower/dist/halower-tree.min.css'
import VTree from 'vue-tree-halower'

const axios = Axios.create({
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/vnd.edusoho.v2+json',
    'Content-Type': 'application/x-www-form-urlencoded',
    'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
  },
});

Vue.prototype.$axios = axios;
Vue.prototype.$qs = qs;
Vue.use(ElementUI);
Vue.use(VTree);
let $app = $('#app');
new Vue({
  el: '#app',
  render: createElement => createElement(Info, {
    props: {
      info: $app.data("info"),
      treeData: $app.data("treedata"),
      dirGetPath: $app.data("dirgetpath"),
      submitPath: $app.data("submitpath")
    }
  })
});


$(function () {
  var $table = $('#dataset-table');
  $table.on('click', '.delete-course', function () {
    var $this = $(this);
    if (!confirm("是否删除此数据集"))
      return;
    var $tr = $this.parents('tr');
    $.post($this.data('url'), function (data) {
      if (data.code > 0) {
        notify('danger', data.message);
      } else if (data.code == 0) {
        $tr.remove();
        notify('success', (data.message));
      } else {
        $('#modal').modal('show').html(data);
      }
    });
  });
})