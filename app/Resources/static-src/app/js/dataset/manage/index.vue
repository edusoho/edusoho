<template>
    <div class="course-manage-info pbl">
        <info
            v-bind:info="info"
            v-bind:tree-data="treeData"
            v-bind:dir-get-path="dirGetPath"
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
            info:Array,
            treeData:Array,
            dirGetPath:String
        },
        methods: {
            // validForm() {
            //     let invalidField = '';
            //     let valids = {
            //         baseInfo: this.$refs.baseInfo.validateForm(),
            //         marketing: this.$refs.marketing.validateForm()
            //     };

            //     for (let key in valids) {
            //         if (!valids[key].result) {
            //                 return false;
            //         }
            //     }

            //     return true;
            // },
            // submitForm() {
            //     if (!this.validForm()) {
            //         return;
            //     }

            //     let formData = {'_csrf_token': $('meta[name=csrf-token]').attr('content')};
            //     Object.assign(
            //         formData,
            //         this.$refs.baseInfo.getFormData(),
            //         this.$refs.marketing.getFormData()
            //     );

            //     this.$axios.post(this.infoSaveUrl, this.$qs.stringify(formData), {emulateJSON: true}).then((res) => {
            //         cd.message({type: 'success', message: Translator.trans('site.save_success_hint')});
            //         window.location.reload();
            //     });
            // }
        },
        data() {
            return {};
        }
    }
</script>

<style scoped>

</style>