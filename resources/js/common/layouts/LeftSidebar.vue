<template>
    <a-layout-sider
        :width="240"
        :style="{
            margin: '0 0 0 0',
            overflowY: 'auto',
            position: 'fixed',
            paddingTop: '8px',
            zIndex: 998,
            backgroundColor: 'white',
        }"
        :trigger="null"
        :collapsed="menuCollapsed"
        theme="light"
        class="sidebar-right-border"
    >
        <div v-if="menuCollapsed" class="logo">
            <img
                :style="{
                    height: '32px',
                }"
                :src="appSetting.light_logo_url" 
            />
        </div>
        <div v-else>
            <img
                :style="{
                    width: '150px',
                    height: '53px',
                    paddingLeft: appSetting.rtl ? '0px' : '30px',
                    paddingRight: appSetting.rtl ? '30px' : '0px',
                    paddingTop: '5px',
                    paddingBottom: '20px',
                    marginLeft: appSetting.rtl ? '0px' : '10px',
                    marginRight: appSetting.rtl ? '10px' : '0px',
                }"
                :src="appSetting.light_logo_url" 
            />
            <CloseOutlined
                v-if="innerWidth <= 991"
                :style="{
                    marginLeft: appSetting.rtl ? '0px' : '45px',
                    marginRight: appSetting.rtl ? '45px' : '0px',
                    verticalAlign: 'super',
                    color: appSetting.left_sidebar_theme == 'dark' ? '#fff' : '#000000',
                }"
                @click="menuSelected"
            />
        </div>

        <div class="main-sidebar">
            <perfect-scrollbar
                :options="{
                    wheelSpeed: 1,
                    swipeEasing: true,
                    suppressScrollX: true,
                }"
            >
                <a-menu
                    theme="light"
                    :openKeys="openKeys"
                    v-model:selectedKeys="selectedKeys"
                    :mode="mode"
                    @openChange="onOpenChange"
                    :style="{ borderRight: 'none', backgroundColor: 'white' }"
                >
                    <a-menu-item
                        @click="
                            () => {
                                menuSelected();
                                $router.push({ name: 'admin.dashboard.index' });
                            }
                        "
                        key="dashboard"
                    >
                        <HomeOutlined />
                        <span>{{ $t("menu.dashboard") }}</span>
                    </a-menu-item>

                    <a-menu-item
                        v-if="
                            (permsArray.includes('products_view') ||
                                permsArray.includes('admin')) &&
                            willSubscriptionModuleVisible('product')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({ name: 'admin.products.index' });
                            }
                        "
                        key="products"
                    >
                        <CopyrightCircleOutlined />
                        <span>{{ $t("menu.products") }}</span>
                    </a-menu-item>
                    <a-sub-menu
                        key="expense_manager"
                        v-if="
                            (permsArray.includes('expense_categories_view') ||
                                permsArray.includes('expenses_view') ||
                                permsArray.includes('admin')) &&
                            willSubscriptionModuleVisible('expense')
                        "
                    >
                        <template #title>
                            <span>
                                <WalletOutlined />
                                <span>{{ $t("menu.expense_manager") }}</span>
                            </span>
                        </template>
                        <a-menu-item
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.expense_categories.index',
                                    });
                                }
                            "
                            key="expense_categories"
                            v-if="
                                permsArray.includes('expense_categories_view') ||
                                permsArray.includes('admin')
                            "
                        >
                            {{ $t("menu.expense_categories") }}
                        </a-menu-item>
                        <a-menu-item
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({ name: 'admin.expenses.index' });
                                }
                            "
                            key="expenses"
                            v-if="
                                permsArray.includes('expenses_view') ||
                                permsArray.includes('admin')
                            "
                        >
                            {{ $t("menu.expenses") }}
                        </a-menu-item>
                    </a-sub-menu>

                    <LeftSideBarMainHeading
                        v-if="
                            permsArray.includes('users_view') ||
                            permsArray.includes('admin') ||
                            ((permsArray.includes('salesmans_view') ||
                                permsArray.includes('admin')) &&
                                willSubscriptionModuleVisible('salesman'))
                        "
                        :title="$t('menu.user_management')"
                        :visible="menuCollapsed"
                    />

                    <a-menu-item
                        v-if="
                            permsArray.includes('users_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({ name: 'admin.users.index' });
                            }
                        "
                        key="users"
                    >
                        <SolutionOutlined />
                        <span>{{ $t("menu.staff_members") }}</span>
                    </a-menu-item>

                    <a-sub-menu
                        v-if="
                            (permsArray.includes('salesmans_view') ||
                                permsArray.includes('admin')) &&
                            willSubscriptionModuleVisible('salesman')
                        "
                        key="salesmans"
                    >
                        <template #title>
                            <span>
                                <ApartmentOutlined />
                                <span>{{ $t("menu.salesmans") }}</span>
                            </span>
                        </template>
                        <a-menu-item
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({ name: 'admin.salesman.index' });
                                }
                            "
                            key="salesmans"
                        >
                            <TeamOutlined />
                            <span>{{ $t("menu.salesmans") }}</span>
                        </a-menu-item>
                        <a-menu-item
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({
                                        name: 'admin.bookings.salesman_bookings.index',
                                    });
                                }
                            "
                            key="salesman_bookings"
                        >
                            <ShoppingCartOutlined />
                            <span>{{ $t("menu.salesman_bookings") }}</span>
                        </a-menu-item>
                    </a-sub-menu>

                    <LeftSideBarMainHeading
                        :title="$t('menu.lead_management')"
                        :visible="menuCollapsed"
                    />

                    <a-menu-item
                        @click="
                            () => {
                                menuSelected();
                                $router.push({ name: 'admin.call_manager.index' });
                            }
                        "
                        key="call_manager"
                    >
                        <AppstoreOutlined />
                        <span>{{ $t("menu.call_manager") }}</span>
                    </a-menu-item>

                    <a-menu-item
                        v-if="
                            permsArray.includes('campaigns_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({ name: 'admin.campaigns.index' });
                            }
                        "
                        key="campaigns"
                    >
                        <CopyrightCircleOutlined />
                        <span>{{ $t("menu.campaigns") }}</span>
                    </a-menu-item>

                    <a-sub-menu key="leads">
                        <template #title>
                            <span>
                                <ApartmentOutlined />
                                <span>{{ $t("menu.leads_calls") }}</span>
                            </span>
                        </template>
                        <a-menu-item
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({ name: 'admin.leads.index' });
                                }
                            "
                            key="leads"
                        >
                            <SoundOutlined />
                            <span>{{ $t("menu.leads") }}</span>
                        </a-menu-item>
                        <a-menu-item
                            v-if="willSubscriptionModuleVisible('lead_call_log')"
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({ name: 'admin.call_logs.index' });
                                }
                            "
                            key="call_logs"
                        >
                            <PhoneOutlined />
                            <span>{{ $t("menu.call_logs") }}</span>
                        </a-menu-item>
                        <a-menu-item
                            v-if="willSubscriptionModuleVisible('lead_notes')"
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({ name: 'admin.lead_notes.index' });
                                }
                            "
                            key="lead_notes"
                        >
                            <FileTextOutlined />
                            <span>{{ $t("menu.lead_notes") }}</span>
                        </a-menu-item>
                    </a-sub-menu>
                    <a-menu-item
                        v-if="willSubscriptionModuleVisible('lead_follow_up')"
                        @click="
                            () => {
                                menuSelected();
                                $router.push({
                                    name: 'admin.bookings.lead_follow_up.index',
                                });
                            }
                        "
                        key="lead_follow_up"
                    >
                        <ScheduleOutlined />
                        <span>{{ $t("menu.lead_follow_up") }}</span>
                    </a-menu-item>

                    <LeftSideBarMainHeading
                        :title="$t('menu.settings')"
                        :visible="menuCollapsed"
                    />

                    <a-menu-item
                        v-if="
                            permsArray.includes('form_field_names_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({
                                    name: 'admin.form_field_names.index',
                                });
                            }
                        "
                        key="form_field_names"
                    >
                        <InsertRowBelowOutlined />
                        <span>{{ $t("menu.form_field_names") }}</span>
                    </a-menu-item>

                    <a-sub-menu
                        key="messaging"
                        v-if="
                            permsArray.includes('email_templates_view') ||
                            permsArray.includes('admin')
                        "
                    >
                        <template #title>
                            <span>
                                <MailOutlined />
                                <span>{{ $t("menu.messaging") }}</span>
                            </span>
                        </template>
                        <a-menu-item
                            v-if="
                                permsArray.includes('email_templates_view') ||
                                permsArray.includes('admin')
                            "
                            @click="
                                () => {
                                    menuSelected();
                                    $router.push({ name: 'admin.email_templates.index' });
                                }
                            "
                            key="email_templates"
                        >
                            <MailOutlined />
                            <span>{{ $t("menu.email_templates") }}</span>
                        </a-menu-item>
                    </a-sub-menu>

                    <a-menu-item
                        v-if="
                            permsArray.includes('forms_view') ||
                            permsArray.includes('admin')
                        "
                        @click="
                            () => {
                                menuSelected();
                                $router.push({ name: 'admin.forms.index' });
                            }
                        "
                        key="forms"
                    >
                        <FolderOpenOutlined />
                        <span>{{ $t("menu.forms") }}</span>
                    </a-menu-item>

                    <a-menu-item
                        key="analyze"
                        @click="
                            () => {
                                menuSelected();
                                $router.push({ name: 'admin.analyze.index' });
                            }
                        "
                    >
                        <BarChartOutlined />
                        <span>{{ $t("menu.analytics") }}</span>
                    </a-menu-item>

                    <a-menu-item
                        key="conversations"
                        @click="
                            () => {
                                menuSelected();
                                $router.push({ name: 'admin.conversations.index' });
                            }
                        "
                    >
                        <MessageOutlined />
                        <span>{{ $t("menu.conversations") }}</span>
                    </a-menu-item>

                    <component
                        v-for="(appModule, index) in appModules"
                        :key="index"
                        v-bind:is="appModule + 'Menu'"
                        @menuSelected="menuSelected"
                    />

                    <a-menu-item
                        @click="
                            () => {
                                menuSelected();
                                $router.push({
                                    name: 'admin.settings.profile.index',
                                });
                            }
                        "
                        key="settings"
                    >
                        <SettingOutlined />
                        <span>{{ $t("menu.settings") }}</span>
                    </a-menu-item>

                    <a-menu-item
                        v-if="appType == 'saas' && appSetting.x_admin_id == user.xid"
                        @click="
                            () => {
                                menuSelected();
                                $router.push({
                                    name: 'admin.subscription.current_plan',
                                });
                            }
                        "
                        key="subscription"
                    >
                        <DollarCircleOutlined />
                        <span>{{ $t("menu.subscription") }}</span>
                    </a-menu-item>

                    <a-menu-item @click="logout" key="logout">
                        <LogoutOutlined />
                        <span>{{ $t("menu.logout") }}</span>
                    </a-menu-item>
                </a-menu>
            </perfect-scrollbar>
        </div>
    </a-layout-sider>
