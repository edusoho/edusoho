<script>
import listHeader from './components/header.vue';
import { Testpaper } from 'common/vue/service';
import {Badge as ABadge, message} from 'ant-design-vue';
import TestpaperTypeTag from '../../../common/src/TestpaperTypeTag.vue';

const columns = [
	{
		title: Translator.trans('question.bank.paper.name'),
		dataIndex: 'name',
		ellipsis: true,
		width: 240,
	},
	{
		title: Translator.trans('question.bank.paper.type'),
		dataIndex: 'type',
		scopedSlots: { customRender: "type" },
		width: 120,
	},
	{
		title: Translator.trans('question.bank.paper.num'),
		dataIndex: 'num',
		scopedSlots: { customRender: "num" },
		width: 90,
		align: 'right'
	},
	{
		title: Translator.trans('question.bank.paper.status'),
		dataIndex: 'status',
		scopedSlots: { customRender: "status" },
		width: 90,
	},
	{
		title: Translator.trans('question.bank.paper.numberOfItems/score'),
		scopedSlots: { customRender: "numberOfItemsAndScore" },
		width: 100,
	},
	{
		title: Translator.trans('question.bank.paper.creator/createdAt'),
		scopedSlots: { customRender: "creatorAndCreatedAt" },
		width: 160,
	},
	{
		title: Translator.trans('question.bank.paper.operation'),
		scopedSlots: { customRender: "operation" },
	},
];

export default {
	props: {
		itemBankId: null
	},
	components: {
		TestpaperTypeTag,
		listHeader
	},
	data() {
		return {
			status: undefined,
			keywordType: 'name',
			keyword: undefined,
			columns,
			pageData: [],
			loading: false,
			pagination: {
				current: 1,
				total: 0,
				pageSize: 10,
			},
			selectedRowKeys: [],
			pageSizeOptions: ['10', '20', '30', '40', '50'],
		};
	},
	methods: {
		async handleTableChange(pagination) {
			const pager = { ...this.pagination };
			pager.current = pagination.current;
			this.pagination = pager;

			const params = {
				limit: pagination.pageSize,
				offset: (pagination.current - 1) * pagination.pageSize
			};

			await this.fetchTestPaper(params);
		},

		async fetchTestPaper(params) {
			this.loading = true;
			const { data, paging } = await Testpaper.search({
				limit: 20,
				itemBankId: this.itemBankId,
				...params
			});

			const pagination = { ...this.pagination };
			pagination.total = paging.total;
			pagination.pageSize = Number(paging.limit);

			this.loading = false;
			this.pageData = data;
			this.pagination = pagination;
		},

		async onSearch(nickname) {
			this.keyWord = nickname;
			this.pagination.current = 1;
			await this.fetchTeacher();
		},

		onSelectChange(selectedRowKeys) {
			this.selectedRowKeys = selectedRowKeys;
		},
		handleSelectAllChange(e) {
			const currentPageDataIds = this.pageData.map(data => data.id);
			if (e.target.checked) {
				this.selectedRowKeys = this.selectedRowKeys.filter(key => !currentPageDataIds.includes(key)).concat(currentPageDataIds);
			} else {
				this.selectedRowKeys = this.selectedRowKeys.filter(key => !currentPageDataIds.includes(key));
			}
		},
		getTableTotal(total) {
			return Translator.trans('question.bank.paper.pageTotal', {total});
		},
		async handlePaginationChange(page, pageSize) {
			const pager = { ...this.pagination };
			pager.current = page;
			pager.pageSize = pageSize;
			this.pagination = pager;
			await this.handleTableChange(this.pagination)
		},
		async publish(record) {
			await Testpaper.changeStatus(record.id, 'open');
			record.status = 'open';
			message.success(Translator.trans('question.bank.paper.publish.success'));
		},
		async close(record) {
			await Testpaper.changeStatus(record.id, 'closed');
			record.status = 'closed';
			message.success(Translator.trans('question.bank.paper.publish.success'));
		}
	},
	watch: {
		status: function (val) {
			console.log(val);
		}
	},
	computed: {
		rowSelection() {
			const { selectedRowKeys } = this;
			return {
				selectedRowKeys,
				onChange: this.onSelectChange,
				hideDefaultSelections: true,
			};
		},
		isSelectAll() {
			const currentPageIds = this.pageData.map(data => data.id);
			for (const id of currentPageIds) {
				if (!this.selectedRowKeys.includes(id)) {
					return false;
				}
			}
			return true;
		},
		isIndeterminate() {
			const currentPageIds = this.pageData.map(data => data.id);
			for (const id of currentPageIds) {
				if (this.selectedRowKeys.includes(id)) {
					return true;
				}
			}
			return false;
		}
	},
	async created() {
		await this.fetchTestPaper(this.pagination);
	}
};
</script>

