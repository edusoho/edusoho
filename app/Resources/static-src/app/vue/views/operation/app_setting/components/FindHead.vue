<template>
  <div class="app-header">
    <img v-show="topHeader" src="/static-dist/app/img/vue/app_head.png" />
    <div class="app-header__search clearfix" :class="{ mt16: !topHeader }">
      <div class="school-title pull-left text-overflow">{{ site.name }}</div>
      <div class="pull-right mt4">
        <img :src="searchIcon" style="width: 20px;margin-right: 16px;" />
        <img :src="informationIcon" style="width: 20px;" />
      </div>
    </div>
  </div>
</template>

<script>
import { Setting } from 'common/vue/service';

const searchIcon = `data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAMAAAC7IEhfAAA
AQlBMVEUAAAAfIy8fJC4gIy0ZIygfJS4fIy4fJC8dIyofJi8hIy8fIysdIy4eIy4eJC8eIy4jIy4fIy8dIy8fIy8e
Iy8fJC/hkI9yAAAAFXRSTlMA34AfEJ9g7zCvcFBgQM+/MLBwUJCHZvvVAAABJUlEQVQ4y8WU63KEIAyFuQQQvG63vP
+rFkQ5hYl1pzM7e34JfDlJJCr+K9Ja30OTkTFr8fYvTLkISXVlPLqDkPJ44F1Vsdn2xRZ2WDGcydgoIJtRw/oZara0
Zzwt9uagwozosQ1OaXw5c+hjr8dRl1jqHLDGU0tek+ySp7U9O3JGmdrHs7W0yXB/+I7x60xhTosJ4OM8JzsfW1sgRFQ
tMQ6C15CTVaWKGKae1SJ1iYK6PvX7QLqrEVGvdm1wUb1CjA+sJrZIXG4VuXac2svtp4cYTmN6YGkY0B/jBz27qceEW2
bPt8GUp3Plv8Lf4WOq7zJPlGEo71nt2MqSVnZ/CjcKxZI62UBOkRAge1dfWGmmjIG8+D8W6I6EQA6vkbieG80kPqsfz
nEWyUyGP3AAAAAASUVORK5CYII=`

const informationIcon = `data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAMAAAC7IEhfAA
AAOVBMVEUAAAAeIy4jIy4fJC8fIy8fJC8jIzcZIyMeIy4hJC8eJC8eIy4fJC0fIy8fIysgIyoeIywfIy4fJC/jM5ADAA
AAEnRSTlMAgB/f3+8QEEDPz7+gUFAwkGB0qpUbAAAAlUlEQVQ4y+3U3Q6DIAyG4RZXYPNv6/1f7PqFbGGBiCeLGn1PquY
JiSFAmxa52QzntZ0Eg05X5BIUXky+sKPFbhf8B5xElfHe2wfsLduMFTioZXPEHO0Bc6jA2KUVvc3epRVl2tdfnw7mp/BV
3grygb8xHFfOtZdCFk4DWTNn3SHhHpwVqchDwgVqBJlcW65ykE8OdMjeZqgVz960T5MAAAAASUVORK5CYII=`

export default {
  props: {
    topHeader: {
      type: Boolean,
      default: true
    }
  },

  data() {
    return {
      site: {},
      searchIcon,
      informationIcon
    }
  },

  mounted() {
    this.getSchoolName();
  },

  methods: {
    async getSchoolName() {
      this.site = await Setting.get('site');
    }
  }
}
</script>

<style lang="less" scoped>
.app-header {
  padding-bottom: 12px;
  width: 100%;
  background-color: #fff;

  img {
    width: 100%;
  }

  &__search {
    padding: 0 16px;

    .school-title {
      font-size: 16px;
      color: #333;
      font-weight: 600;
      line-height: 28px;
      max-width: 68%;
    }

    /deep/ .ant-input-affix-wrapper {
      width: 30%;
      .ant-input {
        height: 28px;
        border-radius: 24px;
      }
    }
  }
}
</style>
