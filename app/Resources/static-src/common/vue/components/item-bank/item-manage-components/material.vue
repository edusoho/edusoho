<template>
  <a-form :form="form" id="item-bank-sdk-material-created">
    <base-type
      @getFromInfo="getFromInfo"
      @getMaterialAttachment="getMaterialAttachment"
      @onFileSort="onFileSort"
      @deleteMaterialAttachment="deleteMaterialAttachment"
      @previewMaterialAttachment="previewMaterialAttachment"
      :form="form"
      v-bind="$attrs"
      :mode="mode"
      :isDisable="isDisable"
      :errorList="errorList"
      @changeEditor="changeEditor"
      @renderFormula="renderFormula"
      @getInitRepeatQuestion="getInitRepeatQuestion"
    >
      <template v-slot:subQuestions>
        <div class="ibs-mt24">
          <a-form-item :wrapper-col="{ span: 16, offset: 4 }">
            <preview-model
              v-for="(item, index) in newQuestions"
              :value="item"
              :key="index"
              :questions="item"
              @getSeq="getSeq"
            >
            </preview-model>
          </a-form-item>
          <a-form-item
            :label="t('itemManage.addSubItem')"
            :label-col="{ span: 4 }"
            :wrapper-col="{ span: 16 }"
          >
            <a-button
              type="primary"
              class="ibs-mr8"
              data-type="single_choice"
              @click="showModal"
              ><i class="ib-icon ib-icon-add"></i
              >{{ t("single_choice") }}</a-button
            >
            <a-button
              type="primary"
              class="ibs-mr8"
              data-type="choice"
              @click="showModal"
              ><i class="ib-icon ib-icon-add"></i>{{ t("choice") }}</a-button
            >
            <a-button
              type="primary"
              class="ibs-mr8"
              data-type="uncertain_choice"
              @click="showModal"
              ><i class="ib-icon ib-icon-add"></i
              >{{ t("uncertain_choice") }}</a-button
            >
            <a-button
              type="primary"
              class="ibs-mr8"
              data-type="judge"
              @click="showModal"
              ><i class="ib-icon ib-icon-add"></i>{{ t("determine") }}</a-button
            >
            <a-button
              type="primary"
              class="ibs-mr8"
              data-type="fill"
              @click="showModal"
              ><i class="ib-icon ib-icon-add"></i>{{ t("fill") }}</a-button
            >
            <a-button
              type="primary"
              class="ibs-mr8"
              data-type="essay"
              @click="showModal"
              ><i class="ib-icon ib-icon-add"></i>{{ t("essay") }}</a-button
            >
            <a-modal
              :title="t('itemManage.addItem')"
              v-model="visible"
              @ok="handleOk"
              width="800px"
              :cancelText="t('cancel')"
              :destroyOnClose="true"
              :footer="null"
              :getContainer="getContainer"
            >
              <essay-type
                v-if="subType === 'essay'"
                :showCategory="false"
                :showDifficulty="false"
                :subQuestions="subQuestions"
                :isSubItem="true"
                :mode="subMode"
                :aiAnalysisEnable="aiAnalysisEnable"
                @patchData="patchData"
                @getAiAnalysis="getAiAnalysis"
              ></essay-type>

              <fill-type
                v-if="subType === 'fill'"
                :showCategory="false"
                :showDifficulty="false"
                :isSubItem="true"
                :subQuestions="subQuestions"
                :mode="subMode"
                :aiAnalysisEnable="aiAnalysisEnable"
                @patchData="patchData"
                @initStatus="initStatus"
                @getAiAnalysis="getAiAnalysis"
              ></fill-type>

              <judge-type
                v-if="subType === 'judge'"
                :showCategory="false"
                :showDifficulty="false"
                :isSubItem="true"
                :subQuestions="subQuestions"
                :mode="subMode"
                :aiAnalysisEnable="aiAnalysisEnable"
                @patchData="patchData"
                @getAiAnalysis="getAiAnalysis"
              ></judge-type>

              <single-type
                v-if="subType === 'single_choice'"
                :showCategory="false"
                :showDifficulty="false"
                :isSubItem="true"
                :subQuestions="subQuestions"
                :mode="subMode"
                :aiAnalysisEnable="aiAnalysisEnable"
                @patchData="patchData"
                @getAiAnalysis="getAiAnalysis"
              ></single-type>

              <choice-type
                v-if="subType === 'choice' || subType === 'uncertain_choice'"
                :type="subType"
                :showCategory="false"
                :showDifficulty="false"
                :isSubItem="true"
                :subQuestions="subQuestions"
                :mode="subMode"
                :aiAnalysisEnable="aiAnalysisEnable"
                @patchData="patchData"
                @getAiAnalysis="getAiAnalysis"
              ></choice-type>
            </a-modal>
          </a-form-item>
        </div>
      </template>
    </base-type>
  </a-form>
