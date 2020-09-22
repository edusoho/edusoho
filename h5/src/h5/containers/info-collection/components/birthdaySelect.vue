<template>
  <div>
    <van-field
      readonly
      v-model="info.showBirthday"
      :value="value"
      label="生日"
      placeholder="请选择年月日"
      right-icon=" iconfangxiang my_setting-more-special"
      icon-prefix="iconfont"
      @click="birthCancel"
    />
    <van-action-sheet v-model="show.birthday">
      <van-datetime-picker
        v-model="birthtDate"
        type="date"
        title="选择年月日"
        :min-date="minDate"
        :max-date="maxDate"
        @confirm="birthConfirm"
        @cancel="birthCancel"
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
        birthday: '',
        showBirthday: '',
      },
      show: {
        birthday: false,
      },
      value: '',
      minDate: new Date(1900, 1, 1),
      maxDate: new Date(),
      birthtDate: new Date(),
    };
  },
  methods: {
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
    // 日期转换为时间戳
    getTime(time) {
      const date = new Date(time); // time: 2020-9-1
      return date.getTime();
    },
    // 将时间戳转换为日期
    timestampToTime(timestamp) {
      const date = new Date(timestamp * 1000); // 时间戳为10位需*1000，时间戳为13位的话不需乘1000
      const Y = date.getFullYear() + '-';
      const M =
        (date.getMonth() + 1 < 10
          ? '0' + (date.getMonth() + 1)
          : date.getMonth() + 1) + '-';
      const D = date.getDate();
      return Y + M + D;
    },
  },
};
</script>

<style></style>
