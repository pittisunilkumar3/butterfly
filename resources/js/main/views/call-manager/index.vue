<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.call_manager`)" class="p-0" />
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.call_manager`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <div class="dashboard-page-content-container">
        <div v-if="allCampaigns != undefined">
            <a-row v-if="allCampaigns.length == 0" :gutter="[15, 15]" class="mt-30 mb-20">
                <a-col :span="24">
                    <a-empty
                        :image-style="{
                            height: '250px',
                        }"
                    >
                        <template #description>
                            <a-typography-text type="warning" strong>
                                {{ $t("campaign.no_call_manager_data") }}
                            </a-typography-text>
                        </template>

                        <CampaignAddButton
                            btnType="primary"
                            @onAddSuccess="campaignAdded"
                            :tooltip="false"
                        >
                            {{ $t("campaign.add") }}
                        </CampaignAddButton>
                    </a-empty>
                </a-col>
            </a-row>
            <a-row v-else :gutter="[15, 15]" class="mt-30 mb-20">
                <a-col
                    v-for="allCampaign in allCampaigns"
                    :key="allCampaign.xid"
                    :xs="24"
                    :sm="24"
                    :md="12"
                    :lg="8"
                    :xl="8"
                >
                    <a-card :title="allCampaign.name" hoverable>
                        <a-card-meta>
                            <template #description>
                                <a-row :gutter="16" class="mt-10">
                                    <a-col :span="8">{{ $t("campaign.members") }}</a-col>
                                    <a-col :span="16">
                                        <CampaignMembers
                                            :members="allCampaign.campaign_users"
                                        />
                                    </a-col>
                                </a-row>

                                <a-row :gutter="16" class="mt-10">
                                    <a-col :span="8">{{ $t("campaign.progress") }}</a-col>
                                    <a-col :span="16">
                                        <CampaignProgress
                                            :campaign="allCampaign"
                                            @success="fetchCampaigns"
                                        />
                                    </a-col>
                                </a-row>

                                <a-row :gutter="16" class="mt-20">
                                    <a-col :span="8">
                                        {{ $t("campaign.last_actioner") }}
                                    </a-col>
                                    <a-col :span="16">
                                        {{
                                            allCampaign.last_actioner &&
                                            allCampaign.last_actioner.name
                                                ? allCampaign.last_actioner.name
                                                : "-"
                                        }}
                                    </a-col>
                                </a-row>

                                <a-row :gutter="16" class="mt-25">
                                    <a-col :span="8">
                                        {{ $t("campaign.started_on") }}
                                    </a-col>
                                    <a-col :span="16">
                                        {{
                                            allCampaign.started_on != undefined
                                                ? formatDateTime(allCampaign.started_on)
                                                : "-"
                                        }}
                                    </a-col>
                                </a-row>
                            </template>
                        </a-card-meta>
                        <template #actions>
                            <a-button
                                type="link"
                                :style="{
                                    color: getLeaActionColor(
                                        allCampaign.upcoming_lead_action
                                    ),
                                }"
                                @click="takeAction(allCampaign)"
                            >
                                <template #icon>
                                    <PlayCircleOutlined
                                        v-if="
                                            allCampaign.upcoming_lead_action ==
                                            'start_and_new_lead'
                                        "
                                    />
                                    <PlusCircleOutlined
                                        v-else-if="
                                            allCampaign.upcoming_lead_action == 'new_lead'
                                        "
                                    />
                                    <PlayCircleOutlined
                                        v-else-if="
                                            allCampaign.upcoming_lead_action == 'start'
                                        "
                                    />
                                    <SyncOutlined v-else />
                                </template>
                                {{ $t(`campaign.${allCampaign.upcoming_lead_action}`) }}
                            </a-button>
                            <a-button
                                type="link"
                                @click="stopCampaign(allCampaign.xid)"
                                danger
                            >
                                <template #icon>
                                    <StopOutlined />
                                </template>
                                {{ $t("common.stop") }}
                            </a-button>
                        </template>
                    </a-card>
                </a-col>
            </a-row>
        </div>
        <div v-else>
            <a-skeleton active />
        </div>
    </div>