<template>
	<div class="test-paper-list-container">
		<list-header/>
		<div class="condition-bar">
			<a-select v-model="status" :placeholder="'question.bank.paper.status'|trans" style="width: 156px" allow-clear>
				<a-select-option value="1">
					{{'question.bank.paper.generating'|trans}}
				</a-select-option>
				<a-select-option value="2">
					{{ 'question.bank.paper.draft'|trans }}
				</a-select-option>
				<a-select-option value="3">
					{{ 'question.bank.paper.fail'|trans }}
				</a-select-option>
			</a-select>
			<a-select :placeholder="'question.bank.paper.type'|trans" style="width: 156px" allow-clear>
				<a-select-option value="3">
					{{ 'question.bank.paper.regular'|trans }}
				</a-select-option>
				<a-select-option value="1">
					{{ 'question.bank.paper.random'|trans }}
				</a-select-option>
				<a-select-option value="2">
					{{ 'question.bank.paper.ai_personality'|trans }}
				</a-select-option>
			</a-select>
			<a-select v-model="keywordType" style="width: 100px">
				<a-select-option value="name">
					{{ 'question.bank.paper.name'|trans }}
				</a-select-option>
				<a-select-option value="creator">
					{{ 'question.bank.paper.creator'|trans }}
				</a-select-option>
			</a-select>
			<a-input v-model="keyword" :placeholder="'question.bank.paper.typeKeyword'|trans" style="flex: 1 0 0"></a-input>
			<a-button type="primary" >{{ 'site.search_hint'|trans }}</a-button>
			<a-button>{{ 'question.bank.reset.btn'|trans }}</a-button>
		</div>
		<a-table
				:columns="columns"
				:data-source="pageData"
				:row-key="record => record.id"
				:pagination="false"
				:row-class-name="() => 'teacher-manage-row'"
				:loading="loading"
				:row-selection="rowSelection"
				@change="handleTableChange"
		>
			<template slot="type" slot-scope="type">
				<testpaper-type-tag :type="type"/>
			</template>
			<template slot="num" slot-scope="num">
				<span>{{ num ? num : '-' }}</span>
			</template>
			<template slot="status" slot-scope="status">
				<a-badge v-if="status === 'draft'" color="gray" :text="'question.bank.paper.draft'|trans" />
				<a-badge v-if="status === 'open'" color="green" :text="'question.bank.paper.published'|trans" />
				<a-badge v-if="status === 'closed'" color="red" :text="'question.bank.paper.closed'|trans" />
				<a-badge v-if="status === 'generating'" color="orange" :text="'question.bank.paper.generating'|trans" />
			</template>
			<template slot="numberOfItemsAndScore" slot-scope="record">
				{{ `${record.question_count} / ${record.total_score}` }}
			</template>
			<template slot="creatorAndCreatedAt" slot-scope="record">
				<div>
					<span>{{ record.created_user.nickname }}</span>
					<br/>
					<span class="created_time">{{ $dateFormat(record.created_time, 'YYYY-MM-DD HH:mm:ss')}}</span>
				</div>
			</template>
			<template slot="operation" slot-scope="record">
				<div class="operation-group">
					<a-button v-if="['draft', 'open'].includes(record.status)" type="link" class="operation-group-button-active">{{ 'question.bank.paper.preview'|trans }}</a-button>
					<a-button v-if="['generating', 'fail'].includes(record.status)" type="link" :disabled="true">{{ 'question.bank.paper.preview'|trans }}</a-button>
					<a-button v-if="['open'].includes(record.status)" type="link" class="operation-group-button-active">{{ 'question.bank.paper.close'|trans }}</a-button>
					<a-button v-if="['draft', 'closed'].includes(record.status)" type="link" class="operation-group-button-active" @click="publish(record)">{{ 'question.bank.paper.publish'|trans }}</a-button>
					<a-button v-if="['generating', 'fail'].includes(record.status)" type="link" :disabled="true">{{ 'question.bank.paper.publish'|trans }}</a-button>
					<a-button v-if="['draft', 'open'].includes(record.status)" type="link" class="operation-group-button-active">{{ 'question.bank.paper.edit'|trans }}</a-button>
				</div>
			</template>
		</a-table>
		<div class="list-bottom">
			<div class="selector-operate">
				<a-checkbox :indeterminate="isIndeterminate && !isSelectAll" :checked="isSelectAll" @change="handleSelectAllChange">
					<span class="checkbox-text">{{ 'question.bank.paper.selectAll'|trans }}</span>
				</a-checkbox>
				<span>{{ 'question.bank.paper.selectedItems'|trans({'select': this.selectedRowKeys.length}) }}</span>
			</div>
			<a-pagination
					show-quick-jumper
					show-size-changer
					:page-size-options="pageSizeOptions"
					style="margin-top: 16px;"
					:show-total="total => getTableTotal(total)"
					v-model="pagination.current"
					:total="pagination.total"
					@showSizeChange="handlePaginationChange"
					@change="handlePaginationChange"
			/>
		</div>
	</div>
</template>