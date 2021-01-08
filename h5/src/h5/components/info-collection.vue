<template>
  <div class="info-collection">
    <van-form ref="infoCollectForm">
      <template v-for="(item, index) in rule">
        <!-- text ，number -->
        <template v-if="item.type === 'input' || item.type === 'textarea'">
          <van-field
            :key="index"
            required-align="right"
            error-message-align="left"
            clearable
            v-model="item.value"
            :name="item.field"
            :label="item.title"
            :placeholder="getPlaceholder(item)"
            :error="errorRule[index].error"
            :error-message="errorRule[index].errorMessage"
            :type="getType(item)"
            @blur="checkField(index, item.value, item.validate)"
            :label-class="isRequired(item.validate) ? 'info-required' : ''"
            :rules="
              item.field == 'idcard'
                ? [{ validator: idcardValidator, message: '身份证号格式错误' }]
                : []
            "
          />
        </template>
        <template v-if="item.type === 'radio'">
          <van-field
            :key="index"
            :name="item.field"
            :label="item.title"
            :placeholder="getPlaceholder(item)"
            :label-class="isRequired(item.validate) ? 'info-required' : ''"
            required-align="right"
          >
            <template #input>
              <van-radio-group v-model="item.value" direction="horizontal">
                <van-radio
                  v-for="(sex, index) in item.options"
                  :key="index"
                  :name="sex.value"
                  >{{ sex.label }}</van-radio
                >
              </van-radio-group>
            </template>
          </van-field>
        </template>
        <!-- select -->
        <template v-if="isSelectType(item)">
          <van-field
            :key="index"
            readonly
            right-icon="arrow"
            v-model="item.value"
            :name="item.field"
            :label="item.title"
            :label-class="isRequired(item.validate) ? 'info-required' : ''"
            :placeholder="getPlaceholder(item)"
            :error="errorRule[index].error"
            :error-message="errorRule[index].errorMessage"
            @click="showPicker(item, index)"
          />
        </template>
      </template>
      <!-- 提交 -->
      <div class="info-footer-top"></div>
      <div class="info-footer">
        <template v-if="isAllowSkip">
          <div
            class="info-footer__btn info-footer__btn-border"
            @click="laterFillIn"
          >
            跳过
          </div>
        </template>

        <div class="info-footer__btn" @click="onSubmit">确认提交</div>
      </div>
    </van-form>

    <van-action-sheet v-model="birthtDateSelect.show">
      <van-datetime-picker
        v-model="birthtDateSelect.birthtDate"
        type="date"
        title="选择年月日"
        :min-date="birthtDateSelect.minDate"
        :max-date="birthtDateSelect.maxDate"
        @confirm="birthConfirm"
        @cancel="birthCancel"
      />
    </van-action-sheet>

    <van-action-sheet v-model="areaSelect.show">
      <van-area
        title="选择地区"
        :area-list="areaList"
        @confirm="areaConfirm"
        @cancel="areaCancel"
      />
    </van-action-sheet>
  </div>
</template>

