<template>
  <div :class="{ active: active === index }" class="carousel-item clearfix" @click="selected(index)">
    <el-upload
      :http-request="uploadImg"
      :before-upload="beforeUpload"
      :show-file-list="false"
      class="add-img"
      action="string"
      accept=".jpg,.jpeg,.png,.gif,.bmp,.JPG,.JPEG,.PBG,.GIF,.BMP"
    >
      <img v-if="!item.image.url"  :src="getDefaultImg(item.link.type)" class="carousel-img">
      <img v-else :src="item.image.url" class="carousel-img">
      <div  class="carousel-img-mask">更换图片</div>
    </el-upload>

    <el-dialog
      :visible.sync="dialogVisible"
      title="提示:通过鼠标滚轮缩放图片"
      width="80%">
      <div class="cropper-container">
        <vueCropper
          v-show="option.img"
          ref="cropper"
          :img="option.img"
          :fixed="option.fixed"
          :enlarge="option.enlarge"
          :auto-crop="option.autoCrop"
          :fixed-number="option.fixedNumber"
          :auto-crop-width="option.autoCropWidth"
          :auto-crop-height="option.autoCropHeight"
        />
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false">取 消</el-button>
        <el-button type="primary" @click="stopCrop">确 定</el-button>
      </span>
    </el-dialog>

    <div class="add-title">标题：<el-input v-model="item.title" size="mini" placeholder="请输入标题" max-length="15" clearable/>
    </div>
    <div  class="add-choose">
      链接：<span class="graphic-navigation-link">{{item.link.url}}</span>
    </div>
  </div>
</template>

<script>
import Api from 'admin/api'
import { VueCropper } from 'vue-cropper'
import settingCell from '../module-frame/setting-cell'
export default {
  components: {
    VueCropper,
    settingCell
  },
  // eslint-disable-next-line vue/require-prop-types
  props: ['item', 'index', 'active'],
  data() {
    return {
      activeIndex: this.active,
      option: {
        img: '',
        autoCrop: true,
        autoCropWidth: 80,
        autoCropHeight: 80,
        fixedNumber: [80, 80],
        fixed: true,
        high: false,
        enlarge: 2
      },
      imageCropped: false,
      dialogVisible: false,
      pathName: this.$route.name,
    }
  },

  created() {

  },
  methods: {
    getDefaultImg(type){
      switch(type){
        case "openCourse":
          return "static/images/openCourse.png"
        case "course":
          return "static/images/hotcourse.png"
        case "class":
          return "static/images/hotclass.png"
      }
    },
    beforeUpload(file) {
      const type = file.type
      const size = file.size / 1024 / 1024

      if (type.indexOf('image') === -1) {
        this.$message({
          message: '文件类型仅支持图片格式',
          type: 'error'
        })
        return
      }

      if (size > 2) {
        this.$message({
          message: '文件大小不得超过 2 MB',
          type: 'error'
        })
        return
      }

      this.dialogVisible = true
      const reader = new FileReader()
      reader.onload = () => {
        this.option.img = reader.result
      }
      reader.readAsDataURL(file)
    },
    stopCrop() {
      this.$refs.cropper.stopCrop()
      this.dialogVisible = false
      this.$refs.cropper.getCropData((data) => {
        this.imageCropped = true
        this.uploadImg(data)
      })
    },
    uploadImg(file) {
      if (!this.imageCropped) return

      this.imageCropped = false

      const formData = new FormData()
      formData.append('file', file)
      formData.append('group', 'system')

      Api.uploadFile({
        data: formData
      })
        .then(data => {
          console.log(data)
          if (this.pathName === 'miniprogramSetting') {
            // 小程序后台替换图片协议
            data.uri = data.uri.replace(/^(\/\/)|(http:\/\/)/, 'https://')
          }
          data.url=data.uri;
          this.item.image = data
          this.$emit('selected',
            {
              selectIndex: this.activeIndex,
              imageUrl: data.url
            })

          this.$message({
            message: '图片上传成功',
            type: 'success'
          })
        })
        .catch((err) => {
          this.$message({
            message: err.message,
            type: 'error'
          })
        })
    },
    selected(index) {
      this.imgAdress = this.item.image.url
      this.activeIndex = index
      this.$emit('selected',
        {
          selectIndex: index,
          imageUrl: this.item.image.url
        }
      )
    }
  }
}

</script>
