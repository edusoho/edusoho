import ShowUnpublish from './../ShowUnpublish';
import BaseManage from './../BaseManage';

class NormalManage extends BaseManage {
  constructor($container) {
    super($container);
  }
}

new NormalManage('#sortable-list');

new ShowUnpublish('input[name="isShowPublish"]');