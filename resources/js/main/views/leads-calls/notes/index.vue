<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.lead_notes`)" class="p-0" />
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
                    {{ $t(`menu.lead_notes`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-table-content>
        <a-card class="page-content-container mt-20 mb-20">
            <LeadNotesTable
                logType="notes"
                :showFormFields="true"
                :showCampaignStatus="true"
                :showTableSearch="true"
                :showLeadDetails="true"
                :showAddButton="false"
                :showActionButton="false"
            />
        </a-card>
    </admin-page-table-content>
</template>

<script>
import { EyeOutlined, PlayCircleOutlined, DownOutlined } from "@ant-design/icons-vue";
import viewDrawer from "../../../../common/composable/viewDrawer";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import LeadNotesTable from "../../../components/lead-notes/index.vue";

export default {
    components: {
        EyeOutlined,
        PlayCircleOutlined,
        DownOutlined,

        AdminPageHeader,
        LeadNotesTable,
    },
    setup() {
        const leadDrawer = viewDrawer();

        return {
            ...leadDrawer,
        };
    },
};
</script>
