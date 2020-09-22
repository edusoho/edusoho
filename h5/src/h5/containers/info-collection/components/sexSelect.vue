<template>
  <div>
    <!-- 性别 -->
    <van-field
      readonly
      v-model="info.sex"
      label="性别"
      placeholder="请选择性别"
      right-icon=" iconfangxiang my_setting-more-special"
      icon-prefix="iconfont"
      @click="sexCancel"
    />
    <van-action-sheet v-model="show.sex">
      <van-picker
        show-toolbar
        :columns="Sexcolumns"
        @confirm="sexConfirm"
        @cancel="sexCancel"
      />
    </van-action-sheet>
  </div>
</template>

<script>
export default {
  components: {},
  data() {
    return {
      info: {
        sex: '男',
      },
      sex: ['男', '女', '保密'],
      Sexcolumns: ['男', '女', '保密'],
      show: {
        sex: false,
      },
      value: '',
    };
  },
  methods: {
    sexCancel() {
      this.show.sex = !this.show.sex;
    },
    sexConfirm(value) {
      this.info.sex = value;
      this.show.sex = false;
    },
  },
};
</script>

<style></style>
