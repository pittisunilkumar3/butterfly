<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.email_settings`)" class="p-0" />
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t("menu.settings") }}
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t("menu.email_settings") }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <a-row>
        <a-col :xs="24" :sm="24" :md="24" :lg="4" :xl="4" class="bg-setting-sidebar">
            <SettingSidebar />
        </a-col>
        <a-col :xs="24" :sm="24" :md="24" :lg="20" :xl="20">
            <a-row>
                <a-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                    <SendMailSetting
                        v-if="sendMailSettings && sendMailSettings.xid"
                        :sendMailSettings="sendMailSettings"
                    />
                </a-col>
            </a-row>
        </a-col>
    </a-row>
</template>
<script>
import { onMounted, ref } from "vue";
import { SaveOutlined } from "@ant-design/icons-vue";
import SettingSidebar from "../SettingSidebar.vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import SendMailSetting from "./SendMailSetting.vue";

export default {
    components: {
        SaveOutlined,
        SettingSidebar,
        AdminPageHeader,
        SendMailSetting,
    },
    setup() {
        const sendMailSettings = ref([]);

        onMounted(() => {
            axiosAdmin.get("settings/email").then((response) => {
                sendMailSettings.value = response.data.sendMailSettings;
            });
        });

        return {
            sendMailSettings,
        };
    },
};
</script>
