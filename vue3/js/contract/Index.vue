<script>
import EsConfigProvider from '../components/EsConfigProvider.vue';
import {defineComponent} from 'vue';

export default defineComponent({
  components: {EsConfigProvider}
});

</script>

<template>
  <es-config-provider>
    <router-view/>
  </es-config-provider>
</template>
