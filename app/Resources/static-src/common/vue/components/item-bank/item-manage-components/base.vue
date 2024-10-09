<template>
  <div id="ib-treeSelect" class="item-bank-sdk ibs-base">
    <a-form-item
      label=""
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 16 }"
      required
    >
      <a-input
        v-decorator="[
          `base['bank_id']`,
          {
            initialValue: bank_id
          }
        ]"
        type="hidden"
      />
    </a-form-item>
    <a-form-item
      v-show="showCategory"
      :label="t('itemManage.ItemCategory')"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 16 }"
    >
      <a-tree-select
        showSearch
        style="width: 200px"
        :dropdownStyle="{ maxHeight: '400px', overflow: 'auto' }"
        :treeData="treeData"
        :getPopupContainer="
          triggerNode => {
            return triggerNode.parentNode || document.body;
          }
        "
        treeNodeFilterProp="title"
        placeholder="Please select"
        @change="setCategoryId"
        v-decorator="[
          `base['category_id']`,
          { initialValue: subject.category_id }
        ]"
      ></a-tree-select>
    </a-form-item>
    <a-form-item
      label=""
      :label-col="{ span: 0 }"
      :wrapper-col="{ span: 20 }"
      style="marginBottom:0"
    >
      <a-input
        type="hidden"
        v-decorator="[`base['category_name']`, { initialValue: '' }]"
      />
    </a-form-item>
    <a-form-item
      v-show="showDifficulty"
      :label="t('Difficulty')"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 16 }"
    >
      <a-radio-group
        name="radioGroup"
        @change="setDifficulty"
        v-decorator="[
          `base['difficulty']`,
          { initialValue: subject.difficulty }
        ]"
      >
        <a-radio
          v-for="(item, index) in difficultySelect"
          :value="item.value"
          :key="index"
          >{{ item.lable }}</a-radio
        >
      </a-radio-group>
    </a-form-item>
    <a-form-item
      v-if="showMaterial"
      :label="t('itemManage.MaterialLabel')"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 16 }"
      required
      :validate-status="material.validateStatus"
      :help="material.errorMsg"
    >
      <a-textarea
        :rows="0"
        :data-image-download-url="showCKEditorData.filebrowserImageDownloadUrl"
        v-decorator="[
          `base['material']`,
          { initialValue: subject.material, rules: [{ required: true }] }
        ]"
      />
    </a-form-item>

    <a-form-item
      v-if="showMaterial && showAttachment"
      :label="t('itemManage.MaterialAttachment')"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 16 }"
    >
      <attachment-upload
        bodyDom="item-bank-create"
        module="material"
        :mode="mode"
        :fileShowData="getAttachmentTypeData('material')"
        :cdnHost="cdnHost"
        :uploaderData="uploadSDKInitData"
        @onFileSort="onFileSort"
        @deleteFile="deleteFile"
        @getFileInfo="getFileInfo"
      ></attachment-upload
    ></a-form-item>

    <slot name="questions"></slot>

    <a-collapse v-show="showAnalysis" :bordered="false">
      <template v-slot:expandIcon="props">
        <div :rotate="props.isActive ? 90 : 0"></div>
      </template>
      <a-collapse-panel
        :header="t('itemManage.ToggleShowChoose')"
        key="1"
        :style="customStyle"
        :forceRender="true"
      >
        <a-form-item
          :label="t('Explain')"
          :label-col="{ span: 4 }"
          :wrapper-col="{ span: 16 }"
        >
          <a-textarea
            :rows="4"
            :data-image-download-url="
              showCKEditorData.filebrowserImageDownloadUrl
            "
            v-decorator="[
              `base['analysis']`,
              { initialValue: subject.analysis }
            ]"
          />
        </a-form-item>

        <a-form-item
          v-if="showAttachment"
          :label="t('ExplainAttachment')"
          :label-col="{ span: 4 }"
          :wrapper-col="{ span: 16 }"
        >
          <attachment-upload
            bodyDom="item-bank-create"
            :mode="mode"
            module="analysis"
            :cdnHost="cdnHost"
            :uploaderData="uploadSDKInitData"
            :fileShowData="getAttachmentTypeData('analysis')"
            @onFileSort="onFileSort"
            @deleteFile="deleteFile"
            @getFileInfo="getFileInfo"
          ></attachment-upload>
        </a-form-item>
      </a-collapse-panel>
    </a-collapse>

    <slot name="subQuestions"></slot>

    <!-- 底部按钮操作 -->
    <a-form-item
      v-if="isSubItem || showModelBtn"
      :wrapper-col="{ span: 8, offset: 16 }"
      class="ibs-base-footer"
    >
      <a-popconfirm
        v-if="self.isWrong"
        placement="topRight"
        :title="t('questionPopconfimTitle')"
        :ok-text="t('ContinueEditing')"
        okType="primary"
        :cancel-text="t('DirectCreation')"
        @confirm="confirm"
        @cancel="e => cancelPopconfim(e)"
      >
        <a-icon slot="icon" type="exclamation-circle" />
        <a-button type="primary" :disabled="isDisable" class="ibs-base--save">
          {{ t("Save") }}
        </a-button>
      </a-popconfirm>

      <a-button
        v-else
        type="primary"
        :disabled="isDisable"
        class="ibs-base--save"
        @click="handleData"
      >
        {{ t("Save") }}
      </a-button>
      <a-button type="link" @click="cancel">{{ t("cancel") }}</a-button>
    </a-form-item>
    <a-form-item
      v-else
      :wrapper-col="{ span: 12, offset: 4 }"
      class="ibs-base-footer"
    >
      <a-button
        type="primary"
        v-if="mode == 'create'"
        :disabled="isDisable"
        @click="handleSubmitAgain"
        >{{ t("itemManage.SaveThenAdd") }}</a-button
      >
      <a-button
        type="primary"
        :disabled="isDisable"
        @click="handleSubmit"
        class="ibs-base--save"
      >
        {{ t("Save") }}
      </a-button>
      <a-button type="link" @click="toGoBack">{{ t("Back") }}</a-button>
    </a-form-item>
  </div>
