<template>
  <div class="">
    <van-form @submit="onSubmit">
      <template v-for="(item, index) in test">
        <van-field
          :key="index"
          v-model="item.value"
          :name="item.field"
          :label="item.title"
          :required="isRequired(item.validate)"
          :placeholder="`请输入${item.title}`"
          :rules="[
            ...item.validate,
            {
              validator: validator(item.value, item.validate),
              message: getErrorMessage(item.value, item.validate),
            },
          ]"
          error-message-align="left"
        />
      </template>

      <div style="margin: 16px;">
        <van-button round block type="info" native-type="submit">
          提交
        </van-button>
      </div>
    </van-form>
  </div>
</template>

<script>
export default {
  components: {},
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
          props: {
            min: 1,
            max: 99,
          },
        },
        {
          type: 'DatePicker',
          field: 'birthday',
          title: '生日',
          value: [],
          props: {
            type: 'date',
            format: 'yyyy-MM-dd',
            placeholder: '请选择',
          },
        },
        {
          type: 'input',
          field: 'idcard',
          title: '身份证号（中国大陆）',
          value: [],
          validate: [
            { required: true, message: '请输入身份证号' },
            { pattern: '[0-9]{17}[0-9xX]{1}', message: '身份证号码格式不正确' },
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
          value: [],
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
          value: [],
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
          value: [],
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
          value: [],
          validate: [
            { required: true, message: '请输入Email' },
            { type: 'email', message: 'Email格式不正确' },
          ],
        },
        {
          type: 'cascader',
          title: '省市',
          field: 'province_city_area',
          value: [],
          props: {
            options: window.province_city_area || [],
          },
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
    validator(value, validate) {
      for (let i = 0; i < validate.length; i++) {
        if (validate[i].min && value.length < validate[i].min) {
          return false;
        }
        if (validate[i].max && value.length > validate[i].max) {
          return false;
        }
      }
      return true;
    },
    getErrorMessage(value, validate) {
      for (let i = 0; i < validate.length; i++) {
        if (validate[i].min && value.length < validate[i].min) {
          console.log('sss');
          return validate[i].message;
        }
        if (validate[i].max && value.length > validate[i].max) {
          return validate[i].message;
        }
      }
      console.log('sss');
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
  },
};
</script>
