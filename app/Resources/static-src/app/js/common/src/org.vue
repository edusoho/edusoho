<template>
    <div>
        <div v-if="OrgTreeTemplate" v-html="OrgTreeTemplate"></div>
    </div>
</template>

<script>
    export default {
        name: "org",
        props: {
            params: {},
            orgCode: '',
        },
        watch: {
            OrgTreeTemplate(newVal, oldVal) {
                if (newVal && !$('.js-org-tree-select').length) {
                    this.$nextTick(() => {
                        import('app/js/org/org-tree-select/index.js');
                    });
                }
            }
        },
        methods: {
            renderOrgTree() {
                let params = Object.assign({
                    withoutFormGroup: true,
                    orgCode: this.orgCode
                }, this.params);

                this.$axios.get('/render/org?' + this.$qs.stringify(params)).then((res) => {
                    this.OrgTreeTemplate = res.data;
                });
            },
        },
        data() {

            return {
                OrgTreeTemplate: this.renderOrgTree(),
                orgCode: ''
            };
        },
    }
</script>

<style scoped>

</style>