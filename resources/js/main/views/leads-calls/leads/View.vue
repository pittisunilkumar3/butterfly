<template>
    <a-drawer
        :title="drawerTitle"
        :width="drawerWidth"
        :open="visible"
        :body-style="{ paddingBottom: '80px' }"
        :maskClosable="false"
        @close="onClose"
        :destroyOnClose="true"
    >
        <a-descriptions :title="$t('common.basic_details')">
            <a-descriptions-item :label="$t('lead.reference_number')">
                {{ lead && lead.reference_number ? lead.reference_number : "-" }}
            </a-descriptions-item>
            <a-descriptions-item :label="$t('lead.campaign')">
                {{ lead && lead.campaign ? lead.campaign.name : "-" }}
            </a-descriptions-item>
            <a-descriptions-item :label="$t('campaign.first_actioner')">
                {{
                    lead && lead.first_actioner && lead.first_actioner.name
                        ? lead.first_actioner.name
                        : "-"
                }}
            </a-descriptions-item>
            <a-descriptions-item :label="$t('campaign.last_actioner')">
                {{
                    lead && lead.last_actioner && lead.last_actioner.name
                        ? lead.last_actioner.name
                        : "-"
                }}
            </a-descriptions-item>
            <a-descriptions-item :label="$t('lead.call_duration')">
                {{ lead && lead.time_taken ? formatTimeDuration(lead.time_taken) : "-" }}
            </a-descriptions-item>
        </a-descriptions>

        <a-tabs v-model:activeKey="activeTabKey" class="mt-20">
            <a-tab-pane key="lead_data">
                <template #tab>
                    <span>
                        <UnorderedListOutlined />
                        {{ $t("lead.lead_data") }}
                    </span>
                </template>
                <a-descriptions
                    class="mt-10"
                    v-if="lead && lead.lead_data && lead.lead_data.length > 0"
                >
                    <a-descriptions-item
                        v-for="leadData in lead.lead_data"
                        :key="leadData.id"
                    >
                        <template #label>{{ leadData.field_name }}</template>
                        {{ leadData.field_value ? leadData.field_value : "-" }}
                    </a-descriptions-item>
                </a-descriptions>
            </a-tab-pane>
            <a-tab-pane key="call_logs">
                <template #tab>
                    <span>
                        <PhoneOutlined />
                        {{ $t("menu.call_logs") }}
                    </span>
                </template>

                <LeadLogTable
                    :leadId="lead.x_lead_id"
                    :showLeadDetails="false"
                    :showTableSearch="false"
                    :showAction="false"
                />
            </a-tab-pane>
            <a-tab-pane key="notes">
                <template #tab>
                    <span>
                        <FileTextOutlined />
                        {{ $t("menu.lead_notes") }}
                    </span>
                </template>
                <LeadNotesTable
                    :leadId="lead.xid"
                    :showAddButton="
                        lead && lead.campaign && lead.campaign.status != 'completed'
                            ? true
                            : false
                    "
                />
            </a-tab-pane>
        </a-tabs>
    </a-drawer>
</template>
<script>
import { defineComponent, ref, watch, computed } from "vue";
import { useI18n } from "vue-i18n";
import {
    UnorderedListOutlined,
    PhoneOutlined,
    FileTextOutlined,
} from "@ant-design/icons-vue";
import common from "../../../../common/composable/common";
import LeadLogTable from "../../../components/lead-logs/index.vue";
import LeadNotesTable from "../../../components/lead-notes/index.vue";

export default defineComponent({
    props: {
        visible: {
            default: false,
        },
        title: {
            default: "",
        },
        lead: {
            default: {},
        },
    },
    emits: ["close"],
    components: {
        UnorderedListOutlined,
        PhoneOutlined,
        FileTextOutlined,

        LeadLogTable,
        LeadNotesTable,
    },
    setup(props, { emit }) {
        const { formatTimeDuration } = common();
        const { t } = useI18n();
        const drawerTitle = ref(t("lead.lead_details"));
        const activeTabKey = ref("lead_data");

        const onClose = () => {
            activeTabKey.value = "lead_data";
            emit("close");
        };

        return {
            drawerTitle,
            formatTimeDuration,
            activeTabKey,

            onClose,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "60%",
        };
    },
});
</script>
