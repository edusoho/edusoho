<template>
  <a-modal
    title="复制班课"
    :visible="visible"
    :confirm-loading="confirmLoading"
    ok-text="确定"
    cancelText="取消"
    width="900px"
    @ok="handleOk"
    @cancel="handleCancel"
  >
    <a-form
      :form="form"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 20 }"
    >
      <a-form-item label="班课名称">
        <a-input
          v-decorator="['title', { rules: [
            { required: true, message: '请填写班课名称' },
            { max: 40, message: '班课名称不能超过40个字' },
            { validator: validatorＴitle }
          ]}]"
          placeholder="请输入班课名称"
        />
      </a-form-item>

      <a-form-item label="选择课程">
        <a-select
          show-search
          v-decorator="['courseId', {
            initialValue: course.initialValue,
            rules: [
              { required: true, message: '请选择课程' }
            ]
          }]"
          :filter-option="false"
          placeholder="请选择课程"
          @search="(value) => handleSearch(value, 'course')"
          @popupScroll="(e) => handlePopupScroll(e, 'course')"
        >
          <a-select-option v-for="item in course.list" :key="item.id">
            {{ item.courseSetTitle }}
          </a-select-option>
        </a-select>
      </a-form-item>

      <a-form-item label="所属产品">
        <a-select
          show-search
          v-decorator="['productId', {
            initialValue: product.initialValue,
            rules: [
              { required: true, message: '请选择归属产品' }
            ]
          }]"
          placeholder="请选择归属产品"
          @search="(value) => handleSearch(value, 'product')"
          @popupScroll="(e) => handlePopupScroll(e, 'product')"
        >
          <a-select-option v-for="item in product.list" :key="item.id">
            {{ item.title }}
          </a-select-option>
        </a-select>
      </a-form-item>

      <a-form-item label="授课老师">
        <a-select
          show-search
          :filter-option="false"
          v-decorator="['teacherId', {
            initialValue: teacher.initialValue,
            rules: [
              { required: true, message: '请选择授课老师' }
            ]
          }]"
          placeholder="请选择授课教师"
          @search="(value) => handleSearch(value, 'teacher')"
          @popupScroll="(e) => handlePopupScroll(e, 'teacher')"
        >
          <a-select-option v-for="item in teacher.list" :key="item.id">
            {{ item.nickname }}
          </a-select-option>
        </a-select>
      </a-form-item>

      <a-form-item label="助教">
        <a-select
          show-search
          :filter-option="false"
          v-decorator="['assistantIds', {
            initialValue: assistant.initialValue,
            rules: [
              { required: true, message: '至少选择一位助教' }
            ]
          }]"
          mode="multiple"
          placeholder="请选择助教"
          @search="(value) => handleSearch(value, 'assistant')"
          @popupScroll="(e) => handlePopupScroll(e, 'assistant')"
        >
          <a-select-option v-for="item in assistant.list" :key="item.id">
            {{ item.nickname }}
          </a-select-option>
        </a-select>
      </a-form-item>
    </a-form>
  </a-modal>
</template>

<script>
import _ from '@codeages/utils';
import { ValidationTitle, Assistant, MultiClassProduct, MultiClass, Teacher, Me } from 'common/vue/service';

