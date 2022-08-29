<template>
  <module-frame
    containerClass="setting-search"
    :isActive="isActive"
    :isIncomplete="isIncomplete"
  >
    <div slot="preview">
      <van-search
        shape="round"
        left-icon="static/images/search-icon.jpg"
        :placeholder="$t('search.placeholder')"
        style="padding: 10px 16px;background-color: #f7f9fa;"
      />
    </div>

    <div slot="setting">
      <e-suggest
        v-if="moduleData.tips"
        :suggest="moduleData.tips"
        :key="moduleData.moduleType"
      ></e-suggest>
      <header class="title">
        {{ $t('search.searchSettings') }}
      </header>
      <div class="text-14 color-gray mts">
        <i class="el-icon-warning"></i>
        {{ $t('search.atPresent') }}
      </div>
    </div>
  </module-frame>
</template>
<script>
import moduleFrame from '../module-frame';
import suggest from '&/components/e-suggest/e-suggest.vue';

const searchIcon = `data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAA
ICAgICAQICAgIDAgIDAwYEAwMDAwcFBQQGCAcJCAgHCAgJCg0LCQoMCggICw8LDA0ODg8OCQsQERA
OEQ0ODg7/2wBDAQIDAwMDAwcEBAcOCQgJDg4ODg4ODg4ODg4ODg4ODg4ODg4ODg4ODg4ODg4ODg4O
Dg4ODg4ODg4ODg4ODg4ODg7/wgARCAAgACADAREAAhEBAxEB/8QAGgAAAgIDAAAAAAAAAAAAAAAABw
gCBgQFCf/EABcBAQEBAQAAAAAAAAAAAAAAAAMEAgH/2gAMAwEAAhADEAAAAOszBUFxpudcGV1arncK
KrBTCpUHWO5daVwmpSzssnpYaCjrp3nT/8QALhAAAQQBAgUCAwkAAAAAAAAAAQIDBAUGBxEACBIhQRM
iJDGCEBgjYpGSsbLy/9oACAEBAAE/ANZNY8ho9QafSjSioZyXVm5Z9cJkH4WnijfeS+dx4B2SSP6JXG
5bNRL5gWOoPMbmr96573G8cmmBDZUfCED+QlHF1I1y5bWWskssola4aSsrAuk2DITcVTRUB6qHOrd4An
uVE/QPeMdyCoynB6rI6GaixprKMiRDkN7gONqG4Ox2IPggjcHseOWxtN/r/wAxudWH4127m71Mgr7qYi
xCQ2gHwCCAR+QfZNhRLKll10+OiXClMqZksuDdLjagUqSR5BBIPHJ249B0Qz3Dy8t+Bi2eWNXWlZJ6WEl
Cwn9y1q+vh+4+7bz1ZRZ5IlcbSHUmSmWLgoKmau1APWl0gEpDhKj/AIWRElRZ1czMhSW5UV5AWy8y4Foc
Se4UFDcEHwRxrZrhj2k2DuMtuou8+ngMUGORiXJMt9Z6WyUJ7pQCdyTt1bdKd1EDjl305sNMeWWtpr5fXl
Ni+7a3p3CvinyCpJI7EpSEIJHYlBI4v6CjyrFJ1DkdZGuqWWjokQ5jIcbcHzHY+Qe4I7ggEEEcO8nuDw5b4
xDPM8wKteWVLrKPIiiN9IWhSv1UeJ/JppUrS+8raxdoMxmAORcssZ6pE+O+g9Tbm46U7dQ7hIBI40tYz2Jo
XRQtTXIj2axmyxPkQnvUbkhCilDpOw9ykBJV2+fH/8QAHREAAgMAAwEBAAAAAAAAAAAAAAECAxESITETQf/aA
AgBAgEBPwCEElrPql4hcZrP048NLHiw9IvC5adWR69HForrZZIjsBX6uyN7JuJ//8QAHBEAAgMAAwEAAAAAAAA
AAAAAAAECERIDECIh/9oACAEDAQE/AG7FCxLBF6IfepKyPkXgUrJTONFaRnJgif/Z`;

export default {
  name: 'search',
  components: {
    moduleFrame,
    'e-suggest': suggest,
  },
  props: {
    active: {
      type: Boolean,
      default: false,
    },
    moduleData: {
      type: Object,
      default: () => {},
    },
    incomplete: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      searchIcon
    }
  },
  computed: {
    isActive: {
      get() {
        return this.active;
      },
      set() {},
    },
    isIncomplete: {
      get() {
        return this.incomplete;
      },
      set() {},
    },
    copyModuleData: {
      get() {
        return this.moduleData;
      },
      set() {},
    },
  },
  watch: {
    copyModuleData: {
      handler(data) {
        this.$emit('updateModule', data);
      },
      deep: true,
    },
  },
};
</script>

<style lang="scss" scoped>
  /deep/ .van-search__content {
    background-color: #fff;
  }

  /deep/ .van-search {
    background-color: transparent;
  }

  /deep/ .van-icon__image {
    margin-top: 5px;
  }

</style>
