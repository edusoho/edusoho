<template>
  <a-affix :offsetTop="0">
    <div class="ibs-import-list ibs-mt16" id="item-bank-sdk-import-list">
      <div class="ibs-clearfix ibs-subject-rect__title">
        <span class="ibs-left">{{ t("itemImport.Title_list") }}</span>
      </div>

      <a-row class="ibs-mt24" v-if="items.length > 0">
        <div
          v-if="errorList.length"
          class="ibs-danger-color ibs-import-list-error-text"
        >
          <a-anchor :affix="false">
            <div style="margin-right: 4px;">
              {{ t("itemImport.Error_list_1") }}
            </div>
            <a-anchor-link
              :href="`#ibs-import-${item}`"
              v-for="(item, index) of errorList"
              :key="item"
            >
              <div slot="title" style="display: flex;">
                <div class="error-link">
                  {{ item + 1 }} {{ t("itemImport.Error_list_2") }}
                </div>
                <span v-if="index !== errorList.length - 1">、</span>
              </div>
            </a-anchor-link>
            <div style="margin-left: 4px;">
              {{ t("itemImport.Error_list_4") }}
            </div>
          </a-anchor>
        </div>
        <a-checkbox-group
          class="ibs-width-full"
          name="checkboxgroup"
          v-model="checkedList"
          @change="onChange"
        >
          <a-anchor :affix="false" class="custom-anchor">
            <a-anchor-link
              :href="`#ibs-import-${index}`"
              :title="index + 1"
              v-for="(item, index) of items"
              :key="index"
              :class="[
                { 'ibs-import-list-error': isError(index) },
                { 'ibs-import-list-worng': isWorng(index) }
              ]"
            >
              <a-checkbox
                v-show="batchOperation && item.type !== 'material'"
                :value="item.ids"
                :key="item.ids"
                @change="changeChoose($event, item.type, item.ids)"
                class="ibs-import-list-item__checkbox"
              />
              <span class="ibs-import-list-item__text">{{
                getTypeName(item.type)
              }}</span>
            </a-anchor-link>
          </a-anchor>
        </a-checkbox-group>
      </a-row>

      <div v-show="batchOperation">
        <a-row>
          <a-col class="ibs-mb8" :span="8">
            <a-checkbox @change="checkAll" :checked="All">{{
              t("itemImport.Check_all")
            }}</a-checkbox>
          </a-col>
          <a-col
            class="ibs-mb8"
            :span="8"
            v-for="(value, key, index) of totalTypes"
            :key="index"
          >
            <a-checkbox
              v-if="key !== 'material' && key !== 'uncertain_choice'"
              @change="checkChange($event, key)"
              :checked="getCheckedType(key)"
              >{{ t(key) }}</a-checkbox
            >
            <a-checkbox
              v-if="key === 'uncertain_choice'"
              @change="checkChange($event, key)"
              :checked="getCheckedType(key)"
              >{{ t("uncertain_choice_1") }}</a-checkbox
            >
          </a-col>
        </a-row>

        <a-row :gutter="16" class="ibs-mt8">
          <a-col class="ibs-mb8" :span="8">
            <a-button type="link" @click="setDiffcult">{{
              t("itemImport.Set_difficulty")
            }}</a-button>
          </a-col>
          <a-col class="ibs-mb8" :span="8">
            <a-button type="link" @click="setScore">{{
              t("itemImport.Set_score")
            }}</a-button>
          </a-col>
          <a-col class="ibs-mb8" :span="8">
            <a-button type="link" @click="setCategory">{{
              t("itemImport.Set_classification")
            }}</a-button>
          </a-col>
        </a-row>

        <a-button
          type="primary"
          class="ibs-import-list-item__btn"
          @click="closeOperation"
          >{{ t("complete") }}
        </a-button>
      </div>

      <a-button
        v-show="!batchOperation"
        type="primary"
        class="ibs-import-list-item__btn"
        @click="doOperation"
        >{{ t("Batch_operation") }}
      </a-button>
    </div>

    <a-modal
      :title="setModalTitle"
      v-model="showSetModal"
      @ok="handleSetModal"
      :destroyOnClose="true"
      :getContainer="getContainer"
      :cancelText="t('cancel')"
      :okText="t('confirm')"
    >
      <a-alert
        message="材料题不支持批量设置分数/难度/分类"
        show-icon
        banner
        style="margin-bottom: 10px;"
      >
        <template #icon>
          <a-icon type="info-circle" style="font-size: 16px; color: #faad14;" />
        </template>
      </a-alert>
      <div class="ibs-import-list-choose ibs-mb24">
        {{ t("itemImport.List_choose_1") }}：
        <div
          v-for="(value, key, index) in types"
          :key="index"
          class="ibs-import-list-choose-text"
          v-show="value.length"
        >
          {{ value.length }}{{ t("itemImport.List_choose_2")
          }}{{ getTypeName(key) }}
          {{ index === Object.keys(types).length - 1 ? "。" : "，" }}
        </div>
      </div>
      <a-form :form="form" class="ibs-import-modal-form">
        <a-form-item
          v-if="setModal === 'score'"
          :label="t('Score')"
          :label-col="{ span: 4 }"
          :wrapper-col="{ span: 16 }"
        >
          <a-input
            style="width: 200px"
            type="number"
            v-decorator="[
              'score',
              {
                rules: [{ required: true, validator: checkScore }],
                initialValue: 2
              }
            ]"
          />
        </a-form-item>
        <a-form-item
          v-if="setModal === 'score' && hasUncertainChoiceOrChoice(types)"
          class="missed-selection-score-item"
          :label-col="{ span: 4 }"
          :wrapper-col="{ span: 16 }"
        >
          <template #label>
            <a-tooltip placement="topLeft" arrow-point-at-center>
              <template slot="title">
                <div class="missed-selection-score-tip-content">漏选得分：当学员选对一个或多个正确答案，但还是有漏选正确答案时的得分（某题满分2分，含3个正确答案，选中1个或2个正确答案得1分，选中全部3个正确答案得2分）</div>
              </template>
              <div class="missed-selection-score-item-label">{{ t('otherScore1') }}</div>
            </a-tooltip>
          </template>
          <a-input
            style="width: 200px"
            type="number"
            v-decorator="[
              'otherScore1',
              {
                rules: [
                  {
                    required: true,
                    message: '请输入漏选得分',
                  },
                  {
                    validator: checkOtherScore1
                  }
                ],
                initialValue: 0
              }
            ]"
          />
        </a-form-item>
        <a-form-item
          v-if="setModal === 'diffcult'"
          :label="t('Difficulty')"
          :label-col="{ span: 4 }"
          :wrapper-col="{ span: 16 }"
        >
          <a-radio-group
            name="radioGroup"
            v-decorator="['difficulty', { initialValue: 'normal' }]"
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
          v-if="setModal === 'category'"
          :label="t('itemImport.Topic_classification')"
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
            v-decorator="['category_id', { initialValue: 0 }]"
          ></a-tree-select>
        </a-form-item>
        <a-form-item
          v-if="setModal === 'category'"
          label=""
          :label-col="{ span: 0 }"
          :wrapper-col="{ span: 20 }"
          style="marginBottom:0"
        >
          <a-input
            type="hidden"
            v-decorator="['category_name', { initialValue: '' }]"
          />
        </a-form-item>
      </a-form>
    </a-modal>
  </a-affix>