</template>

<script>
import { defineComponent, ref, watch, onMounted, computed } from "vue";
import { Layout } from "ant-design-vue";
import { useStore } from "vuex";
import { useRoute } from "vue-router";
import {
    HomeOutlined,
    LogoutOutlined,
    UserOutlined,
    SettingOutlined,
    CloseOutlined,
    ShoppingOutlined,
    ShoppingCartOutlined,
    AppstoreOutlined,
    ShopOutlined,
    CalculatorOutlined,
    TeamOutlined,
    WalletOutlined,
    BankOutlined,
    RocketOutlined,
    LaptopOutlined,
    CarOutlined,
    DollarCircleOutlined,
    CopyrightCircleOutlined,
    IeOutlined,
    PhoneOutlined,
    FolderOpenOutlined,
    FileTextOutlined,
    SoundOutlined,
    ApartmentOutlined,
    ScheduleOutlined,
    SolutionOutlined,
    MailOutlined,
    FormOutlined,
    InsertRowBelowOutlined,
    MessageOutlined,
    BarChartOutlined, // Ensure BarChartOutlined is imported
} from "@ant-design/icons-vue";
import { PerfectScrollbar } from "vue3-perfect-scrollbar";
import common from "../../common/composable/common";
import LeftSideBarMainHeading from "../components/common/typography/LeftSideBarMainHeading.vue";
const { Sider } = Layout;

