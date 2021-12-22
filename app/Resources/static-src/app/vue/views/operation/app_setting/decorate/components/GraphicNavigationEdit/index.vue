<template>
  <edit-layout>
    <template #title>{{ 'decorate.graphic_navigation_settings' | trans }}</template>

    <div class="design-editor">
      <div class="design-editor__item" v-for="(item, index) in moduleData" :key="index">
        <item
          :item="item"
          :index="index"
          @modity="handleModity"
        />
      </div>

      <div class="design-editor__item" v-if="moduleData.length < 8">
        <a-button type="primary" block @click="handleClickAdd">
          {{ 'decorate.add_graphic_navigation' | trans }}
        </a-button>
      </div>
    </div>

    <modity-image-modal
      ref="modal"
      @update-image="handleUpdateImage"
      @upload-image="handleUploadImage"
    />
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from '../EditLayout.vue';
import Item from './Item.vue';
import ModityImageModal from './ModityImageModal.vue';

export default {
  name: 'GraphicNavigationEdit',

  props: {
    moduleData: {
      type: Array,
      required: true
    }
  },

  components: {
    EditLayout,
    Item,
    ModityImageModal
  },

  data() {
    return {
      currentIndex: null
    }
  },

  methods: {
    handleClickAdd() {
      const params = {
        title: '',
        image: {},
        link: {}
      };

      this.update({ key: 'add', value: params });
    },

    handleModity(params) {
      const { index, type, value } = params;
      this.currentIndex = index;

      if (type === 'image') {
        this.$refs.modal.showModal();
        return;
      }

      if (type === 'remove' && _.size(this.moduleData) === 1) {
        this.$message.info(Translator.trans('decorate.not_less_than'));
        return;
      }

      this.update({
        key: type,
        value
      });
    },

    handleUpdateImage({ uri }) {
      const params = {
        key: 'image',
        value: { uri }
      };
      this.update(params);
    },

    handleUploadImage(data) {
      const params = {
        key: 'image',
        value: data
      };
      this.update(params);
    },

    update(params) {
      this.$emit('update-edit', {
        type: 'graphic_navigation',
        index: this.currentIndex,
        ...params
      });
    }
  }
}
</script>
