<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.call_logs`)" class="p-0" />
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.leads_calls`) }}
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.call_logs`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-table-content>
        <a-card class="page-content-container mt-20 mb-20">
            <CallLogTable :showAction="false" :showCampaignStatus="true" />
        </a-card>
    </admin-page-table-content>
</template>

<script>
import { EyeOutlined, PlayCircleOutlined, DownOutlined } from "@ant-design/icons-vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import CallLogTable from "../../../components/lead-logs/index.vue";

export default {
    components: {
        EyeOutlined,
        PlayCircleOutlined,
        DownOutlined,

        AdminPageHeader,
        CallLogTable,
    },
    setup() {
        return {};
    },
};
</script>