</template>

<script>
import { onMounted, ref, createVNode } from "vue";
import {
    PlusCircleOutlined,
    StopOutlined,
    PlayCircleOutlined,
    SyncOutlined,
    ExclamationCircleOutlined,
} from "@ant-design/icons-vue";
import { Modal, notification } from "ant-design-vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import AdminPageHeader from "../../../common/layouts/AdminPageHeader.vue";
import CampaignMembers from "../campaigns/CampaignMembers.vue";
import CampaignProgress from "../campaigns/CampaignProgress.vue";
import CampaignAddButton from "../campaigns/AddButton.vue";
import common from "../../../common/composable/common";
import apiAdmin from "../../../common/composable/apiAdmin";

export default {
    components: {
        AdminPageHeader,
        CampaignMembers,
        CampaignProgress,
        CampaignAddButton,

        PlusCircleOutlined,
        StopOutlined,
        PlayCircleOutlined,
        SyncOutlined,
    },
    setup() {
        const { formatDateTime, permsArray } = common();
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const allCampaigns = ref(undefined);
        const { t } = useI18n();
        const campaignsUrl =
            "call-managers?fields=id,xid,name,reference_prefix,allow_reference_prefix,remaining_leads,total_leads,campaignUsers{id,xid,user_id,x_user_id,campaign_id,x_campaign_id},campaignUsers:user{id,xid,name,profile_image,profile_image_url},form_id,x_form_id,form{id,xid,name,form_fields},detail_fields,last_action_by,x_last_action_by,lastActioner{id,xid,name},completed_by,x_completed_by,completedBy{id,xid,name},started_on,completed_on,upcoming_lead_action";
        const router = useRouter();

        onMounted(() => {
            fetchCampaigns();
        });

        const fetchCampaigns = () => {
            const campaignsPromise = axiosAdmin.get(campaignsUrl);

            Promise.all([campaignsPromise]).then(([campaignsResponse]) => {
                allCampaigns.value = campaignsResponse.data;
            });
        };

        const campaignAdded = () => {
            axiosAdmin.get(campaignsUrl).then((response) => {
                allCampaigns.value = response.data;
            });
        };

        const getLeaActionColor = (actionType) => {
            if (actionType == "start_and_new_lead") {
                return "#faad14";
            } else if (actionType == "resume") {
                return "#52c41a";
            } else {
                return "";
            }
        };

        const stopCampaign = (campaignXId) => {
            Modal.confirm({
                title: t("campaign.stop_campaign") + "?",
                icon: createVNode(ExclamationCircleOutlined),
                content: t("campaign.are_you_want_to_stop_campaign"),
                centered: true,
                okText: t("common.yes"),
                okType: "danger",
                cancelText: t("common.no"),
                onOk() {
                    addEditRequestAdmin({
                        url: "campaigns/stop",
                        data: { x_campaign_id: campaignXId },
                        successMessage: t("campaign.campaign_stopped_successfully"),
                        success: (res) => {
                            fetchCampaigns();
                        },
                    });
                },
                onCancel() {},
            });
        };

        const takeAction = (campaign) => {
            Modal.confirm({
                title: t("common.are_you_sure") + "?",
                icon: createVNode(ExclamationCircleOutlined),
                content: t("campaign.are_you_want_to_create_new_lead"),
                centered: true,
                okText: t("common.yes"),
                okType: "danger",
                cancelText: t("common.no"),
                onOk() {
                    addEditRequestAdmin({
                        url: "campaigns/take-action",
                        data: { x_campaign_id: campaign.xid },
                        successMessage: t("campaign.new_lead_added"),
                        success: (res) => {
                            router.push({
                                name: "admin.call_manager.take_action",
                                params: { id: res.x_lead_id },
                            });
                        },
                    });
                },
                onCancel() {},
            });
        };

        return {
            permsArray,
            formatDateTime,
            allCampaigns,
            fetchCampaigns,

            campaignAdded,
            stopCampaign,
            getLeaActionColor,
            takeAction,
        };
    },
};
</script>