</template>
<script>
import Locale from "common/vue/mixins/locale";
export default {
  name: "import-list",
  mixins: [Locale],
  data() {
    return {
      checkedList: [],
      batchOperation: false,
      all: false,
      types: {},
      showSetModal: false,
      setModalTitle: "",
      setModal: "",
      form: this.$form.createForm(this, { name: "form" }),
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
      hasError: false
    };
  },
  props: {
    items: {
      type: Array,
      default: () => []
    },
    typeIndex: {
      type: Object,
      default: () => {}
    },
    //分类
    category: {
      type: Array,
      default: () => []
    },
    errorList: {
      type: Array,
      default: () => []
    },
    totalTypes: {
      type: Object,
      default() {
        return {};
      }
    },
    repeatList: {
      type: Array,
      default: () => []
    }
  },
  computed: {
    singleChoiceAll: function() {
      let type = "single_choice";
      if (
        this.types[type] &&
        this.types[type].length === this.typeIndex[type].length
      ) {
        return true;
      }
      return false;
    },
    uncertainChoiceAll: function() {
      let type = "uncertain_choice";
      if (
        this.types[type] &&
        this.types[type].length === this.typeIndex[type].length
      ) {
        return true;
      }
      return false;
    },
    choiceAll: function() {
      let type = "choice";
      if (
        this.types[type] &&
        this.types[type].length === this.typeIndex[type].length
      ) {
        return true;
      }
      return false;
    },
    fillAll: function() {
      let type = "fill";
      if (
        this.types[type] &&
        this.types[type].length === this.typeIndex[type].length
      ) {
        return true;
      }
      return false;
    },
    essayAll: function() {
      let type = "essay";
      if (
        this.types[type] &&
        this.types[type].length === this.typeIndex[type].length
      ) {
        return true;
      }
      return false;
    },
    determineAll: function() {
      let type = "determine";
      if (
        this.types[type] &&
        this.types[type].length === this.typeIndex[type].length
      ) {
        return true;
      }
      return false;
    },
    All: function() {
      if (this.checkedList.length === this.typeIndex.all.length) {
        return true;
      }
      return false;
    },
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
  methods: {
    getTypeName(type) {
      switch (type) {
        case "single_choice":
          return this.t("single_choice");
        case "fill":
          return this.t("fill");
        case "essay":
          return this.t("essay");
        case "material":
          return this.t("material");
        case "uncertain_choice":
          return this.t("uncertain_choice_1");
        case "choice":
          return this.t("choice");
        case "determine":
          return this.t("determine");
        default:
          return "";
      }
    },
    getCheckedType(type) {
      switch (type) {
        case "single_choice":
          return this.singleChoiceAll;
        case "fill":
          return this.fillAll;
        case "essay":
          return this.essayAll;
        case "material":
          return false;
        case "uncertain_choice":
          return this.uncertainChoiceAll;
        case "choice":
          return this.choiceAll;
        case "determine":
          return this.determineAll;
        default:
          return false;
      }
    },
    hasUncertainChoiceOrChoice(types) {
      const keys = Object.keys(types);
      const hasChoice = keys.includes('choice') && types?.choice.length > 0
      const hasUncertainChoice = keys.includes('uncertain_choice') && types?.uncertain_choice.length > 0
      return hasChoice || hasUncertainChoice
    },
    checkOtherScore1(rule, value, callback) {
      const parts = value.toString().split('.');
      const score = this.form.getFieldValue('score');
      if (value >= score) {
        callback('得分不超过全选得分');
      } else if (parts.length > 1 && parts[1].length > 1) {
        callback('最多只能输入一位小数');
      } else {
        callback();
      }
    },
    onChange(e) {
      if (e.length === this.typeIndex.all.length) {
        this.all = true;
      } else {
        this.all = false;
      }
    },
    checkAll(e) {
      let copyTypeIndex = JSON.parse(JSON.stringify(this.typeIndex));

      if (copyTypeIndex?.material) {
        delete copyTypeIndex.material;
      }

      if (e.target.checked) {
        this.checkedList = copyTypeIndex.all;
        this.types = copyTypeIndex;
      } else {
        this.checkedList = [];
        this.types = {};
      }
    },
    checkChange(e, type) {
      let copyTypeIndex = JSON.parse(JSON.stringify(this.typeIndex));
      if (e.target.checked) {
        this.checkedList = this.checkedList.concat(copyTypeIndex[type]);
        this.checkedList = Array.from(new Set(this.checkedList));
        if (!this.types[type]) {
          this.$set(this.types, type, copyTypeIndex[type]);
        } else {
          this.types[type] = copyTypeIndex[type];
        }
      } else {
        this.typeIndex[type].forEach(item => {
          const index = this.checkedList.indexOf(item);
          if (index > -1) {
            this.checkedList.splice(index, 1);
          }
        });
        this.types[type] = [];
      }
    },
    doOperation() {
      this.batchOperation = true;
    },
    closeOperation() {
      this.types = {};
      this.checkedList = [];
      this.batchOperation = false;
    },
    changeChoose(e, type, ids) {
      if (!this.types[type]) {
        this.$set(this.types, type, []);
      }
      if (e.target.checked) {
        this.types[type].push(ids);
      } else {
        let option = this.types[type].indexOf(ids);
        this.types[type].splice(option, 1);
      }
    },
    // 判断是否含有材料题
    isHasMaterial(items) {
      const isAllMaterial = items.every(item => item.type === "material");
      return isAllMaterial;
    },
    setDiffcult() {
      if (!this.isCheckItem() && this.isHasMaterial(this.items)) {
        this.$message.error(`${this.t("itemImport.Choose_topic_difficult")}`);
        return;
      }

      if (!this.isCheckItem()) {
        this.$message.error(`${this.t("itemImport.Choose_topic")}`);
        return;
      }
      this.showSetModal = true;
      this.setModal = "diffcult";
      this.setModalTitle = this.t("itemImport.Set_difficulty");
    },
    setScore() {
      if (!this.isCheckItem() && this.isHasMaterial(this.items)) {
        this.$message.error(`${this.t("itemImport.Choose_topic_score")}`);
        return;
      }

      if (!this.isCheckItem()) {
        this.$message.error(`${this.t("itemImport.Choose_topic")}`);
        return;
      }
      this.showSetModal = true;
      this.setModal = "score";
      this.setModalTitle = this.t("itemImport.Set_score");
    },
    setCategory() {
      if (!this.isCheckItem() && this.isHasMaterial(this.items)) {
        this.$message.error(`${this.t("itemImport.Choose_topic_category")}`);
        return;
      }

      if (!this.isCheckItem()) {
        this.$message.error(`${this.t("itemImport.Choose_topic")}`);
        return;
      }
      this.showSetModal = true;
      this.setModal = "category";
      this.setModalTitle = this.t("itemImport.Set_classification");
    },
    handleSetModal() {
      switch (this.setModal) {
        case "score":
          this.handleScore();
          break;
        case "diffcult":
          this.handleDiffcult();
          break;
        case "category":
          this.handleCategory();
          break;
        default:
          "";
      }
    },
    handleScore() {
      this.$nextTick(() => {
        this.form.validateFields(["score", "otherScore1"], { force: true }, (err, values) => {
          if (!err) {
            //设置分数
            this.$emit("setScore", this.checkedList, values.score, values.otherScore1);
            this.showSetModal = false;
          }
        });
      });
    },
    handleDiffcult() {
      this.$nextTick(() => {
        const difficulty = this.form.getFieldValue("difficulty");
        //设置难度
        this.$emit("setDifficult", this.checkedList, difficulty);
        this.showSetModal = false;
      });
    },
    handleCategory() {
      this.$nextTick(() => {
        const category_id = this.form.getFieldValue("category_id");
        const category_name =
          this.form.getFieldValue("category_name") || this.t("None");
        //设置分类
        this.$emit("setCategory", this.checkedList, category_id, category_name);
        this.showSetModal = false;
      });
    },
    isCheckItem() {
      return !!this.checkedList.length;
    },
    checkScore(rule, value, callback) {
      const fractionRule = /^(([1-9]{1}\d{0,2})|([0]{1}))(\.(\d){1})?$/;
      if (value < 0) {
        callback(`${this.t("ScoreRule.one")}`);
      } else if (value > 999) {
        callback(`${this.t("ScoreRule.two")}`);
      } else if (!fractionRule.test(value)) {
        callback(`${this.t("ScoreRule.three")}`);
      } else {
        callback();
      }
    },
    getContainer() {
      return document.getElementById("item-bank-sdk-import-list");
    },
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
      this.form.setFieldsValue({ ["category_name"]: label[0] });
      this.form.setFieldsValue({ ["category_id"]: value });
    },
    isError(index) {
      return this.errorList.includes(index);
    },
    isWorng(index) {
      return this.repeatList.includes(index);
    }
  }
};
</script>