export default {
  name: 'CopyMultiClassModal',

  props: {
    visible: {
      type: Boolean,
      required: true
    },

    id: {
      type: [String, Number],
      required: true
    }
  },

  data() {
    return {
      confirmLoading: false,
      form: this.$form.createForm(this, { name: 'copy_multi_class' }),
      course: {
        list: [],
        title: '',
        flag: true,
        initialValue: undefined,
        paging: {
          pageSize: 10,
          current: 0
        }
      },
      product: {
        list: [],
        flag: true,
        initialValue: undefined,
        paging: {
          pageSize: 10,
          current: 0
        }
      },
      teacher: {
        list: [],
        title: '',
        flag: true,
        initialValue: undefined,
        paging: {
          pageSize: 10,
          current: 0
        }
      },
      assistant: {
        list: [],
        title: '',
        flag: true,
        initialValue: [],
        paging: {
          pageSize: 10,
          current: 0
        }
      }
    };
  },

  created() {
    this.fetchCourse();
    this.fetchProduct();
    this.fetchTeacher();
    this.fetchAssistant();
  },

  methods: {
    duplicateRemoval(data, id) {
      _.forEach(data, (item, index) => {
        if (item.id == id) {
          data.splice(index, 1);
          return false;
        }
      });
    },

    fetchCourse() {
      const { title, paging: { pageSize, current } } = this.course;

      const params = {
        isDefault: 1,
        limit: pageSize,
        offset: pageSize * current
      };

      if (title) {
        params.titleLike = title;
      }

      Me.get('teach_courses', { params }).then(res => {
        this.course.paging.current++;

        if (this.course.initialValue) {
          this.duplicateRemoval(res.data, this.course.initialValue);
        }

        this.course.list = _.concat(this.course.list, res.data);
        if (_.size(this.course.list) >= res.paging.total) {
          this.course.flag = false;
        }
      });
    },

    fetchProduct() {
      const { paging: { pageSize, current } } = this.product;

      const params = {
        limit: pageSize,
        offset: pageSize * current
      };

      MultiClassProduct.search(params).then(res => {
        this.product.paging.current++;

        if (this.product.initialValue) {
          this.duplicateRemoval(res.data, this.product.initialValue);
        }

        this.product.list = _.concat(this.product.list, res.data);
        if (_.size(this.product.list) >= res.paging.total) {
          this.product.flag = false;
        }
      });
    },

    fetchTeacher() {
      const { title, paging: { pageSize, current } } = this.teacher;
      const params = {
        limit: pageSize,
        offset: pageSize * current
      };
      if (title) {
        params.nickname = title;
      }
      Teacher.search(params).then(res => {
        this.teacher.paging.current++;
        if (this.teacher.initialValue) {
          this.duplicateRemoval(res.data, this.teacher.initialValue);
        }
        this.teacher.list = _.concat(this.teacher.list, res.data);
        if (_.size(this.teacher.list) >= res.paging.total) {
          this.teacher.flag = false;
        }
      });
    },

    fetchAssistant() {
      const { title, paging: { pageSize, current } } = this.assistant;
      const params = {
        limit: pageSize,
        offset: pageSize * current
      };

      if (title) {
        params.nickname = title;
      }

      Assistant.search(params).then(res => {
        this.assistant.paging.current++;
        _.forEach(this.assistant.initialValue, item => {
          this.duplicateRemoval(res.data, item);
        });
        this.assistant.list = _.concat(this.assistant.list, res.data);
        if (_.size(this.assistant.list) >= res.paging.total) {
          this.assistant.flag = false;
        }
      });
    },

    validatorＴitle: _.debounce(async function(rule, value, callback) {
      const { result } = await ValidationTitle.search({
        type: 'multiClass',
        title: value
      });

      result ? callback() : callback('产品名称不能与已创建的相同');
    }, 300),

    handleSearch: _.debounce(function(value, type) {
      this[type] = {
        list: [],
        title: value,
        flag: true,
        paging: {
          pageSize: 10,
          current: 0
        }
      };
      this[`fetch${_.capitalize(type)}`]();
    }, 300),

    handlePopupScroll: _.debounce(function (e, type) {
      const { scrollHeight, offsetHeight, scrollTop } = e.target;
      const maxScrollTop = scrollHeight - offsetHeight - 20;
      if ((maxScrollTop < scrollTop) && this[type].flag) {
        this[`fetch${_.capitalize(type)}`]();
      }
    }, 300),

    handleOk(e) {
      this.confirmLoading = true;
      // const { success } = await MultiClass.copyMultiClass(multiClass.id);
      if ('success') {
        this.$message.success('正在复制中...');
        this.confirmLoading = false;
        this.handleCancel();
      }
    },

    handleCancel() {
      this.$emit('event-communication', { event: 'cancel-modal' });
    }
  }
};
</script>
