<template>
  <div :class="{ active: active === index }" class="graphic-navigation-item clearfix" @click="selected(index)">
    <div class="graphic-navigation-item-left clearfix">
      <div class="add-img" @click="chooseImg">
        <img v-if="!item.image.uri"  :src="getDefaultImg(item.link.type)" class="graphic-navigation-img">
        <img v-else :src="item.image.uri" class="graphic-navigation-img">
        <div  class="graphic-navigation-img-mask">更换图片</div>
      </div>
    </div>
    <div class="graphic-navigation-item-right clearfix">
      <div class="add-title">
        标题：<el-input v-model="item.title" size="mini" placeholder="请输入标题" maxlength="5" clearable/>
      </div>
      <div class="add-title">
        链接来源：
        <el-select v-model="item.link.type" placeholder="请选择" size="mini" width="150px">
          <el-option v-for="typeItem in typeOptions" :key="typeItem.value" :label="typeItem.label" :value="typeItem.value"/>
        </el-select>
      </div>
      <div  class="add-choose" v-show="groupList.length">
        {{ getTypeText(item.link.type) }}分类：
        <el-select v-model="item.link.categoryId" placeholder="请选择" size="mini" width="150px">
          <el-option v-for="groupItem in groupList" :key="groupItem.id" :label="groupItem.name" :value="groupItem.id"/>
        </el-select>
      </div>
    </div>
    <img
      class="icon-delete"
      src="static/images/delete.png"
      @click="handleRemove(index)"
      v-show="active === index"
    />

    <el-dialog
      :visible.sync="chooseVisible"
      title="选择图片"
      width="60%">
      <div class="choose-container">
        <div class="choose-container-group"  v-for="(group, groupIndex) in imgChooseList" :key="groupIndex">
          <div class="choose-container-group-item" v-for="(item, index) in group" :key="index">
            <img :src="item" class="graphic-navigation-img" @click="setCurrentImg(item)">
          </div>
        </div>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button @click="chooseVisible = false">取 消</el-button>
        <el-upload
        :http-request="uploadImg"
        :before-upload="beforeUpload"
        :show-file-list="false"
        class="upload-img"
        action="string"
        accept=".jpg,.jpeg,.png,.gif,.bmp,.JPG,.JPEG,.PBG,.GIF,.BMP"
      >
        <el-button type="primary">上传图片</el-button>
      </el-upload>
      </span>
    </el-dialog>
    

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
          :outputType="option.outputType"
        />
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false">取 消</el-button>
        <el-button type="primary" @click="stopCrop">确 定</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import Api from 'admin/api'
import { VueCropper } from 'vue-cropper'
import settingCell from '../module-frame/setting-cell'
import { mapActions, mapState } from 'vuex';

const { protocol, pathname, host } = window.location;
export default {
  components: {
    VueCropper,
    settingCell
  },
  // eslint-disable-next-line vue/require-prop-types
  props: ['item', 'index', 'active'],
  data() {
    return {
      baseUri: `${protocol}//${host}${pathname.split('/').slice(0, -1).join('/')}/`,
      chooseVisible: false,
      chooseType: '',
      imgChooseList: ICON_LIST,
      appTypeBaseList: [{
        value: 'openCourse',
        label: '公开课分类'
      }, {
        value: 'classroom',
        label: '班级分类'
      }, {
        value: 'course',
        label: '课程分类'
      }],
      h5TypeBaseList: [{
        value: 'classroom',
        label: '班级分类'
      }, {
        value: 'course',
        label: '课程分类'
      }],
      typeText: {
        openCourse: '公开课',
        classroom: '班级',
        course: '课程',
        vip: '会员'
      },
      groupList: [],
      activeIndex: this.active,
      option: {
        img: '',
        outputType: 'png', // 裁剪生成图片的格式
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
  computed: {
    ...mapState(['isLoading', 'vipLevels', 'vipSettings',
                'vipSetupStatus', 'vipPlugin','settings']),
    vipDisabled() {
      return !this.vipSetupStatus || (!this.vipSettings
            || !this.vipSettings.enabled
            || !this.vipSettings.h5Enabled);
    },
    typeOptions() {
      if(this.pathName==="h5Setting"){
        return this.h5TypeBaseList;
      }
      if(this.pathName==="appSetting"){
        const vipItem = !this.vipDisabled ? [{
          value: 'vip',
          label: '会员专区'
        }] : []
        return [ ...vipItem, ...this.appTypeBaseList ];
      }
    },
  },
  created() {
    this.getCurrentType();
  },
  methods: {
    ...mapActions([
      'getCategoryType',
    ]),
    getCurrentType() {
      this.groupList.length = 0;
      if (this.item.link.type === 'vip') {
        return;
      }
      this.getCategoryType({
        type: this.item.link.type
      }).then(res => {
        console.log(res);
        res.forEach(item => {
          this.groupList.push(item);
        })
      })
    },
    getTypeText(type) {
      return this.typeText[type];
    },
    setCurrentImg(uri) {
      this.item.image.uri = this.baseUri + uri;
      this.chooseVisible = false;
    },
    handleRemove(data, index) {
      this.$emit("removeItem", data);
    },
    getDefaultImg(type){
      switch(type){
        case "openCourse":
          return "static/images/openCourse.png"
        case "course":
          return "static/images/hotcourse.png"
        case "classroom":
          return "static/images/hotclass.png"
        default:
          return `${this.baseUri}static/images/graphic/default/icon@2x.png`
      }
    },
    beforeUpload(file) {
      this.chooseVisible = false;
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
      this.readFail(file)
    },
    chooseImg() {
      this.chooseVisible = true;
    },
    readFail(file){
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
      this.sendUploadFile(formData)
    },
    sendUploadFile(formData){
       Api.uploadFile({
        data: formData
      })
        .then(data => {
          if (this.pathName === 'miniprogramSetting') {
            // 小程序后台替换图片协议
            data.uri = data.uri.replace(/^(\/\/)|(http:\/\/)/, 'https://')
          }
          this.item.image = data
          this.$emit('selected',
            {
              selectIndex: this.activeIndex,
              imageUrl: data.uri
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
      this.imgAdress = this.item.image.uri
      this.activeIndex = index
      this.$emit('selected',
        {
          selectIndex: index,
          imageUrl: this.item.image.uri
        }
      )
    }
  },
  watch: {
    'item.link.type': {
      handler(data) {
        this.getCurrentType();
      }
    }
  },
}

</script>