</template>

<script>
import baseType from "./base";
import essayType from "./essay";
import fillType from "./fill";
import judgeType from "./judge";
import singleType from "./single-choice";
import choiceType from "./choice";
import previewModel from "./preview/preview-model";
import Locale from "common/vue/mixins/locale";

export default {
  name: "material",
  mixins: [Locale],
  data() {
    return {
      form: this.$form.createForm(this, { name: "base-question-type" }),
      visible: false,
      subItem: {},
      subType: "",
      subQuestions: {},
      newQuestions: [],
      currentSeq: 0,
      subMode: "create",
      typeArray: {
        true_false: "judge",
        uncertain_choice: "uncertain_choice",
        choice: "choice",
        single_choice: "single_choice",
        text: "fill",
        rich_text: "essay"
      }
    };
  },
  components: {
    baseType,
    essayType,
    judgeType,
    fillType,
    singleType,
    choiceType,
    previewModel
  },
  inject: ["bank_id", "subject", "showCKEditorData"],
  props: {
    mode: {
      type: String,
      default: "create"
    },
    isDisable: {
      type: Boolean,
      default: false
    },
    aiAnalysisEnable: {
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
  computed: {
    questions: function() {
      if (this.mode === "edit") {
        return this.subject.questions;
      }
      return this.newQuestions;
    }
  },
  mounted() {
    this.$on("closeModal", this.cancel);
    if (this.mode === "edit") {
      this.newQuestions = this.questions;
      this.newQuestions.map((item, index) => {
        item.seq = index + 1;
      });
    }
  },
  methods: {
    getContainer() {
      return document.getElementById("item-bank-sdk-material-created");
    },
    getFromInfo() {
      this.form.validateFieldsAndScroll((err, values) => {
        if (!err) {
          this.formateData(values);
        }
      });
    },

    formateData(values) {
      values.base.attachments = this.subject.attachments;
      values.base.questions = this.questions;
      const data = values.base;
      if (values.base.questions.length > 0) {
        this.$emit("patchData", data);
      } else {
        this.$message.error(this.t("itemManage.addSubItemRule"));
      }
      return data;
    },
    cancel() {
      this.visible = false;
    },
    showModal(e) {
      const target = e.target;
      this.subType = target.getAttribute("data-type");
      this.visible = true;
      this.subMode = "create";
    },
    handleOk(e) {
      console.log(e);
      this.visible = false;
    },
    patchData(value) {
      this.$emit("getData", value);
      this.subItem = value.questions[0];
      if (this.subMode === "edit") {
        this.$set(this.newQuestions, this.currentSeq, value.questions[0]);
      } else {
        this.newQuestions.push(value.questions[0]);
        this.newQuestions.map((item, index) => {
          item.seq = index + 1;
        });
      }
      this.visible = false;
    },

    initStatus(value) {
      this.subQuestions = value;
    },
    showEditModal(seq) {
      this.visible = true;
      this.subType = this.typeArray[this.newQuestions[seq].answer_mode];
      this.subQuestions = this.newQuestions[seq];
      this.subMode = "edit";
    },
    getSeq(data) {
      this.currentSeq = data.seq;
      if (data.operate === "delete") {
        this.newQuestions.splice(this.currentSeq, 1);
        this.newQuestions.map((item, index) => {
          item.seq = index + 1;
        });
      } else {
        this.showEditModal(this.currentSeq);
      }
    },
    // 材料题获取附件
    getMaterialAttachment(data) {
      this.subject.attachments.push(data);
    },
    onFileSort(attachments) {
      let attachmentsOrigin = this.subject.attachments;

      attachmentsOrigin = attachmentsOrigin.filter(item => {
        return item.module !== attachments[0].module;
      });

      attachmentsOrigin = attachmentsOrigin.concat(attachments);

      this.$set(this.subject, "attachments", attachmentsOrigin);
    },
    // 材料题删除获取附件
    deleteMaterialAttachment(fileId) {
      this.subject.attachments = this.subject.attachments.filter(item => {
        return item.id !== fileId;
      });
    },
    previewMaterialAttachment(fileId) {
      this.$emit("previewMaterialAttachment", fileId);
    },
    changeEditor(data) {
      this.$emit("changeEditor", data);
    },
    getInitRepeatQuestion() {
      this.$emit("getInitRepeatQuestion");
    },
    renderFormula() {
      this.$emit("renderFormula");
    },
    getAiAnalysis(data, disable, enable, complete, finish) {
      this.form.validateFieldsAndScroll((err, values) => {
        if (!err) {
          data.material = values.base.material;
        }
      });
      this.$emit("getAiAnalysis", data, disable, enable, complete, finish);
    },
  }
};
</script>