<script>
import { arealist } from '@/utils/arealist';
import Api from '@/api';
const defaultType = ['input', 'InputNumber'];
const selectType = ['select', 'cascader', 'DatePicker'];
const idcardPattern = /^[1-9]\d{5}(19|20)\d{2}((0[1-9])|(1[0-2]))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/;
const rule = [
  {
    type: 'input',
    title: '姓名',
    field: 'name',
    value: '',
    validate: [
      { required: true, message: '姓名不能为空' },
      { min: 2, message: '最少输入2个字符' },
      { max: 20, message: '最多输入20个字符' },
    ],
  },
  {
    type: 'radio',
    field: 'gender',
    title: '性别',
    value: '男',
    options: [
      { value: '男', label: '男' },
      { value: '女', label: '女' },
    ],
    validate: [{ required: true, message: '姓名' }],
  },
  {
    type: 'input',
    field: 'age',
    title: '年龄',
    value: '',
    props: {
      type: 'number',
    },
    validate: [
      { required: true, message: '年龄不能为空' },
      { pattern: '^[1-9]([0-9])?$', message: '年龄不在正常范围内' },
    ],
  },
  {
    type: 'DatePicker',
    field: 'birthday',
    title: '生日',
    value: '',
    props: {
      type: 'date',
      format: 'yyyy-MM-dd',
      placeholder: '请选择出生年月日',
    },
    validate: [{ required: true, message: '生日不能为空' }],
  },
  {
    type: 'input',
    field: 'idcard',
    title: '身份证号',
    value: '',
    props: {
      placeholder: '仅支持中国大陆',
    },
    validate: [
      { required: true, message: '身份证号不能为空' },
      { pattern: idcardPattern, message: '身份证号格式错误' },
    ],
  },
  {
    type: 'input',
    field: 'phone',
    title: '手机号码',
    value: '',
    props: {
      type: 'number',
      placeholder: '仅支持中国大陆',
    },
    validate: [
      { required: true, message: '手机号不能为空' },
      { pattern: '^[1][0-9]{10}$', message: '手机号格式错误' },
    ],
  },
  {
    type: 'input',
    field: 'email',
    title: 'Email',
    value: '',
    validate: [
      { required: true, message: '请输入Email' },
      { type: 'email', message: 'Email格式错误' },
    ],
  },
  {
    type: 'input',
    field: 'wechat',
    title: '微信号',
    value: '',
    validate: [
      { required: true, message: '微信号不能为空' },
      {
        pattern: '^[a-zA-Z]([-_a-zA-Z0-9]{5,19})+$',
        message: '微信号格式错误',
      },
    ],
  },
  {
    type: 'input',
    field: 'qq',
    title: 'QQ号',
    value: '',
    props: {
      type: 'number',
    },
    validate: [
      { required: true, message: 'QQ号不能为空' },
      { pattern: '^[0-9]{5,11}$', message: 'QQ号格式错误' },
    ],
  },
  {
    type: 'input',
    field: 'weibo',
    title: '微博号',
    value: '',
    validate: [
      { required: true, message: '微博号不能为空' },
      { min: 4, message: '最少输入4个字符' },
      { max: 30, message: '最多输入30个字符' },
    ],
  },
  {
    type: 'cascader',
    title: '省市区县',
    field: 'province_city_area',
    value: ['陕西省', '西安市', '新城区'],
    props: {
      options: [],
      placeholder: '请选择省市区县',
    },
    validate: [{ required: true, message: '省市区县不能为空' }],
  },
  {
    type: 'input',
    title: '详细地址',
    field: 'address_detail',
    value: '',
    validate: [
      { required: true, message: '详细地址不能为空' },
      { min: 2, message: '最少输入2个字符' },
      { max: 40, message: '最多输入40个字符' },
    ],
  },
  {
    type: 'input',
    title: '职业',
    field: 'occupation',
    value: '',
    validate: [
      { required: true, message: '职业不能为空' },
      { min: 2, message: '最少输入2个字符' },
      { max: 40, message: '最多输入40个字符' },
    ],
  },
  {
    type: 'input',
    title: '公司',
    field: 'company',
    value: '',
    validate: [
      { required: true, message: '公司不能为空' },
      { min: 2, message: '最少输入2个字符' },
      { max: 40, message: '最多输入40个字符' },
    ],
  },
  {
    type: 'input',
    title: '职位',
    field: 'position',
    value: '',
    validate: [
      { required: true, message: '职位不能为空' },
      { min: 2, message: '最少输入2个字符' },
      { max: 40, message: '最多输入40个字符' },
    ],
  },
  {
    type: 'input',
    title: '学校',
    field: 'school',
    value: '',
    validate: [
      { required: true, message: '学校不能为空' },
      { min: 2, message: '最少输入2个字符' },
      { max: 40, message: '最多输入40个字符' },
    ],
  },
  {
    type: 'input',
    title: '年级',
    field: 'grade',
    value: '',
    validate: [
      { required: true, message: '年级不能为空' },
      { min: 2, message: '最少输入2个字符' },
      { max: 40, message: '最多输入40个字符' },
    ],
  },
  {
    type: 'input',
    title: '班级',
    field: 'class',
    value: '',
    validate: [
      { required: true, message: '班级不能为空' },
      { min: 2, message: '最少输入2个字符' },
      { max: 40, message: '最多输入40个字符' },
    ],
  },
  {
    type: 'input',
    title: '国家',
    field: 'country',
    value: '',
    validate: [
      { required: true, message: '国家不能为空' },
      { min: 2, message: '最少输入2个字符' },
      { max: 40, message: '最多输入40个字符' },
    ],
  },
  {
    type: 'input',
    title: '语言',
    field: 'language',
    value: '',
    validate: [
      { required: true, message: '语言不能为空' },
      { min: 2, message: '最少输入2个字符' },
      { max: 100, message: '最多输入100个字符' },
    ],
  },
  {
    type: 'textarea',
    title: '兴趣',
    field: 'interest',
    value: '',
    validate: [
      { required: true, message: '兴趣不能为空' },
      { min: 2, message: '最少输入2个字符' },
      { max: 100, message: '最多输入100个字符' },
    ],
  },
];

