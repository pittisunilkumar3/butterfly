<template>
    <a-layout>
        <LeftSidebar />
        <a-layout style="margin-left: 240px">
            <AdminPageHeader>
                <template #header>
                    <a-page-header :title="$t(`menu.conversations`)" class="p-0" />
                </template>
                <template #breadcrumb>
                    <a-breadcrumb separator="-" style="font-size: 12px">
                        <a-breadcrumb-item>
                            <router-link :to="{ name: 'admin.dashboard.index' }">
                                {{ $t(`menu.dashboard`) }}
                            </router-link>
                        </a-breadcrumb-item>
                        <a-breadcrumb-item>
                            {{ $t(`menu.conversations`) }}
                        </a-breadcrumb-item>
                    </a-breadcrumb>
                </template>
            </AdminPageHeader>

            <div class="dashboard-page-content-container">
                <div v-if="allConversations != undefined">
                    <a-row v-if="allConversations.length == 0" :gutter="[15, 15]" class="mt-30 mb-20">
                        <a-col :span="24">
                            <a-empty
                                :image-style="{
                                    height: '250px',
                                }"
                            >
                                <template #description>
                                    <a-typography-text type="warning" strong>
                                        {{ $t("conversations.no_conversations") }}
                                    </a-typography-text>
                                </template>

                                <a-button type="primary" @click="startNewConversation">
                                    {{ $t("conversations.start_conversation") }}
                                </a-button>
                            </a-empty>
                        </a-col>
                    </a-row>
                    <a-row v-else :gutter="[15, 15]" class="mt-30 mb-20">
                        <a-col
                            v-for="conversation in allConversations"
                            :key="conversation.xid"
                            :xs="24"
                            :sm="24"
                            :md="12"
                            :lg="8"
                            :xl="8"
                        >
                            <a-card :title="conversation.name" hoverable>
                                <a-card-meta>
                                    <template #description>
                                        <a-row :gutter="16" class="mt-10">
                                            <a-col :span="8">{{ $t("conversations.participants") }}</a-col>
                                            <a-col :span="16">
                                                <a-avatar-group>
                                                    <a-avatar 
                                                        v-for="user in conversation.conversation_users" 
                                                        :key="user.xid"
                                                        :src="user.user.profile_image_url"
                                                    >
                                                        {{ user.user.name.charAt(0) }}
                                                    </a-avatar>
                                                </a-avatar-group>
                                            </a-col>
                                        </a-row>

                                        <a-row :gutter="16" class="mt-10">
                                            <a-col :span="8">{{ $t("conversations.started_on") }}</a-col>
                                            <a-col :span="16">
                                                {{ 
                                                    conversation.started_on != undefined 
                                                    ? formatDateTime(conversation.started_on) 
                                                    : "-" 
                                                }}
                                            </a-col>
                                        </a-row>
                                    </template>
                                </a-card-meta>
                                <template #actions>
                                    <a-button 
                                        type="link" 
                                        @click="archiveConversation(conversation.xid)"
                                        danger
                                    >
                                        <template #icon>
                                            <StopOutlined />
                                        </template>
                                        {{ $t("conversations.archive") }}
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
        </a-layout>
    </a-layout>
</template>

<script>
import { onMounted, ref, createVNode } from "vue";
import {
    StopOutlined,
    ExclamationCircleOutlined,
} from "@ant-design/icons-vue";
import { Modal, notification } from "ant-design-vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import LeftSidebar from "../../../common/layouts/LeftSidebar.vue";
import AdminPageHeader from "../../../common/layouts/AdminPageHeader.vue";
import common from "../../../common/composable/common";
import apiAdmin from "../../../common/composable/apiAdmin";

export default {
    components: {
        LeftSidebar,
        AdminPageHeader,
        StopOutlined,
    },
    setup() {
        const { formatDateTime, permsArray } = common();
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const allConversations = ref(undefined);
        const { t } = useI18n();
        const router = useRouter();

        const conversationsUrl = "conversations?fields=id,xid,name,conversation_users{id,xid,user{id,xid,name,profile_image_url}},started_on";

        onMounted(() => {
            fetchConversations();
        });

        const fetchConversations = async () => {
            try {
                const response = await addEditRequestAdmin({
                    url: conversationsUrl,
                    method: 'get'
                });

                if (response.success) {
                    allConversations.value = response.data;
                } else {
                    notification.error({
                        message: t('common.error'),
                        description: response.message
                    });
                }
            } catch (error) {
                notification.error({
                    message: t('common.error'),
                    description: error.message
                });
            }
        };

        const startNewConversation = () => {
            router.push({ name: 'admin.conversations.create' });
        };

        const archiveConversation = async (conversationId) => {
            Modal.confirm({
                title: t('conversations.archive_confirm'),
                icon: createVNode(ExclamationCircleOutlined),
                content: t('conversations.archive_confirm_message'),
                async onOk() {
                    try {
                        const response = await addEditRequestAdmin({
                            url: `conversations/${conversationId}/archive`,
                            method: 'post'
                        });

                        if (response.success) {
                            notification.success({
                                message: t('common.success'),
                                description: t('conversations.archived_success')
                            });
                            fetchConversations();
                        } else {
                            notification.error({
                                message: t('common.error'),
                                description: response.message
                            });
                        }
                    } catch (error) {
                        notification.error({
                            message: t('common.error'),
                            description: error.message
                        });
                    }
                },
            });
        };

        return {
            formatDateTime,
            permsArray,
            allConversations,
            fetchConversations,
            startNewConversation,
            archiveConversation
        };
    }
};
</script>
