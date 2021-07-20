<template>
  <div>
    <van-radio-group v-model="lang">
      <van-cell-group>
        <van-cell
          v-for="(item, index) in langOptions"
          :key="index"
          clickable
          :title="$t(item.title)"
          @click="toggleLang(item.name)"
        >
          <template #right-icon>
            <van-radio :name="item.name" ref="checkboxes" />
          </template>
        </van-cell>
      </van-cell-group>
    </van-radio-group>
    <div style="padding: 16px;">
      <van-button type="info" size="small" block @click="onClickSave">{{
        $t('btn.save')
      }}</van-button>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';

export default {
  name: 'SettingLang',

  data() {
    return {
      lang: '',
      langOptions: [
        {
          title: 'lang.zh',
          name: 'zh-cn',
        },
        {
          title: 'lang.en',
          name: 'en',
        },
      ],
    };
  },

  computed: {
    ...mapState({
      language: state => state.language,
    }),
  },

  created() {
    this.lang = this.language;
  },

  methods: {
    ...mapActions(['setLanguage']),

    toggleLang(value) {
      this.lang = value;
    },

    onClickSave() {
      this.$i18n.locale = this.lang;
      this.setLanguage(this.lang);
      this.$cookie.set('language', this.lang);
      this.$toast.success({
        message: this.$t('toast.switchSucceeded'),
        duration: 200,
      });
      setTimeout(() => {
        this.$router.go(-1);
      }, 200);
    },
  },
};
</script>