export default {
  components: {},
  props: {
    formRule: {
      type: Array,
      default: () => rule,
    },
    userInfoCollectForm: {
      type: Object,
      default: () => {},
    },
    targetType: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      birthtDateSelect: {
        minDate: new Date(1900, 1, 1),
        maxDate: new Date(),
        birthtDate: new Date(1990, 0, 1),
        show: false,
      },
      areaSelect: {
        show: false,
      },
      currentSelectIndex: 0,
      areaList: Object.freeze(arealist),
      errorRule: [],
      rule: this.formRule,
      areaIndex: null,
    };
  },
  computed: {
    isAllowSkip() {
      return this.userInfoCollectForm.allowSkip;
    },
  },
  watch: {},
  created() {
    this.getErrorRule();
  },
  methods: {
    getErrorRule() {
      const rule = this.rule;
      rule.forEach((item, index) => {
        // 后端返回回来的是array
        if (item.field === 'province_city_area') {
          this.areaIndex = index;
          this.$set(this.rule[index], 'value', this.arrayToString(item.value));
        }
        this.errorRule.push({ error: false, errorMessage: '' });
      });
    },
    onSubmit() {
      const formData = {};
      for (let i = 0; i < this.rule.length; i++) {
        if (!this.checkField(i, this.rule[i].value, this.rule[i].validate)) {
          this.$refs.infoCollectForm.scrollToField(this.rule[i].field);
          return;
        }
        formData[this.rule[i].field] = this.rule[i].value;
      }
      // 省市区需要转换为ａｒｒａｙ
      if (this.areaIndex !== null) {
        const areaKey = this.rule[this.areaIndex].field;
        const areaValue = this.rule[this.areaIndex].value;
        formData[areaKey] = this.stringToArray(areaValue);
      }
      this.setInfoCollection(formData);
    },
    laterFillIn() {
      this.$emit('submitForm');
    },
    arrayToString(value) {
      if (Array.isArray(value)) {
        return value.join(' ');
      }
      return value;
    },
    stringToArray(value) {
      return value.split(' ');
    },
    getType(item) {
      if (item.type === 'input') {
        return item.props?.type || 'text';
      }
      return item.type;
    },
    getPlaceholder(item) {
      if (item.props) {
        return item.props.placeholder || '';
      }
      return '';
    },
    checkField(index, value, validate) {
      if (!validate) {
        return true;
      }

      for (let i = 0; i < validate.length; i++) {
        if (validate[i].required && !value) {
          this.setError(index, true, validate[i].message);
          return false;
        }
        if (value) {
          const currentValue = value.length;
          if (validate[i].min && currentValue < validate[i].min) {
            this.setError(index, true, validate[i].message);
            return false;
          }
          if (validate[i].max && currentValue > validate[i].max) {
            this.setError(index, true, validate[i].message);
            return false;
          }
          if (validate[i].pattern) {
            const reg = new RegExp(validate[i].pattern);
            if (!reg.test(value)) {
              this.setError(index, true, validate[i].message);
              return false;
            }
          }
        }
      }
      this.setError(index, false, '');
      return true;
    },
    setError(index, error, errorMessage) {
      const rule = {
        error: error,
        errorMessage: errorMessage,
      };
      this.$set(this.errorRule, index, rule);
    },
    avalidator(value, validate) {
      if (!validate) {
        return;
      }
      for (let i = 0; i < validate.length; i++) {
        if (validate[i].min && value.length < validate[i].min) {
          console.log(i);
          return false;
        }
        if (validate[i].max && value.length > validate[i].max) {
          console.log(i);
          return false;
        }
        if (validate[i].pattern) {
          const reg = new RegExp(validate[i].pattern);
          console.log(reg);
          console.log(reg.test(value));
          return reg.test(value);
        }
      }
      return true;
    },
    isRequired(rule) {
      if (rule) {
        for (let i = 0; i < rule.length; i++) {
          if (rule[i].required) {
            return true;
          }
        }
      }

      return false;
    },
    isDefaultType(item) {
      if (defaultType.includes(item.type)) {
        return true;
      }
      return false;
    },
    isSelectType(item) {
      if (selectType.includes(item.type)) {
        return true;
      }
      return false;
    },
    showPicker(item, index) {
      this.currentSelectIndex = index;
      switch (item.type) {
        case 'DatePicker':
          this.birthtDateSelect.show = true;
          break;
        case 'cascader':
          this.areaSelect.show = true;
          break;
      }
    },
    formatDate(date) {
      return `${date.getFullYear()}/${date.getMonth() + 1}/${date.getDate()}`;
    },
    birthConfirm() {
      const currentSelectIndex = this.currentSelectIndex;
      this.$set(
        this.rule[currentSelectIndex],
        'value',
        this.formatDate(this.birthtDateSelect.birthtDate),
      );
      this.birthCancel();
    },
    birthCancel() {
      const currentSelectIndex = this.currentSelectIndex;
      this.checkField(
        currentSelectIndex,
        this.rule[currentSelectIndex].value,
        this.rule[currentSelectIndex].validate,
      );
      this.birthtDateSelect.show = false;
    },
    areaConfirm(val) {
      const currentSelectIndex = this.currentSelectIndex;
      const province = val[0].name;
      const city = val[1].name;
      const local = val[2].name;

      let area = province + ' ' + city + ' ' + local;
      if (province === city) {
        area = city + ' ' + local;
      }
      this.$set(this.rule[currentSelectIndex], 'value', area);
      this.areaCancel();
    },
    areaCancel() {
      const currentSelectIndex = this.currentSelectIndex;
      this.checkField(
        currentSelectIndex,
        this.rule[currentSelectIndex].value,
        this.rule[currentSelectIndex].validate,
      );
      this.areaSelect.show = false;
    },
    setInfoCollection(formData) {
      const data = {
        eventId: this.userInfoCollectForm.eventId,
        ...formData,
      };
      Api.setInfoCollection({
        data,
      }).then(res => {
        this.$toast('提交成功');
        this.laterFillIn();
      });
    },
    // 身份证校验
    idcardValidator(value) {
      let sum = 0;
      const weights = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
      const codes = '10X98765432';
      for (let i = 0; i < value.length - 1; i++) {
        sum += value[i] * weights[i];
      }
      const last = codes[sum % 11];

      return value[value.length - 1] == last;
    },
  },
};
</script>