export default defineComponent({
    components: {
        Sider,
        PerfectScrollbar,
        Layout,
        LeftSideBarMainHeading,

        HomeOutlined,
        LogoutOutlined,
        UserOutlined,
        SettingOutlined,
        CloseOutlined,
        ShoppingOutlined,
        ShoppingCartOutlined,
        AppstoreOutlined,
        ShopOutlined,
        CalculatorOutlined,
        TeamOutlined,
        WalletOutlined,
        BankOutlined,
        RocketOutlined,
        LaptopOutlined,
        CarOutlined,
        DollarCircleOutlined,
        CopyrightCircleOutlined,
        IeOutlined,
        FolderOpenOutlined,
        FileTextOutlined,
        SoundOutlined,
        PhoneOutlined,
        ApartmentOutlined,
        ScheduleOutlined,
        SolutionOutlined,
        MailOutlined,
        FormOutlined,
        InsertRowBelowOutlined,
        MessageOutlined,
        BarChartOutlined, // Ensure BarChartOutlined is imported
    },
    setup(props, { emit }) {
        const {
            appSetting,
            appType,
            user,
            permsArray,
            appModules,
            menuCollapsed,
            willSubscriptionModuleVisible,
        } = common();
        const rootSubmenuKeys = [
            "dashboard",
            "leads",
            "users",
            "salesmans",
            "forms",
            "form_field_names",
            "settings",
            "subscription",
        ];
        const store = useStore();
        const route = useRoute();

        const innerWidth = window.innerWidth;
        const openKeys = ref([]);
        const selectedKeys = ref([]);
        const mode = ref("inline");

        onMounted(() => {
            var menuKey =
                typeof route.meta.menuKey == "function"
                    ? route.meta.menuKey(route)
                    : route.meta.menuKey;

            if (route.meta.menuParent == "settings") {
                menuKey = "settings";
            }

            if (route.meta.menuParent == "subscription") {
                menuKey = "subscription";
            }

            if (innerWidth <= 991) {
                openKeys.value = [];
            } else {
                openKeys.value = menuCollapsed.value ? [] : [route.meta.menuParent];
            }

            selectedKeys.value = [menuKey.replace("-", "_")];
        });

        const logout = () => {
            store.dispatch("auth/logout");
        };

        const menuSelected = () => {
            if (innerWidth <= 991) {
                store.commit("auth/updateMenuCollapsed", true);
            }
        };

        const onOpenChange = (currentOpenKeys) => {
            const latestOpenKey = currentOpenKeys.find(
                (key) => openKeys.value.indexOf(key) === -1
            );

            if (rootSubmenuKeys.indexOf(latestOpenKey) === -1) {
                openKeys.value = currentOpenKeys;
            } else {
                openKeys.value = latestOpenKey ? [latestOpenKey] : [];
            }
        };

        watch(route, (newVal, oldVal) => {
            const menuKey =
                typeof newVal.meta.menuKey == "function"
                    ? newVal.meta.menuKey(newVal)
                    : newVal.meta.menuKey;

            if (innerWidth <= 991) {
                openKeys.value = [];
            } else {
                openKeys.value = [newVal.meta.menuParent];
            }

            if (newVal.meta.menuParent == "settings") {
                selectedKeys.value = ["settings"];
            } else if (newVal.meta.menuParent == "subscription") {
                selectedKeys.value = ["subscription"];
            } else {
                selectedKeys.value = [menuKey.replace("-", "_")];
            }
        });

        watch(
            () => menuCollapsed.value,
            (newVal, oldVal) => {
                const menuKey =
                    typeof route.meta.menuKey == "function"
                        ? route.meta.menuKey(route)
                        : route.meta.menuKey;

                if (innerWidth <= 991 && menuCollapsed.value) {
                    openKeys.value = [];
                } else {
                    openKeys.value = menuCollapsed.value ? [] : [route.meta.menuParent];
                }

                if (route.meta.menuParent == "settings") {
                    selectedKeys.value = ["settings"];
                } else if (route.meta.menuParent == "subscription") {
                    selectedKeys.value = ["subscription"];
                } else {
                    selectedKeys.value = [menuKey.replace("-", "_")];
                }
            }
        );

        return {
            mode,
            selectedKeys,
            openKeys,
            logout,
            innerWidth: window.innerWidth,

            onOpenChange,
            menuSelected,
            menuCollapsed,
            appSetting,
            appType,
            user,
            permsArray,
            appModules,
            willSubscriptionModuleVisible,
        };
    },
});
</script>

<style lang="less">
.main-sidebar .ps {
    height: calc(100vh - 62px);
}

@media only screen and (max-width: 1150px) {
    .ant-layout-sider.ant-layout-sider-collapsed {
        left: -80px !important;
    }
}
</style>
