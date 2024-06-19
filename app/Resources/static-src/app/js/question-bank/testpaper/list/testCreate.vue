<template>
  <div class="test-create">
    <div class="test-create-title">
      <div class="test-create-title-left">
        <span class="test-create-title-left-back" @click="back()">
          <img src="/static-dist/app/img/question-bank/back-image.png" alt="" />
          返回
        </span>
        <i></i>
        <span class="test-create-title-left-type">随机卷</span>
      </div>
      <div class="test-create-title-right">
        <p>
          <span>试卷</span>
          <span class="test-create-title-right-mark">0</span>
        </p>
        <i></i>
        <p>
          <span>总分</span>
          <span class="test-create-title-right-mark">0</span>
        </p>
      </div>
    </div>

    <div class="test-create-content">
      <a-form id="test-create-form">
        <a-form-item label="试卷名称">
          <a-input placeholder="请输入试卷说明"/>
        </a-form-item>
        <a-form-item label="试卷说明">
          <a-input placeholder="请输入试卷说明" @focus="isShow = true" v-show="!isShow"/>
          <div v-show="isShow">
             <a-textarea
            :data-image-download-url="showCKEditorData.publicPath"
            :name="`test-paper-explain`"
            />
          </div>
         
        </a-form-item>
        <div class="test-num-tips">
            为了确保每位学生都能获得丰富多样的学习体验，并考虑到系统处理效率及资源分配的最优状态，我们精心设定了试卷生成的灵活性与合理性平衡点。目前，系统支持您创建最多200张独特的随机试卷
        </div>
        <a-form-item label="试卷份数">
           <div class="test-paper-number">
             <a-input-number id="inputNumber" v-model="testNum" :min="1" :max="10" @change="onChange" />
             <span class="test-paper-number-text">≤200</span>
             <span class="test-num-tips-image">
              <img src="/static-dist/app/img/question-bank/test-num-tips.png" alt="" >
             </span>          
           </div> 
        </a-form-item>
        <a-form-item label="抽题方式">
          <a-radio-group name="radioGroup" :default-value="1">
           <a-radio :value="1">按题型抽题</a-radio>
           <a-radio :value="2">按题型+分类抽题</a-radio>
          </a-radio-group>
        </a-form-item>
      </a-form>
    </div>
  </div>
</template>
<script>
import loadScript from "load-script";
export default {
  data() {
    return {
      isShow: false,
      explainEditor: "",
      showCKEditorData: {
        filebrowserImageDownloadUrl:
          "/editor/download?token=Mnxjb3Vyc2V8aW1hZ2V8MTY3NzY2OTYyN3w5ZjM2NmRjNjg1ZjUyMzJkZGI0ZjM3MDQ1NzMzODhiNA",
        filebrowserImageUploadUrl:
          "/editor/upload?token=Mnxjb3Vyc2V8aW1hZ2V8MTY3NzY2OTYyN3w5ZjM2NmRjNjg1ZjUyMzJkZGI0ZjM3MDQ1NzMzODhiNA",
        jqueryPath:
          "/static-dist/libs/jquery/dist/jquery.min.js?version=23.1.6",
        language: "zh-cn",
        publicPath: "/static-dist/libs/es-ckeditor/ckeditor.js?version=23.1.6"
      },
      testNum: 1
    };
  },
    mounted() {
    this.$nextTick(() => {
      loadScript(this.showCKEditorData.jqueryPath, err => {
        if (err) {
          console.log(err);
        }
        loadScript(this.showCKEditorData.publicPath, err => {
          if (err) {
            console.log(err);
          }
          this.TestPaperExplain();
        });
      });
    });
  },
  methods: {
    TestPaperExplain() {
      this.explainEditor = window.CKEDITOR.replace(
        `test-paper-explain`,
        {
          toolbar: "Minimal",
          fileSingleSizeLimit: this.showCKEditorData.fileSingleSizeLimit,
          filebrowserImageUploadUrl: this.showCKEditorData
            .filebrowserImageUploadUrl,
          filebrowserImageDownloadUrl: this.showCKEditorData
            .filebrowserImageDownloadUrl,
          language: this.showCKEditorData.language
        }
      );
     this.explainEditor.on("blur", () => {
        this.isShow = false
      })
    },
    back() {
      this.$router.push({
        name: "list",
      });
    },
     onChange(value) {
      console.log('changed', value);
    },
  },
};
</script>
