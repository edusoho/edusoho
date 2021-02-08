export default class Training {
  constructor($iframeContent) {
    this.$trainingModal = $('#modal', window.parent.document);
    this.$PickedModal = $('#attachment-modal', window.parent.document);
    this.$element = $iframeContent;
    this.$step2_form = this.$element.find('#step2-form');
    this.$step3_form = this.$element.find('#step3-form');
    this.currentImagesName = {"id":0,name:""}
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    this.$element.on('click', '[data-role="pick-item-images"]', event => this.showPickImages(event));
    this.$element.on('click', '[data-role="pick-item-dataset"]', event => this.showPickDataset(event));

    // 监听tag内容变化
    $(".dataset-cache").on("DOMNodeInserted", function(){
      // 设置tag显示、修改提交内容
      let tagsJson = JSON.parse($(this).text());

      let html = "";
      for(let i=0;i<tagsJson.length;i++){
        html += "<div class='tag-info checktag-"+tagsJson[i].id+"'><span class='label label-primary'>"+tagsJson[i].name+"+<a id='bb' href='javascript:void(0)' class='panel-tool-close bb-icon-close'  data-id='"+tagsJson[i].id+"'>X</a></span></div>";
      }
      $(".tag-lists").html(html);
    });

    // 标签点击删除
    $(".tag-lists").on("click",".bb-icon-close",function(){
      // 重置下cachediv中数据
      let tagsJson = JSON.parse($(".dataset-cache").text());
      let cache = [];
      for(let i=0;i<tagsJson.length;i++){
        if (tagsJson[i].id != $(this).data("id") ){
          cache.push(tagsJson[i]);
        }
      }
      $(".dataset-cache").text(JSON.stringify(cache));
  });
    

    this.$PickedModal.on('shown.bs.modal', () => {
      this.$trainingModal.hide();
    });


    this.$PickedModal.on('hidden.bs.modal', () => {
      this.showPickedImages();
      this.$trainingModal.show();
      this.$PickedModal.html('');
    });
  }

  // 镜像弹窗
  showPickImages(event) {
    event.preventDefault();
    let $btn = $(event.currentTarget);
    this.$PickedModal.modal().data('manager', this);
    // 需要传递一个选中参数过去，来判断之前是否选中
    $.get($btn.data('url'), {
      current:this.currentImagesName.id
    }, html => {
      this.$PickedModal.html(html);
    });
  }

  // 数据集弹窗
  showPickDataset(event){
    event.preventDefault();
    let $btn = $(event.currentTarget);
    this.$PickedModal.modal().data('manager', this);
    // 需要传递一个选中参数过去，来判断之前是否选中
    let idsText = $(".dataset-cache").text()
    let idsJson = {};
    if(idsText != ""){
      idsJson = JSON.parse(idsText);
    }
    $.post($btn.data('url'), {
      current:idsJson,
    }, html => {
      this.$PickedModal.html(html);
    });
    
  }

  showPickedImages() {
    let $cachedImages = $('.js-cached-question');
    if ($cachedImages.text() === '') {
      return;
    }
    
    this.currentImagesName = JSON.parse($cachedImages.text());
    $(".selectImages").html(this.currentImagesName.name);
    
  }
}
