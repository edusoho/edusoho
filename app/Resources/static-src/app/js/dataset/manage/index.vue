<template>
    <div class="course-manage-info pbl">
        <info
            v-bind:info="info"
            v-bind:tree-data="treeData"
            v-bind:dir-get-path="dirGetPath"
            v-bind:submit-path="submitPath"
        ></info>
    </div>
</template>

<script>
import Info from './form/edit';

export default {
    name: "manage-info",
    components: {
        Info
    },
    props: {
        info: Array,
        treeData: Array,
        dirGetPath: String,
        submitPath: String,
    },
    methods: {},
    data() {
        return {};
    }
}
</script>

<style scoped>

</style>