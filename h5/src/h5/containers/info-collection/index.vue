<template>
  <div class="">
    <van-form validate-first @submit="onSubmit">
      <!-- 姓名
      <van-field
        v-model="rule[0].value"
        :name="rule[0].field"
        :label="rule[0].title"
        :required="isRequired(rule[0].validate)"
        required-align="right"
        :placeholder="`请输入${rule[0].title}`"
        clearable
        @blur="avalidator(rule[0].value, rule[0].validate)"
        :error-message="getErrorMessage(rule[0].value, rule[0].validate)"
        error-message-align="left"
        style="padding: 2.66667vw 4.26667vw;"
      /> -->
      <sexSelect />
      <div v-for="(item, index) in rule" :key="index">
        <div v-show="item.field === 'province_city_area' || 'birthday'">
          <areaSelect v-show="item.field === 'province_city_area'" />
          <birthdatSelect v-show="item.field === 'birthday'" />
        </div>
        <div>
          <van-field
            v-model="item.value"
            :name="item.field"
            :label="item.title"
            :required="isRequired(item.validate)"
            required-align="right"
            :placeholder="`请输入${item.title}`"
            clearable
            @blur="avalidator(item.value, item.validate)"
            :error-message="getErrorMessage(item.value, item.validate)"
            error-message-align="left"
            style="padding: 2.66667vw 4.26667vw;"
          />
        </div>
      </div>

      <div style="margin: 16px;">
        <van-button round block type="info" native-type="submit">
          提交
        </van-button>
      </div>
    </van-form>
  </div>
</template>

<script>
import areaSelect from './components/areaSelect.vue';
import birthdatSelect from './components/birthdaySelect.vue';
import sexSelect from './components/sexSelect.vue';
export default {
  components: {
    areaSelect,
    birthdatSelect,
    sexSelect,
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
        // {
        //   type: 'select',
        //   field: 'gender',
        //   title: '性别',
        //   value: '男',
        //   options: [
        //     { value: '男', label: '男' },
        //     { value: '女', label: '女' },
        //     { value: '保密', label: '保密' },
        //   ],
        // },
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
            // { required: true, message: '请输入微信号' },
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
    };
  },
  computed: {},
  watch: {},
  created() {},
  methods: {
    onSubmit(values) {
      console.log('submit', values);
    },
    areaConfirm(val) {
      this.info.province = val[0].name;
      this.info.city = val[1].name;
      if (this.info.province === this.info.city) {
        this.info.area = this.info.city;
      } else this.info.area = this.info.province + ' ' + this.info.city;
      this.show.area = false;
    },
    areaCancel() {
      this.show.area = !this.show.area;
    },
    avalidator(value, validate) {
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
    getErrorMessage(value, validate) {
      for (let i = 0; i < validate.length; i++) {
        if (value && validate[i].min && value.length < validate[i].min) {
          console.log('sss' + validate[i].message);
          return validate[i].message;
        }
        if (value && validate[i].max && value.length > validate[i].max) {
          console.log('sss' + validate[i].message);
          return validate[i].message;
        }
        const reg = new RegExp(validate[i].pattern);
        if (value && !reg.test(value)) {
          return validate[i].message;
        }
      }
      return '';
    },
    isRequired(rule) {
      for (let i = 0; i < rule.length; i++) {
        if (rule[i].required) {
          return true;
        }
      }
      return false;
    },

    birthConfirm() {
      this.info.showBirthday = this.formatDate(this.birthtDate);
      this.info.birthday = this.getTime(this.info.showBirthday) / 1000;
      this.show.birthday = false;
    },
    formatDate(date) {
      return `${date.getFullYear()}/${date.getMonth() + 1}/${date.getDate()}`;
    },
    birthCancel() {
      this.show.birthday = !this.show.birthday;
    },
  },
};
</script>
