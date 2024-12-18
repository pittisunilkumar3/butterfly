<template>
    <a-layout class="layout">
        <a-layout-header>
            <a-row>
                <a-col :span="4">
                    <img
                        v-if="appDetails && appDetails.app"
                        :style="{
                            height: '32px',
                        }"
                        :src="
                            appDetails.app.left_sidebar_theme == 'dark'
                                ? appDetails.app.dark_logo_url
                                : appDetails.app.light_logo_url
                        "
                    />
                </a-col>
                <a-col :span="20" :style="{ textAlign: 'right' }">
                    <a-button
                        type="link"
                        @click="logout"
                        class="p-0"
                        :style="{ color: 'white' }"
                    >
                        {{ $t("menu.logout") }}
                    </a-button>
                </a-col>
            </a-row>
        </a-layout-header>
        <a-layout-content style="padding: 0 50px">
            <a-card
                class="setup-page-content-container"
                :bodyStyle="{ padding: '0px' }"
                :style="{ height: '100vh', marginTop: '20px', background: 'white' }"
            >
                <a-row>
                    <a-col :span="21" :offset="1">
                        <a-alert
                            :message="$t('setup_company.setup_not_completed')"
                            :description="
                                $t('setup_company.setup_not_completed_description')
                            "
                            type="error"
                            show-icon
                            :style="{ marginTop: '40px' }"
                        />
                    </a-col>
                </a-row>
                <a-row :style="{ marginTop: '80px', background: 'white' }">
                    <a-col :span="21" :offset="1">
                        <a-steps :current="currentStep">
                            <a-step :status="currencyStepStatus">
                                <template #title>
                                    {{ $t("setup_company.currency") }}
                                </template>
                                <template #description>
                                    <span>{{
                                        $t("setup_company.add_first_currency")
                                    }}</span>
                                </template>
                            </a-step>
                            <a-step :status="companySettingStepStatus">
                                <template #title>
                                    {{ $t("setup_company.company_settings") }}
                                </template>
                                <template #description>
                                    <span>{{
                                        $t("setup_company.set_company_basic_settings")
                                    }}</span>
                                </template>
                            </a-step>
                        </a-steps>
                        <a-divider></a-divider>

                        <template
                            v-if="appDetails && appDetails.app && appDetails.app.name"
                        >
                            <template v-if="currentStep == 0">
                                <a-button type="primary" @click="addCurrency">
                                    <PlusOutlined />
                                    {{ $t("currency.add") }}
                                </a-button>

                                <CurrencyTable
                                    class="mb-40"
                                    ref="currencyTableRef"
                                    :showFilter="false"
                                    @addOrEditSuccess="currencyAddSuccess"
                                />
                            </template>
                            <template v-if="currentStep == 1">
                                <EditSetupSetting
                                    class="mb-40"
                                    ref="companySettingRef"
                                    :showSubmitButton="false"
                                    @updateSuccess="companySettingUpdated"
                                />
                            </template>
                        </template>
                    </a-col>
                </a-row>

                <a-row class="mt-10">
                    <a-col :span="20" :offset="1">
                        <div
                            :style="{
                                position: 'absolute',
                                right: 0,
                                bottom: 0,
                                width: '100%',
                                borderTop: '1px solid #e9e9e9',
                                padding: '10px 16px',
                                background: '#fff',
                                textAlign: 'left',
                                zIndex: 1,
                                marginBottom: '10px',
                            }"
                        >
                            <a-space>
                                <a-button
                                    v-if="currentStep >= 0 && currentStep != 0"
                                    type="primary"
                                    :style="{
                                        backgroundColor: '#faad14',
                                        border: '#faad14',
                                    }"
                                    @click="goBack"
                                >
                                    <DoubleLeftOutlined />
                                    {{ $t("setup_company.previous_step") }}
                                </a-button>
                                <a-button
                                    v-if="currentStep < 1"
                                    type="primary"
                                    @click="goNext"
                                    :disabled="currentStep >= correctStep"
                                >
                                    {{ $t("setup_company.next_step") }}
                                    <DoubleRightOutlined />
                                </a-button>
                                <a-button
                                    v-if="currentStep == 1 && companySettingRef"
                                    type="primary"
                                    :style="{
                                        backgroundColor: '#52c41a',
                                        border: '#52c41a',
                                    }"
                                    @click="submitCompanySetting"
                                    :loading="companySettingRef.loading"
                                >
                                    <SaveOutlined />
                                    {{ $t("setup_company.save_finish_setup") }}
                                </a-button>
                            </a-space>
                        </div>
                    </a-col>
                </a-row>
            </a-card>

            <a-modal
                v-model:open="showFinalModal"
                :maskClosable="false"
                :closable="false"
                :footer="null"
                centered
            >
                <a-result>
                    <template #title>
                        <span v-if="setupCompleted" :style="{ color: '#7676e3' }">
                            {{ $t("setup_company.setup_complete_message") }}
                        </span>
                        <span v-else :style="{ color: '#7676e3' }">
                            {{ $t("setup_company.setup_running_message") }}
                        </span>
                    </template>
                    <template #icon>
                        <CheckCircleOutlined v-if="setupCompleted" />
                        <InfoCircleOutlined v-else />
                    </template>
                    <template #extra>
                        <a-button
                            v-if="setupCompleted"
                            type="primary"
                            :style="{ backgroundColor: '#52c41a', border: '#52c41a' }"
                            @click="goToDashboard"
                        >
                            <HomeOutlined />
                            {{ $t("setup_company.go_to_dashboard") }}
                        </a-button>
                        <SyncOutlined
                            v-else
                            :style="{ fontSize: '38px', color: '#5254cf' }"
                            spin
                        />
                    </template>
                </a-result>
            </a-modal>
        </a-layout-content>
    </a-layout>
