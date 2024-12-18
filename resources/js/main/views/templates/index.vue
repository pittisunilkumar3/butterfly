<template>
    <div>
        <admin-page-header :pageTitle="$t('templates.title')" />
        
        <a-card class="page-content-card">
            <div class="table-page-search-wrapper">
                <a-form layout="inline">
                    <a-row :gutter="48">
                        <a-col :md="8" :sm="24">
                            <a-form-item>
                                <a-input
                                    v-model:value="searchData.templateName"
                                    :placeholder="$t('common.search')"
                                    allowClear
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :md="8" :sm="24">
                            <span class="table-page-search-submitButtons">
                                <a-button type="primary" @click="fetch" :loading="loading">
                                    {{ $t("common.search") }}
                                </a-button>
                                <a-button style="margin-left: 8px" @click="reset">
                                    {{ $t("common.reset") }}
                                </a-button>
                            </span>
                        </a-col>
                    </a-row>
                </a-form>
            </div>

            <div class="table-operator mb-15">
                <a-button type="primary" @click="addNew">
                    <plus-outlined />
                    {{ $t("common.add") }}
                </a-button>
            </div>

            <a-table
                :columns="columns"
                :row-key="(record) => record.id"
                :data-source="templates"
                :pagination="pagination"
                :loading="loading"
                @change="handleTableChange"
            >
                <template #bodyCell="{ column, record }">
                    <template v-if="column.dataIndex === 'action'">
                        <span>
                            <a-button type="link" @click="edit(record)">
                                {{ $t("common.edit") }}
                            </a-button>
                            <a-divider type="vertical" />
                            <a-popconfirm
                                :title="$t('common.delete_confirmation')"
                                @confirm="deleteTemplate(record.id)"
                            >
                                <a-button type="link" danger>
                                    {{ $t("common.delete") }}
                                </a-button>
                            </a-popconfirm>
                        </span>
                    </template>
                </template>
            </a-table>
        </a-card>
    </div>
</template>

<script>
import { defineComponent, ref, onMounted } from "vue";
import { message } from "ant-design-vue";
import { PlusOutlined } from "@ant-design/icons-vue";
import common from "../../../common/composable/common";
import AdminPageHeader from "../../../common/layouts/AdminPageHeader.vue";

export default defineComponent({
    components: {
        AdminPageHeader,
        PlusOutlined,
    },
    setup() {
        const { permsArray } = common();
        const searchData = ref({
            templateName: "",
        });
        const templates = ref([]);
        const loading = ref(false);
        const pagination = ref({
            current: 1,
            pageSize: 10,
            total: 0,
            showSizeChanger: true,
            showTotal: (total, range) =>
                `${range[0]}-${range[1]} of ${total} items`,
        });

        const columns = [
            {
                title: $t("templates.template_name"),
                dataIndex: "name",
                sorter: true,
            },
            {
                title: $t("templates.description"),
                dataIndex: "description",
            },
            {
                title: $t("common.actions"),
                dataIndex: "action",
                width: "150px",
            },
        ];

        const fetch = async (params = {}) => {
            loading.value = true;
            try {
                // Implement API call here
                loading.value = false;
            } catch (error) {
                loading.value = false;
                message.error(error.message);
            }
        };

        const reset = () => {
            searchData.value = {
                templateName: "",
            };
            pagination.value.current = 1;
            fetch();
        };

        const handleTableChange = (pag, filters, sorter) => {
            pagination.value.current = pag.current;
            pagination.value.pageSize = pag.pageSize;
            fetch({
                page: pag.current,
                pageSize: pag.pageSize,
                sortField: sorter.field,
                sortOrder: sorter.order,
                ...filters,
            });
        };

        const addNew = () => {
            // Implement add new template logic
        };

        const edit = (record) => {
            // Implement edit template logic
        };

        const deleteTemplate = async (id) => {
            try {
                // Implement delete API call here
                message.success("Template deleted successfully");
                fetch();
            } catch (error) {
                message.error(error.message);
            }
        };

        onMounted(() => {
            fetch();
        });

        return {
            searchData,
            templates,
            loading,
            pagination,
            columns,
            fetch,
            reset,
            handleTableChange,
            addNew,
            edit,
            deleteTemplate,
        };
    },
});
</script>
