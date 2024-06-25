<template>
  <div class="test-create">
    <div class="test-create-title">
      <div class="test-create-title-left">
        <div class="test-create-title-left-back" @click="back()">
          <span class="test-create-title-left-back-img">
            <img src="/static-dist/app/img/question-bank/back-image.png" alt="" />
          </span>
          <span class="test-create-title-left-back-text">返回</span>
        </div>
        <i></i>
        <span class="test-create-title-left-type">随机卷</span>
      </div>
      <div class="test-create-title-right">
        <span class="test-create-title-right-item">
          <span class="test-create-title-right-text">试卷</span>
          <span class="test-create-title-right-number">0</span>
        </span>
        <i></i>
        <span class="test-create-title-right-item">
          <span class="test-create-title-right-text">总分</span>
          <span class="test-create-title-right-number">0.0</span>
        </span>
      </div>
    </div>

    <div class="test-create-content">
      <a-form id="test-create-form" :form="form">
        <a-form-item label="试卷名称">
          <a-input placeholder="请输入试卷说明"
                   v-decorator="[
          'testname',
          { rules: [{ required: true, message: '请输入试卷名称' }] },
        ]"/>
          <span class="maxNum">0/50</span>
        </a-form-item>
        <a-form-item label="试卷说明">
          <a-input
            placeholder="请输入试卷说明"
            @focus="isShow = true"
            v-show="!isShow"
          />
          <span class="maxNum">0/500</span>
          <div v-show="isShow">
            <a-textarea
              :data-image-download-url="showCKEditorData.publicPath"
              :name="`test-paper-explain`"
            />
          </div>
        </a-form-item>
        <div class="test-num-tips" v-show="showNumTips">
          为了确保每位学生都能获得丰富多样的学习体验，并考虑到系统处理效率及资源分配的最优状态，我们精心设定了试卷生成的灵活性与合理性平衡点。目前，系统支持您创建最多200张独特的随机试卷
        </div>
        <a-form-item label="试卷份数">
          <div class="test-paper-number">
            <a-input-number
              id="inputNumber"
              v-model="testNum"
              :min="1"
              :max="10"
              @change="onChange"
              v-decorator="[
               'testnumber',
              { rules: [{ required: true, message: '请至少设置 1 份试卷' }] },
             ]"
            />
            <span class="test-paper-number-text">≤200</span>
            <span class="test-num-tips-image" @mouseenter="showNumTips = true" @mouseleave="showNumTips = false">
              <img
                src="/static-dist/app/img/question-bank/test-num-tips.png"
                alt=""
              />
            </span>
          </div>
        </a-form-item>
        <a-form-item label="抽题方式">
          <div class="extraction-method-content">
            <a-radio-group name="radioGroup" :default-value="1">
              <a-radio :value="1">按题型抽题</a-radio>
              <a-radio :value="2">按题型+分类抽题</a-radio>
            </a-radio-group>
            <a-dropdown :trigger="['click']" placement="bottomRight">
              <div class="question-type-show">
                <img
                  src="/static-dist/app/img/question-bank/question-type-show-image.png"
                  alt=""
                />
                <span>题型展示设置</span>
              </div>
              <a-menu slot="overlay" class="question-type-setting-menu">
                <draggable v-model="questionTypes">
                  <transition-group>
                    <a-menu-item v-for="questionType in questionTypes" :key="questionType.type" class="question-type-setting-menu-item">
                      <span class="question-type-setting-menu-item-label">
                        <img
                          class="question-type-setting-menu-item-label-icon"
                          src="/static-dist/app/img/question-bank/question-type-drag.png"
                          alt=""
                        />
                        <span class="question-type-setting-menu-item-label-text">{{ questionType.name }}</span>
                      </span>
                      <a-switch v-model:checked="questionType.checked" class="question-type-setting-menu-item-switch"></a-switch>
                    </a-menu-item>
                  </transition-group>
                </draggable>
              </a-menu>
            </a-dropdown>
          </div>
        </a-form-item>
        <div class="question-type">
          <div class="question-typ-top">
            <h3 class="question-typ-top-list">
              <span v-for="(item,index) in questionType" :key="index">{{item.name}}</span>
            </h3>
          </div>
          <div class="question-type-content">
            <p class="question-type-content-list">
              <span>题目数量</span>
              <input v-for="(data, i) in 7" :key="i" />
            </p>
            <p class="question-type-content-list">
              <span>每题分数</span>
              <input v-for="(data, i) in 7" :key="i" />
            </p>
            <p class="question-type-content-list">
              <span class="question-type-content-list-total">
                合计
                <i>(题数/总分)</i>
               </span>
              <input v-for="(data, i) in 7" :key="i" disabled />
            </p>
          </div>
        </div>
        <a-form-item label="难度调节">
          <a-switch checked-children="开启" un-checked-children="关闭" />
        </a-form-item>
      </a-form>
    </div>

    <footer>
      <button class="test-create-save" @click="saveBtn()">保存</button>
      <button class="test-create-cancel">取消</button>
    </footer>
  </div>
</template>

<script>
import loadScript from "load-script";
import Draggable from 'vuedraggable';

export default {
  components: {
    Draggable
  },
  data() {
    return {
      isShow: false,
      showNumTips: false,
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
      testNum: 1,
      questionType: [
        {
          name: "题型设置",
        },
        {
          name: "单选题",
        },
        {
          name: "多选题",
        },
        {
          name: "问答题",
        },
        {
          name: "不定项",
        },
        {
          name: "判断题",
        },
        {
          name: "填空题",
        },
        {
          name: "材料题",
        },
      ],
      questionTypes: [
        {
          type: "single_choice",
          name: "单选题",
          checked: true,
        },
        {
          type: "multiple_choice",
          name: "多选题",
          checked: true,
        },
        {
          type: "essay",
          name: "问答题",
          checked: true,
        },
        {
          type: "uncertain_choice",
          name: "不定项",
          checked: true,
        },
        {
          type: "determine",
          name: "判断题",
          checked: true,
        },
        {
          type: "fill",
          name: "填空题",
          checked: true,
        },
        {
          type: "material",
          name: "材料题",
          checked: true,
        },
      ],
      form: this.$form.createForm(this, { name: "save-test-paper" }),
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
      this.explainEditor = window.CKEDITOR.replace(`test-paper-explain`, {
        toolbar: "Minimal",
        fileSingleSizeLimit: this.showCKEditorData.fileSingleSizeLimit,
        filebrowserImageUploadUrl: this.showCKEditorData.filebrowserImageUploadUrl,
        filebrowserImageDownloadUrl: this.showCKEditorData.filebrowserImageDownloadUrl,
        language: this.showCKEditorData.language
      });
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
      console.log("changed", value);
    },
    saveBtn() {
      this.form.validateFields(err => {
        if (!err) {
          console.info('success');
        }
      });
    },
  },
}
</script>

<style scoped>

</style>