</template>

<script>
import { defineComponent, ref, onMounted, reactive, toRefs } from "vue";
import {
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    DoubleRightOutlined,
    DoubleLeftOutlined,
    SaveOutlined,
    SyncOutlined,
    CheckCircleOutlined,
    HomeOutlined,
    InfoCircleOutlined,
} from "@ant-design/icons-vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";
import common from "../../common/composable/common";
import CurrencyTable from "./common/settings/currency/CurrencyTable.vue";
import EditSetupSetting from "../views/settings/company/EditSetupSetting.vue";

export default defineComponent({
    components: {
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        DoubleRightOutlined,
        DoubleLeftOutlined,
        SaveOutlined,
        SyncOutlined,
        CheckCircleOutlined,
        HomeOutlined,
        InfoCircleOutlined,

        CurrencyTable,
        EditSetupSetting,
    },
    setup() {
        const { permsArray, appSetting } = common();
        const router = useRouter();
        const store = useStore();
        const warhouseTableRef = ref(null);
        const currencyTableRef = ref(null);
        const companySettingRef = ref(null);
        const appDetails = ref([]);
        const currentStep = ref(0);
        const correctStep = ref(0);
        const stepStatus = reactive({
            currencyStepStatus: "wait",
            companySettingStepStatus: "wait",
        });
        const showFinalModal = ref(false);
        const setupCompleted = ref(false);

        onMounted(() => {
            checkStepNumber();
        });

        const checkStepNumber = () => {
            axiosAdmin.get("app").then((appResponse) => {
                appDetails.value = appResponse.data;

                // For Setting Status
                stepStatus.currencyStepStatus =
                    appDetails.value.total_currencies > 0 ? "finish" : "wait";
                stepStatus.companySettingStepStatus =
                    companySettingNotSetup() == false ? "finish" : "wait";

                // For Setting Step
                if (appDetails.value.total_currencies == 0) {
                    currentStep.value = 0;
                    correctStep.value = 0;
                    stepStatus.currencyStepStatus = "error";
                } else if (companySettingNotSetup()) {
                    currentStep.value = 1;
                    correctStep.value = 1;
                    stepStatus.companySettingStepStatus = "error";
                } else {
                    currentStep.value = 1;
                    correctStep.value = 1;

                    setupCompleted.value = true;
                    showFinalModal.value = true;
                }
            });
        };

        const companySettingNotSetup = () => {
            return appDetails.value.app.x_currency_id == null ? true : false;
        };

        const addCurrency = () => {
            currencyTableRef.value.addItem();
        };

        const currencyAddSuccess = (details) => {
            checkStepNumber();
        };

        const submitCompanySetting = () => {
            companySettingRef.value.onSubmit();
        };

        const companySettingUpdated = () => {
            showFinalModal.value = true;

            axiosAdmin
                .post(`/user`)
                .then(function (response) {
                    store.commit("auth/updateUser", response.data.user);

                    setupCompleted.value = true;
                })
                .catch(function (error) {});
        };

        const goToDashboard = () => {
            showFinalModal.value = false;

            router.push({
                name: "admin.dashboard.index",
            });
        };

        const goBack = () => {
            currentStep.value = currentStep.value == 0 ? 0 : currentStep.value - 1;
        };

        const goNext = () => {
            currentStep.value = currentStep.value >= 1 ? 1 : currentStep.value + 1;
        };

        const logout = () => {
            store.dispatch("auth/logout");
        };

        return {
            appDetails,
            currentStep,
            correctStep,
            ...toRefs(stepStatus),

            warhouseTableRef,
            currencyTableRef,
            addCurrency,
            currencyAddSuccess,
            companySettingRef,
            submitCompanySetting,
            companySettingUpdated,
            showFinalModal,
            setupCompleted,
            goToDashboard,

            goBack,
            goNext,

            logout,
        };
    },
});
</script>

<style></style>
