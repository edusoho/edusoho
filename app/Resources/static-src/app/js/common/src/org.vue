<template>
    <div>
        <div v-html="OrgTreeTemplate"></div>
    </div>
</template>

<script>
    export default {
        name: "org",
        props: {
            params: {},
            orgCode: '',
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