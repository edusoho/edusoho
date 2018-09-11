<template>
  <div class="carousel-item clearfix" :class="{ active: active === index }" @click="selected(index)">
    <el-upload
      class="add-img"
      action="string"
      accept=".jpg,.jpeg,.png,.gif,.bmp,.JPG,.JPEG,.PBG,.GIF,.BMP"
      :http-request="uploadImg"
      :before-upload="beforeUpload"
      :show-file-list="false"
      >
      <img class="carousel-img" :src="item.image.uri" v-show="item.image.uri">
      <span v-show="!item.image.uri"><i class="text-xlarge">+</i> 添加图片</span>
    </el-upload>

    <el-dialog
      title="提示:通过鼠标滚轮缩放图片"
      :visible.sync="dialogVisible"
      width="80%">
      <div class="cropper-container">
        <vueCropper
          ref="cropper"
          v-show="option.img"
          :img="option.img"
          :fixed="option.fixed"
          :autoCrop="option.autoCrop"
          :fixedNumber="option.fixedNumber"
          :autoCropWidth="option.autoCropWidth"
          :autoCropHeight="option.autoCropHeight"
        ></vueCropper>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false">取 消</el-button>
        <el-button type="primary" @click="stopCrop">确 定</el-button>
      </span>
    </el-dialog>

    <img class="icon-delete" src="static/images/delete.png" v-show="active === index" @click="handleRemove($event, index, itemNum)">
    <div class="add-title pull-left">标题：<el-input size="mini" v-model="item.title" placeholder="请输入标题" clearable></el-input>
    </div>
    <div class="pull-left">链接：<el-button type="info" size="mini" @click="openModal" v-show="!linkTextShow">选择课程</el-button>
      <el-tag
        class="courseLink"
        closable
        :disable-transitions="true"
        @close="handleClose"
        v-show="linkTextShow">
        <el-tooltip class="text-content ellipsis" effect="dark" placement="top">
          <span slot="content">{{linkTextShow}}</span>
          <span>{{ linkTextShow }}</span>
        </el-tooltip>
      </el-tag>
    </div>
  </div>
</template>

<script>
  import Api from '@admin/api';
  import VueCropper from 'vue-cropper';

  export default {
    components: {
      VueCropper
    },
    props: ['item', 'index', 'active', 'itemNum', 'courseSets'],
    data() {
      return {
        activeIndex: this.active,
        option: {
          img: '',
          autoCrop: true,
          autoCropWidth: 375,
          autoCropHeight: 200,
          fixedNumber: [375, 200],
          fixed: true,
        },
        imageCropped: false,
        dialogVisible: false,
      };
    },
    computed: {
      linkTextShow() {
        return this.item.link.target && this.item.link.target.displayedTitle;
      },
    },
    watch: {
      courseSets(sets) {
        console.log(sets, 'courseSets')
        if (sets.length) {
          this.item.link.target = {
            id: sets[0].id,
            title: sets[0].title,
            displayedTitle: sets[0].displayedTitle
          }
        } else {
          this.item.link.target = {};
        }
      }
    },
    methods: {
      beforeUpload(file) {
        const type = file.type;
        const size = file.size / 1024 / 1024;

        if (type.indexOf('image') === -1) {
          this.$message({
            message: '文件类型仅支持图片格式',
            type: 'error'
          });
          return;
        }

        if (size > 2) {
          this.$message({
            message: '文件大小不得超过 2 MB',
            type: 'error'
          });
          return;
        }

        this.dialogVisible = true;
        const reader = new FileReader();
        reader.onload = () => {
          this.option.img = reader.result;
        }
        reader.readAsDataURL(file)
      },
      stopCrop() {
        this.$refs.cropper.stopCrop()
        this.dialogVisible = false;
        this.$refs.cropper.getCropData((data) => {
          this.imageCropped = true;
          this.uploadImg(data)
        })
      },
      uploadImg(file) {
        if (!this.imageCropped) return;

        this.imageCropped = false;

        let formData = new FormData()
        formData.append('file', file)
        formData.append('group', 'system')

        Api.uploadFile({
          data: formData
        })
        .then((data) => {
          this.item.image = data;
          this.$emit('selected',
          {
            selectIndex: this.activeIndex,
            imageUrl: data.uri
          });

          this.$message({
            message: '图片上传成功',
            type: 'success'
          });
        })
        .catch((err) => {
          this.$message({
            message: err.message,
            type: 'error'
          });
        });
      },
      selected(index) {
        this.imgAdress = this.item.image.uri;
        this.activeIndex = index;
        this.$emit('selected',
          {
            selectIndex: index,
            imageUrl: this.item.image.uri
          }
        );
      },
      handleRemove(e, index, length) {
        e.stopPropagation();
        if (length > 1) {
          this.$emit('remove', index);
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
      },
      handleClose() {
        this.$emit('removeCourseLink', this.index);
      },
    }
  }

</script>
