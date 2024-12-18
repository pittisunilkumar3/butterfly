<template>
    <a-table
        :columns="appointmentColumns"
        :row-key="(record) => record.xid"
        :data-source="responseData.allFollowUps"
        :pagination="false"
    >
        <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'campaign'">
                {{ record.campaign.name }}
                <br />
                <small>
                    {{
                        record.lead_follow_up && record.lead_follow_up.date_time
                            ? formatDateTime(record.lead_follow_up.date_time)
                            : ""
                    }}
                </small>
            </template>
            <template v-if="column.dataIndex === 'action'">
                <a-tooltip :title="$t('lead.go_resume_call')">
                    <a-space>
                        <a-button
                            type="primary"
                            :style="{
                                backgroundColor: '#1ec997',
                                borderColor: '#1ec997',
                            }"
                            @click="goAndResumeLead(record)"
                        >
                            <template #icon>
                                <PlayCircleOutlined />
                            </template>
                        </a-button>
                        <a-button type="primary" @click="showViewDrawer(record)">
                            <template #icon>
                                <EyeOutlined />
                            </template>
                        </a-button>
                    </a-space>
                </a-tooltip>
            </template>
        </template>
    </a-table>

    <!-- Global Compaonent -->
    <view-lead-details
        :visible="isViewDrawerVisible"
        :lead="viewDrawerData"
        @close="hideViewDrawer"
    />
</template>

<script>
import { createVNode, reactive, ref } from "vue";
import {
    ExclamationCircleOutlined,
    PlayCircleOutlined,
    EyeOutlined,
} from "@ant-design/icons-vue";
import { Modal } from "ant-design-vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import common from "../../../common/composable/common";
import viewDrawer from "../../../common/composable/viewDrawer";

export default {
    props: ["responseData"],
    components: {
        PlayCircleOutlined,
        EyeOutlined,
    },
    setup() {
        const router = useRouter();
        const { formatDateTime } = common();
        const leadDrawer = viewDrawer();
        const { t } = useI18n();
        const appointmentColumns = ref([
            {
                title: t("lead.campaign"),
                dataIndex: "campaign",
            },
            {
                title: t("common.action"),
                dataIndex: "action",
            },
        ]);

        const goAndResumeLead = (record) => {
            Modal.confirm({
                title: t("common.are_you_sure") + "?",
                icon: createVNode(ExclamationCircleOutlined),
                content: t("lead.are_you_want_to_resume_this_lead"),
                centered: true,
                okText: t("common.yes"),
                okType: "danger",
                cancelText: t("common.no"),
                onOk() {
                    router.push({
                        name: "admin.call_manager.take_action",
                        params: {
                            id: record.xid,
                        },
                    });
                },
                onCancel() {},
            });
        };

        return {
            formatDateTime,
            ...leadDrawer,
            appointmentColumns,
            goAndResumeLead,
        };
    },
};
</script>

<style></style>
