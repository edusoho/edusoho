export default class Training {
  constructor($iframeContent) {
    this.$trainingModal = $('#modal', window.parent.document);
    this.$PickedModal = $('#attachment-modal', window.parent.document);
    this.$element = $iframeContent;
    this.$step2_form = this.$element.find('#step2-form');
    this.$step3_form = this.$element.find('#step3-form');
    this.currentImages = {"id":0,name:""}
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    this.$element.on('click', '[data-role="pick-item-images"]', event => this.showPickImages(event));
    this.$element.on('click', '[data-role="pick-item-dataset"]', event => this.showPickDataset(event));

    // 监听tag内容变化
    $(".dataset-cache").on("click", function(){
      // 设置tag显示、修改提交内容
      console.log("数据集回调")
      let tagsJson = JSON.parse($(this).val());

      let html = "";
      for(let i=0;i<tagsJson.length;i++){
        html += "<div class='tag-info checktag-"+tagsJson[i].id+"'><span class='label label-primary'>"+tagsJson[i].name+"+<a id='bb' href='javascript:void(0)' class='panel-tool-close bb-icon-close'  data-id='"+tagsJson[i].id+"'>X</a></span></div>";
      }
      $(".tag-lists").html(html);
    });

    // 镜像保存回调
    $(".images-cache").on("click",function(){
      console.log("镜像回调")
        let $cachedImages = $('.images-cache').val();
        if ($cachedImages === '') {
          return;
        }
        
        this.currentImages = JSON.parse($cachedImages);
        $(".selectImages").html(this.currentImages.name);
    })

    // 标签点击删除
    $(".tag-lists").on("click",".bb-icon-close",function(){
      // 重置下cachediv中数据
      let tagsJson = JSON.parse($(".dataset-cache").val());
      let cache = [];
      for(let i=0;i<tagsJson.length;i++){
        if (tagsJson[i].id != $(this).data("id") ){
          cache.push(tagsJson[i]);
        }
      }
      $(".dataset-cache").attr("value",JSON.stringify(cache));
      // 触发显示监测tag
      $(".dataset-cache").trigger("click");
  });
    

    this.$PickedModal.on('shown.bs.modal', () => {
      this.$trainingModal.hide();
    });


    this.$PickedModal.on('hidden.bs.modal', () => {
      // this.showPickedImages();
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

    let imagesText = $(".images-cache").val()
    let imagesJson = 0;
    if(imagesText != ""){
      imagesJson = JSON.parse(imagesText);
    }
    $.get($btn.data('url'), {
      currentId:imagesJson.id
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
    let idsText = $(".dataset-cache").val()
    let idsJson = {};
    if(idsText != ""){
      idsJson = JSON.parse(idsText);
    }
    $.post($btn.data('url'), {
      currentId:idsJson,
    }, html => {
      this.$PickedModal.html(html);
    });
  }

  // showPickedImages() {
  //   let $cachedImages = $('.images-cache').val();
  //   if ($cachedImages === '') {
  //     return;
  //   }
    
  //   this.currentImages = JSON.parse($cachedImages);
  //   $(".selectImages").html(this.currentImages.name);
    
  // }
}
