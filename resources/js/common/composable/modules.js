import { ref } from "vue";
import axios from "axios";
import { forEach, find, includes, remove } from "lodash-es";
import { notification, message } from "ant-design-vue";
import { useI18n } from "vue-i18n";
import { useStore } from "vuex";
import { getUrlByAppType } from "../scripts/functions";

const modules = () => {
    const allModules = ref([]);
    const rules = ref({});
    const purchaseCode = ref("");
    const errorMessage = ref("");
    const successMessage = ref("");
    const loading = ref(false);
    const { t } = useI18n();
    const productName = ref(window.config.product_name);
    const version = ref(window.config.product_version);
    const store = useStore();
    const offers = ref([]);
    const settings = ref([]);
    const downloading = ref(false);
    const extracting = ref("");
    const downloadPercentage = ref(0);

    const getModuleData = () => {
        const mainProductName = window.config.product_name;
        const modulesPromise = axiosAdmin.get(getUrlByAppType("modules"));
        var allModulesData = [];

        modulesPromise.then((modulesResponse) => {
            const installedModules = window.config.installed_modules;
            const enabledModules = window.config.modules;

            allModules.value = allModulesData;
        });
    };

    const verifyPurchase = (configObject) => {
        const { success } = configObject;
        store.commit('auth/updateActiveModules', window.config.modules);
        success({ status: "success" });
    }

    const install = (moduleName) => {
        downloading.value = true;
        downloadPercentage.value = 0;
        extracting.value = "";
        
        axiosAdmin
            .post(getUrlByAppType("modules/install"), { verified_name: moduleName })
            .then((response) => {
                downloading.value = false;
                downloadPercentage.value = 100;
                extracting.value = "completed";

                store.commit(
                    "auth/updateActiveModules",
                    response.data.enabled_modules
                );

                window.config.modules = response.data.enabled_modules;
                window.config.installed_modules = response.data.installed_modules;
            })
            .catch((error) => {
                downloading.value = false;
                downloadPercentage.value = 0;
            });
    }

    return {
        allModules,
        getModuleData,
        install,

        verifyPurchase,
        rules,
        purchaseCode,
        errorMessage,
        successMessage,
        loading,
        productName,
        version,

        offers,
        settings,
        downloading,
        downloadPercentage,
        extracting,
    };
}

export default modules;
