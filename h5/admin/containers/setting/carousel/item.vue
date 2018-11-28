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
      <div class="carousel-img-mask" v-show="item.image.uri">更换图片</div>
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
          :enlarge="option.enlarge"
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
    <div class="add-title pull-left">标题：<el-input size="mini" v-model="item.title" placeholder="请输入标题" maxLength="15" clearable></el-input>
    </div>
    <div>链接：
      <el-dropdown v-show="!linkTextShow">
        <el-button size="mini" class="el-dropdown-link">
          添加链接
        </el-button>
        <el-dropdown-menu slot="dropdown">
          <el-dropdown-item @click.native="insideLinkHandle(item.type)" v-for="item in linkOptions" :key="item.key">{{item.label}}</el-dropdown-item>
        </el-dropdown-menu>
      </el-dropdown>
      <el-tag
        class="courseLink"
        closable
        :disable-transitions="true"
        @close="handleClose"
        v-show="linkTextShow">
        <el-tooltip class="text-content ellipsis" effect="dark" placement="top">
          <span slot="content">{{ linkTextShow }}</span>
          <span>{{ linkTextShow }}</span>
        </el-tooltip>
      </el-tag>
    </div>
  </div>
</template>

<script>
  import Api from '@admin/api';
  import { VueCropper } from 'vue-cropper';

  export default {
    components: {
      VueCropper
    },
    props: ['item', 'index', 'active', 'itemNum', 'courseSets', 'type'],
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
          high: false,
          enlarge: 2,
        },
        linkOptions: [{
          key: 0,
          type: 'course_list',
          label: '选择课程',
        }, {
          key: 1,
          type: 'classroom_list',
          label: '选择班级',
        }],
        imageCropped: false,
        dialogVisible: false,
        pathName: this.$route.name,
      };
    },
    computed: {
      linkTextShow() {
        return this.item.link.target && this.item.link.target.displayedTitle;
      },
    },
    watch: {
      courseSets(sets) {
        console.log(sets[0], 'courseSets')
        if (sets.length) {
          this.item.link.target = {
            id: sets[0].id,
            title: sets[0].title,
            courseSetId: sets[0].courseSetId,
            displayedTitle: sets[0].displayedTitle
          }
        } else {
          this.item.link.target = null;
        }
      },
      type() {
        this.item.link.type = (this.type === 'course_list') ? 'course' : 'classroom'; // 修复默认数据中type为 url 的bug
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
        .then(data => {
          if (this.pathName !== 'h5Setting') {
            // 小程序后台替换图片协议
            data.uri = data.uri.replace(/^(\/\/)|(http:\/\/)/, 'https://');
          }
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
      handleClose() {
        this.$emit('removeCourseLink', this.index);
      },
      insideLinkHandle(value) {
        this.$emit('chooseCourse', value);
      }
    }
  }

</script>
