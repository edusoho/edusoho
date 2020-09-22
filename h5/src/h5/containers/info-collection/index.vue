<template>
  <div class="">
    <van-form ref="infoCellectForm">
      <!-- <sexSelect /> -->
      <template v-for="(item, index) in rule">
        <!-- text ，number -->
        <template v-if="isDefaultType(item)">
          <van-field
            :key="index"
            v-model="item.value"
            :name="item.field"
            :label="item.title"
            :required="isRequired(item.validate)"
            required-align="right"
            :placeholder="`请输入${item.title}`"
            clearable
            :error="errorRule[index].error"
            :error-message="errorRule[index].errorMessage"
            @blur="checkField(index, item.value, item.validate)"
            error-message-align="left"
          />
        </template>
        <!-- select -->
        <template v-if="isSelectType(item)">
          <van-field
            :key="index"
            readonly
            v-model="item.value"
            :name="item.field"
            :label="item.title"
            placeholder="请选择"
            right-icon=" iconfangxiang my_setting-more-special"
            icon-prefix="iconfont"
            @click="showPicker(item, index)"
          />
        </template>
      </template>

      <div style="margin: 16px;">
        <van-button round block type="info" @click="onSubmit">
          提交
        </van-button>
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

    <van-action-sheet v-model="sexSelect.show">
      <van-picker
        show-toolbar
        :columns="sexSelect.columns"
        @confirm="sexConfirm"
        @cancel="sexCancel"
      />
    </van-action-sheet>
  </div>
</template>

