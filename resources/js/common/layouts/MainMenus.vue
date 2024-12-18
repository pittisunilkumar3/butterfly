<template>
    <a-menu
        :theme="appSetting.left_sidebar_theme"
        :openKeys="openKeys"
        v-model:selectedKeys="selectedKeys"
        :mode="mode"
        @openChange="onOpenChange"
        :style="{ borderRight: 'none' }"
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

        <a-sub-menu
            v-if="permsArray.includes('users_view') || permsArray.includes('admin')"
            key="users"
        >
            <template #title>
                <span>
                    <UserOutlined />
                    <span>{{ $t("menu.users") }}</span>
                </span>
            </template>
            <a-menu-item
                v-if="permsArray.includes('users_view') || permsArray.includes('admin')"
                @click="
                    () => {
                        menuSelected();
                        $router.push({ name: 'admin.users.index' });
                    }
                "
                key="users"
            >
                {{ $t("menu.staff_members") }}
            </a-menu-item>
        </a-sub-menu>

        <a-sub-menu 
            key="communication"
        >
            <template #title>
                <span>
                    <MessageOutlined />
                    <span>{{ $t("menu.menu.communication") }}</span>
                </span>
            </template>
            <a-menu-item
                v-if="permsArray.includes('call_manager_view') || user.role.name === 'admin'"
                @click="
                    () => {
                        menuSelected();
                        $router.push({ name: 'admin.call_manager.index' });
                    }
                "
                key="call_manager"
            >
                <PhoneOutlined />
                <span>{{ $t("menu.menu.call_manager") }}</span>
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
                <span>{{ $t("conversations") }}</span>
            </a-menu-item>

            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({ name: 'admin.campaigns.index' });
                    }
                "
                key="campaigns"
            >
                <FundProjectionScreenOutlined />
                <span>{{ $t("menu.menu.campaigns") }}</span>
            </a-menu-item>

            <a-menu-item
                @click="
                    () => {
                        menuSelected();
                        $router.push({ name: 'admin.templates.index' });
                    }
                "
                key="templates"
            >
                <span>{{ $t("menu.menu.templates") }}</span>
            </a-menu-item>
        </a-sub-menu>

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
                    $router.push({ name: 'admin.settings.profile.index' });
                }
            "
            key="settings"
        >
            <SettingOutlined />
            <span>{{ $t("menu.settings") }}</span>
        </a-menu-item>

        <a-menu-item @click="logout" key="logout">
            <LogoutOutlined />
            <span>{{ $t("menu.logout") }}</span>
        </a-menu-item>
    </a-menu>
</template>

<script>
import { defineComponent, ref, computed, watch } from "vue";
import { Layout } from "ant-design-vue";
import { useStore } from "vuex";
import { useRoute } from "vue-router";
import {
    HomeOutlined,
    LogoutOutlined,
    UserOutlined,
    SettingOutlined,
    MessageOutlined,
    PhoneOutlined,
    FundProjectionScreenOutlined,
} from "@ant-design/icons-vue";
import { PerfectScrollbar } from "vue3-perfect-scrollbar";
import common from "../../common/composable/common";
const { Sider } = Layout;

export default defineComponent({
    props: ["collapsed"],
    components: {
        Sider,
        PerfectScrollbar,
        Layout,

        HomeOutlined,
        LogoutOutlined,
        UserOutlined,
        SettingOutlined,
        MessageOutlined,
        PhoneOutlined,
        FundProjectionScreenOutlined,
    },
    setup(props, { emit }) {
        const { appSetting, user, permsArray, appModules, cssSettings } = common();
        
        // Extensive debug logging
        console.warn('DEBUG: Full User Object:', JSON.stringify(user.value));
        console.warn('DEBUG: Permissions Array:', JSON.stringify(permsArray.value));
        console.warn('DEBUG: App Modules:', JSON.stringify(appModules.value));
        console.warn('DEBUG: User Role:', user.value?.role?.name);
        console.warn('DEBUG: Is Superadmin:', user.value?.is_superadmin);

        // Add a watcher to track changes
        watch(user, (newUser) => {
            console.warn('DEBUG: User Object Changed:', JSON.stringify(newUser));
        });

        watch(permsArray, (newPerms) => {
            console.warn('DEBUG: Permissions Changed:', JSON.stringify(newPerms));
        });

        const rootSubmenuKeys = ["dashboard", "users", "settings", "communication"];
        const store = useStore();
        const route = useRoute();

        const openKeys = ref([]);
        const selectedKeys = ref([]);
        const mode = ref("horizontal");

        onMounted(() => {
            setSelectedKeys(route);
        });

        const logout = () => {
            store.dispatch("auth/logout");
        };

        const menuSelected = () => {
            emit("menuSelected");
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

        const setSelectedKeys = (newVal) => {
            const menuKey =
                typeof newVal.meta.menuKey == "function"
                    ? newVal.meta.menuKey(newVal)
                    : newVal.meta.menuKey;

            if (newVal.meta.menuParent == "settings") {
                selectedKeys.value = ["settings"];
            } else if (newVal.meta.menuParent == "communication") {
                selectedKeys.value = ["communication"];
            } else {
                selectedKeys.value = [menuKey.replace("-", "_")];
            }

            if (cssSettings.value.headerMenuMode == "horizontal") {
                openKeys.value = [];
            } else {
                openKeys.value = [newVal.meta.menuParent];
            }
        };

        watch(route, (newVal, oldVal) => {
            setSelectedKeys(newVal);
        });

        return {
            mode,
            selectedKeys,
            openKeys,
            logout,
            innerWidth: window.innerWidth,

            onOpenChange,
            menuSelected,
            appSetting,
            user,
            permsArray,
            appModules,
        };
    },
});
</script>

<style lang="less"></style>
