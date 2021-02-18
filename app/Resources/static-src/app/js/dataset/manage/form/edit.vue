<template>
    <div>
        <el-row>
            <div class="course-manage-subltitle cd-mb40 ml0">数据集</div>
            <el-col :span="12">
                <el-form :model="form" :rules="formRule"
                        ref="baseInfoForm"
                        label-position="right"
                        label-width="150px">
                    <el-form-item label="数据集标题">
                        <el-col>
                            <el-input ref="title" v-model="form.title" auto-complete="off"></el-input>
                        </el-col>
                    </el-form-item>
                    <el-form-item label="描述信息">
                        <el-col>
                            <el-input ref="subtitle" v-model="form.subtitle" type="textarea" rows="3"></el-input>
                        </el-col>
                    </el-form-item>
                    <el-form-item label="挂载目录选择">
                        <el-col>
                            <el-table
                                :data="tableData"
                                stripe>
                                <el-table-column prop="name" label="名称"></el-table-column>
                                <el-table-column prop="path" label="路径"></el-table-column>
                                <el-table-column prop="action" label="操作">
                                    <template slot-scope="scope">
                                        <el-button
                                        size="mini"
                                        @click="deleteDirectory(scope.row)">删除</el-button>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </el-col>
                    </el-form-item>
                </el-form>
            </el-col>
            <el-col :span="12">
                <div class="tree">
                    <v-tree ref='tree' :data='treeData1' :multiple="true" :tpl='tpl' :halfcheck='true' />
                </div>
            </el-col>
        </el-row>
    </div>

</template>

<script>
    import * as validation from 'common/element-validation';


    export default {
        name: "base-info",
        methods: {
            tpl (...args) {
                let {0: node, 2: parent, 3: index} = args
                let titleClass = node.selected ? 'node-title node-selected' : 'node-title'
                let dis = node.isChildren;
                    return <span>
                        <span class={titleClass} domPropsInnerHTML={node.name} onClick={() => {
                        this.$refs.tree.nodeSelected(node)
                        }}></span>
                        <el-button type="primary" onClick={() => this.asyncLoad(node)} disabled={dis} size="mini">刷新</el-button>
                    </span>
            },
            async asyncLoad (node) {
            this.$set(node, 'loading', true)
            let pro = new Promise(resolve => {
                setTimeout(resolve, 2000, ['async node1', 'async node2'])
            })
            this.$refs.tree1.addNodes(node, await pro)
            this.$set(node, 'loading', false)
            },
            search () {
                this.$refs.tree.searchNodes(this.searchword)
            }
        //     validateForm() {
        //         if (!this.$refs.baseInfoForm) {
        //             return {result: true, invalidFields: {}};
        //         }

        //         let result = false;
        //         let invalids = {};
        //         this.$refs.baseInfoForm.clearValidate();

        //         this.$refs.baseInfoForm.validate((valid, invalidFields) => {
        //             if (valid) {
        //                 result = true;
        //             } else {
        //                 invalids = invalidFields;
        //             }
        //         });

        //         return {result: result, invalidFields: invalids};
        //     },
        //     getFormData() {
        //         this.form.orgCode = $('.js-org-tree-select').children('option:selected').val();
        //         this.form.about = aboutEditor.getData();

        //         return this.form;
        //     },
        },
        data() {
            return {
                searchword: '',
                initSelected: ['node-1'],
                treeData1: [{
                    title: 'node1',
                    expanded: true,
                    children: [{
                    title: 'node 1-1',
                    expanded: true,
                    children: [{
                        title: 'node 1-1-1'
                    }, {
                        title: 'node 1-1-2'
                    }, {
                        title: 'node 1-1-3'
                    }]
                    }, {
                    title: 'node 1-2',
                    children: [{
                        title: "<span style='color: red'>node 1-2-1</span>"
                    }, {
                        title: "<span style='color: red'>node 1-2-2</span>"
                    }]
                    }]
                }],
                treeData2: [{
                    title: 'node1',
                    expanded: false,
                    async: true
                }],

                treeData3: [{
                    title: 'node1',
                    expanded: true,
                    children: [{
                    title: 'node 1-1',
                    expanded: true,
                    children: [{
                        title: 'node 1-1-1'
                    }, {
                        title: 'node 1-1-2'
                    }, {
                        title: 'node 1-1-3'
                    }]
                    }]
                }],
                tableData: [{
                name: '猫狗测试',
                path: '/www/work',
                }],
                uploadImageTemplate: '',
                form: {
                    title: "标题",
                    subtitle: "副标题",
                },
                formRule: {
                    title: [
                        {
                            required: true,
                            message: "请输入标题",
                            trigger: 'blur'
                        },
                        {
                            min: 2,
                            message: Translator.trans('validate.length_min.message', {'length': 2}),
                            trigger: 'blur',
                        },
                        {
                            max: 30,
                            message: Translator.trans('validate.length_max.message', {'length': 30}),
                            trigger: 'blur',
                        },
                        {validator: validation.trim, trigger: 'blur'},
                        {validator: validation.course_title, trigger: 'blur'}
                    ],
                    subtitle: [
                        {
                            max: 30,
                            message: Translator.trans('validate.length_max.message', {'length': 30}),
                            trigger: 'blur',
                        },
                    ]
                },
            };
        },
        mounted() {
        }
    }
</script>

<style scoped>

</style>