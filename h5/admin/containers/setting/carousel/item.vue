<template>
  <div class="carousel-item clearfix" :class="active === index ? 'active' : ''" @click="selected(item, index)">
    <el-upload
      class="add-img"
      action="string"
      :http-request="uploadImg"
      :show-file-list="false"
      >
      <img class="carousel-img" :src="item.image">
      <span v-show="!item.image"><i class="text-18">+</i> 添加图片</span>
    </el-upload>
    <img class="icon-delete" src="static/images/delete.png" v-show="active === index" @click="handleRemove(index)">
    <div class="add-title pull-left">标题：<el-input size="mini" v-model="input" placeholder="请输入标题"></el-input></div>
    <div class="pull-left">链接：<el-button type="info" size="mini" @click="openModal">选择课程</el-button></div>
  </div>
</template>

<script>
  import Api from '@admin/api';

  export default {
    props: ['item', 'index', 'active'],
    data() {
      return {
        activeIndex: 0,
        input: ''
      };
    },
    methods: {
      uploadImg(item) {
        let formData = new FormData()
        formData.append('file', item.file)
        formData.append('group', 'system')

        Api.uploadFile({
          data: formData
        })
        .then((data) => {
          this.item.image = data.uri;
          this.$emit('selected',
          {
            selectIndex: this.activeIndex,
            activeStatus: true,
            imageUrl: data.uri
          }
        );
          console.log(data)
        })
        .catch((err) => {
          console.log(err, 'error');
        });
      },
      selected(item, index) {
        this.imgAdress = item.image;
        const activeStatus = this.isActive;
        this.activeIndex = index;
        this.$emit('selected',
          {
            selectIndex: index,
            activeStatus: true,
            imageUrl: this.item.image
          }
        );
      },
      handleRemove(index) {
        if (index) {
          this.$emit('selected', {imageUrl: ''});
          this.$el.remove();
        } else {
          this.$message({
            message: '至少要留一张轮播图',
            type: 'warning'
          });
        }
      },
      handlePictureCardPreview(file) {
        this.dialogImageUrl = file.url;
        this.dialogVisible = true;
      },
      openModal() {
        this.$emit('chooseCourse');
      }
    }
  }

</script>