<script>
// import areaSelect from './components/areaSelect.vue';
// import birthdatSelect from './components/birthdaySelect.vue';
// import sexSelect from './components/sexSelect.vue';
import { arealist } from '@/utils/arealist';
const defaultType = ['input', 'InputNumber'];
const selectType = ['select', 'cascader', 'DatePicker'];
export default {
  components: {
    // areaSelect,
    // birthdatSelect,
    // sexSelect,
  },
  data() {
    return {
      test: [
        {
          type: 'input',
          title: '姓名',
          field: 'name',
          value: '',
          validate: [
            { required: true, message: '请输入姓名' },
            { min: 2, message: '最少2个字' },
            { max: 20, message: '最多20个字' },
          ],
        },
        {
          type: 'input',
          field: 'weibo_name',
          title: '新浪微博名',
          value: '',
          validate: [
            { required: true, message: '请输入新浪微博名' },
            { min: 4, message: '最少4个字' },
            { max: 30, message: '最多30个字' },
          ],
        },
      ],
      rule: [
        {
          type: 'input',
          title: '姓名',
          field: 'name',
          value: '',
          validate: [
            { required: true, message: '请输入姓名' },
            { min: 2, message: '最少2个字' },
            { max: 20, message: '最多20个字' },
          ],
        },
        {
          type: 'select',
          field: 'gender',
          title: '性别',
          value: '男',
          options: [
            { value: '男', label: '男' },
            { value: '女', label: '女' },
            { value: '保密', label: '保密' },
          ],
        },
        {
          type: 'InputNumber',
          field: 'age',
          title: '年龄',
          value: '',
          validate: [
            { required: true, message: '请输入您的年龄' },
            { min: 1, message: '请输入正确的年龄' },
            { max: 99, message: '请输入正确的年龄' },
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
            placeholder: '请选择',
          },
          validate: [],
        },
        {
          type: 'input',
          field: 'idcard',
          title: '身份证号（中国大陆）',
          value: '',
          validate: [
            { required: true, message: '请输入身份证号' },
            {
              pattern: '[0-9]{17}[0-9xX]{1}',
              message: '身份证号码格式不正确',
            },
          ],
        },
        {
          type: 'input',
          field: 'phone',
          title: '手机号（中国大陆）',
          value: '',
          props: {
            type: 'number',
          },
          validate: [
            { required: true, message: '请输入手机号' },
            { pattern: '^[1][0-9]{10}$', message: '手机号格式不正确' },
          ],
        },
        {
          type: 'input',
          field: 'wechat',
          title: '微信号',
          value: '',
          validate: [
            { required: true, message: '请输入微信号' },
            {
              pattern: '^[a-zA-Z]([-_a-zA-Z0-9]{5,19})+$',
              message: '微信号格式不正确',
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
            { required: true, message: '请输入QQ号' },
            { pattern: '^[0-9]{5,11}$', message: 'QQ号格式不正确' },
          ],
        },
        {
          type: 'input',
          field: 'weibo_name',
          title: '新浪微博名',
          value: '',
          validate: [
            { required: true, message: '请输入新浪微博名' },
            { min: 4, message: '最少4个字' },
            { max: 30, message: '最多30个字' },
          ],
        },
        {
          type: 'input',
          field: 'email',
          title: 'Email',
          value: '',
          validate: [
            { required: true, message: '请输入Email' },
            { type: 'email', message: 'Email格式不正确' },
          ],
        },
        {
          type: 'cascader',
          title: '省市',
          field: 'province_city_area',
          value: '',
          props: {
            options: window.province_city_area || [],
          },
          validate: [],
        },
        {
          type: 'input',
          title: '详细地址',
          field: 'address_detail',
          value: '',
          validate: [
            { required: true, message: '请输入详细地址' },
            { min: 2, message: '最少2个字' },
            { max: 40, message: '最多40个字' },
          ],
        },
        {
          type: 'input',
          title: '职业',
          field: 'occupation',
          value: '',
          validate: [
            { required: true, message: '请输入职业' },
            { min: 2, message: '最少2个字' },
            { max: 40, message: '最多40个字' },
          ],
        },
        {
          type: 'input',
          title: '公司',
          field: 'company',
          value: '',
          validate: [
            { required: true, message: '请输入公司' },
            { min: 2, message: '最少2个字' },
            { max: 40, message: '最多40个字' },
          ],
        },
        {
          type: 'input',
          title: '职位',
          field: 'position',
          value: '',
          validate: [
            { required: true, message: '请输入职业' },
            { min: 2, message: '最少2个字' },
            { max: 40, message: '最多40个字' },
          ],
        },
        {
          type: 'input',
          title: '学校',
          field: 'school',
          value: '',
          validate: [
            { required: true, message: '请输入学校' },
            { min: 2, message: '最少2个字' },
            { max: 40, message: '最多40个字' },
          ],
        },
        {
          type: 'input',
          title: '年级',
          field: 'grade',
          value: '',
          validate: [
            { required: true, message: '请输入年级' },
            { min: 2, message: '最少2个字' },
            { max: 40, message: '最多40个字' },
          ],
        },
        {
          type: 'input',
          title: '班级',
          field: 'class',
          value: '',
          validate: [
            { required: true, message: '请输入班级' },
            { min: 2, message: '最少2个字' },
            { max: 40, message: '最多40个字' },
          ],
        },
        {
          type: 'input',
          title: '国家',
          field: 'country',
          value: '',
          validate: [
            { required: true, message: '请输入国家' },
            { min: 2, message: '最少2个字' },
            { max: 40, message: '最多40个字' },
          ],
        },
        {
          type: 'input',
          title: '语言',
          field: 'language',
          value: '',
          validate: [
            { required: true, message: '请输入语言' },
            { min: 2, message: '最少2个字' },
            { max: 100, message: '最多100个字' },
          ],
        },
        {
          type: 'input',
          title: '兴趣',
          field: 'interest',
          value: '',
          validate: [
            { required: true, message: '请输入兴趣' },
            { min: 2, message: '最少2个字' },
            { max: 100, message: '最多100个字' },
          ],
        },
      ],
      birthtDateSelect: {
        minDate: new Date(1900, 1, 1),
        maxDate: new Date(),
        birthtDate: new Date(),
        show: false,
      },
      areaSelect: {
        show: false,
      },
      sexSelect: {
        show: false,
        columns: ['男', '女', '保密'],
      },
      currentSelectIndex: 0,
      areaList: Object.freeze(arealist),
      errorRule: [],
    };
  },
  computed: {},
  watch: {},
  created() {
    this.getErrorRule();
  },
  methods: {
    getErrorRule() {
      const rule = this.rule;
      rule.forEach(() => {
        this.errorRule.push({ error: false, errorMessage: '' });
      });
    },
    onSubmit() {
      for (let i = 0; i < this.rule.length; i++) {
        if (!this.checkField(i, this.rule[i].value, this.rule[i].validate)) {
          this.$refs.infoCellectForm.scrollToField(this.rule[i].field);
          break;
        }
      }
    },
    checkField(index, value, validate) {
      if (!validate) {
        return;
      }
      for (let i = 0; i < validate.length; i++) {
        if (validate[i].min && value.length < validate[i].min) {
          this.setError(index, true, validate[i].message);
          return false;
        }
        if (validate[i].max && value.length > validate[i].max) {
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
      // console.log('aaa');
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
      for (let i = 0; i < rule.length; i++) {
        if (rule[i].required) {
          return true;
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
        case 'select':
          this.sexSelect.show = true;
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
      // this.rule[currentSelectIndex].value = this.formatDate(this.birthtDate);
      this.birthCancel();
    },
    birthCancel() {
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
      this.areaSelect.show = false;
    },
    sexCancel() {
      this.sexSelect.show = false;
    },
    sexConfirm(value) {
      const currentSelectIndex = this.currentSelectIndex;
      this.$set(this.rule[currentSelectIndex], 'value', value);
      this.sexCancel();
    },
  },
};
</script>