</template>

<script>
import loadScript from "load-script";
import attachmentUpload from "../attachment-upload";
import Emitter from "common/vue/mixins/emitter";
import Locale from "common/vue/mixins/locale";
import { Popconfirm } from "ant-design-vue";

export default {
  name: "base-type",
  components: {
    attachmentUpload,
    APopconfirm: Popconfirm
  },
  mixins: [Emitter, Locale],
  data() {
    return {
      formLayout: "horizontal",
      difficultySelect: [
        {
          lable: this.t("Simple"),
          value: "simple"
        },
        {
          lable: this.t("Normal"),
          value: "normal"
        },
        {
          lable: this.t("Difficult"),
          value: "difficulty"
        }
      ],
      material: {
        validateStatus: "",
        errorMsg: ""
      },
      publicPath: process.env.BASE_URL,
      customStyle: "background: #ffffff;border: 0",
      materialEditor: "",
      analysisEditor: ""
    };
  },
  created() {},
  inject: [
    "subject",
    "category",
    "bank_id",
    "showCKEditorData",
    "showAttachment",
    "uploadSDKInitData",
    "cdnHost",
    "self"
  ],
  computed: {
    treeData: function() {
      let data = JSON.parse(JSON.stringify(this.category));
      this.formateCategory(data);
      data.unshift({
        value: 0,
        title: this.t("None"),
        key: 0
      });
      return data;
    }
  },
  props: {
    form: {
      type: Object,
      default: () => {}
    },
    showMaterial: {
      type: Boolean,
      default: false
    },
    showAnalysis: {
      type: Boolean,
      default: false
    },
    showCategory: {
      type: Boolean,
      default: true
    },
    showDifficulty: {
      type: Boolean,
      default: true
    },
    mode: {
      type: String,
      default: "create"
    },
    isSubItem: {
      type: Boolean,
      default: false
    },
    showModelBtn: {
      type: Boolean,
      default: false
    },
    isDisable: {
      type: Boolean,
      default: false
    },
    errorList: {
      type: Array,
      default() {
        return [];
      }
    }
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
          if (this.showMaterial) {
            this.initBaseMaterial();
            this.initBaseAnalysis();
          }
        });
      });
    });
  },
  methods: {
    //格式化分类格式
    formateCategory(category) {
      category.forEach(item => {
        item.value = item.id;
        item.title = item.name;
        item.key = `${item.name}${item.id}`;
        if (item.children) {
          this.formateCategory(item.children);
        }
      });
    },
    //设置分类id
    setCategoryId(value, label) {
      this.form.setFieldsValue({ [`base['category_name']`]: label[0] });
      this.form.setFieldsValue({ [`base['category_id']`]: value });
    },
    //设置难度
    setDifficulty(e) {
      this.form.setFieldsValue({ [`base['difficulty']`]: e.target.value });
    },
    //初始化材料题
    initBaseMaterial() {
      this.materialEditor = window.CKEDITOR.replace(
        "base-question-type_base['material']",
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
      this.materialEditor.setData(this.subject.material);
      this.materialEditor.on("change", () => {
        const data = this.materialEditor.getData();
        if (this.errorList.length == 0) {
          this.$emit("changeEditor", data);
        }
        this.form.setFieldsValue({ [`base['material']`]: data });
        this.checkMaterial(data);
      });
      this.materialEditor.on("blur", () => {
        const data = this.materialEditor.getData();
        this.form.setFieldsValue({ [`base['material']`]: data });
        this.checkMaterial(data);
      });
    },
    //初始化分析
    initBaseAnalysis() {
      this.analysisEditor = window.CKEDITOR.replace(
        "base-question-type_base['analysis']",
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
      this.analysisEditor.setData(this.subject.analysis);
      this.analysisEditor.on("change", () => {
        this.form.setFieldsValue({
          [`base['analysis']`]: this.analysisEditor.getData()
        });
        this.analysis = this.analysisEditor.getData();
      });
      this.analysisEditor.on("blur", () => {
        this.form.setFieldsValue({
          [`base['analysis']`]: this.analysisEditor.getData()
        });
        this.analysis = this.analysisEditor.getData();
      });
    },
    //校验材料题
    checkMaterial(value) {
      if (!value) {
        this.material.validateStatus = "error";
        this.material.errorMsg = this.t("itemManage.MaterialRule");
      } else {
        this.material.validateStatus = "";
        this.material.errorMsg = "";
      }
    },
    //保存
    handleSubmit(e) {
      e.preventDefault();
      this.dispatch("item-manage", "changeAgain", false);
      if (this.showMaterial) {
        this.checkMaterial(this.form.getFieldValue(`base['material']`));
      }
      this.$emit("getFromInfo");
    },
    handleSubmitAgain(e) {
      e.preventDefault();
      this.dispatch("item-manage", "changeAgain", true);
      if (this.showMaterial) {
        this.checkMaterial(this.form.getFieldValue(`base['material']`));
      }
      this.$emit("getFromInfo");
    },
    //关闭材料题子题的模态框
    cancel() {
      this.dispatch("material", "closeModal");
      this.dispatch("item-import", "closeModal");
    },
    //返回
    toGoBack() {
      this.dispatch("item-manage", "toGoBack");
    },
    getFileInfo(file) {
      this.$emit("getMaterialAttachment", file);
    },
    deleteFile(fileId) {
      this.$emit("deleteMaterialAttachment", fileId);
    },
    onFileSort(attachments) {
      this.$emit("onFileSort", attachments);
    },
    getAttachmentTypeData(type) {
      if (this.mode === "create") {
        return [];
      }

      const result = this.subject.attachments.filter(item => {
        return item.module == type;
      });

      return result;
    },
    confirm() {
      this.$emit("clickConfirm");
      document
        .getElementById("ib-treeSelect")
        .scrollTo({ behavior: "smooth", top: 0 });
    },
    cancelPopconfim(e) {
      e.preventDefault();
      this.dispatch("item-manage", "changeAgain", false);
      if (this.showMaterial) {
        this.checkMaterial(this.form.getFieldValue(`base['material']`));
      }
      if (this.errorList.length == 0) {
        this.$emit("getInitRepeatQuestion");
      }
      this.$nextTick(() => {
        this.$emit("renderFormula");
      });
      this.$emit("closeConfirm");
      this.$emit("getFromInfo");
    },
    handleData(e) {
      this.cancelPopconfim(e);
    }
  }
};
</script>
