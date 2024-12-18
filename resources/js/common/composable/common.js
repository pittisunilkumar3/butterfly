import { computed } from "vue";
import moment from "moment";
import { useStore } from "vuex";
import { useI18n } from "vue-i18n";
import { forEach, includes } from "lodash-es";
import { checkUserPermission } from "../scripts/functions";
import dayjs from 'dayjs';

moment.suppressDeprecationWarnings = true;
import utc from "dayjs/plugin/utc";
import timezone from "dayjs/plugin/timezone";
dayjs.extend(utc);
dayjs.extend(timezone);

const common = () => {
    const store = useStore();
    const { t } = useI18n();
    const appType = window.config.app_type;
    const downloadLangCsvUrl = window.config.download_lang_csv_url;
    const menuCollapsed = computed(() => store.state.auth.menuCollapsed);
    const cssSettings = computed(() => store.state.auth.cssSettings);
    const appModules = computed(() => store.state.auth.activeModules);
    const visibleSubscriptionModules = computed(() => store.state.auth.visibleSubscriptionModules);
    const globalSetting = computed(() => store.state.auth.globalSetting);
    const appSetting = computed(() => store.state.auth.appSetting);
    const addMenus = computed(() => store.state.auth.addMenus);
    const selectedLang = computed(() => store.state.auth.lang);

    const user = computed(() => {
        const userObj = store.state.auth.user;
        console.warn('DEBUG: User object in common composable:', JSON.stringify(userObj));
        return userObj;
    });

    const statusColors = {
        enabled: "success",
        disabled: "error",
    };

    const userStatus = [
        {
            key: "enabled",
            value: t("common.enabled")
        },
        {
            key: "disabled",
            value: t("common.disabled")
        }
    ];

    const disabledDate = (current) => {
        // Can not select days before today and today
        return current && current > moment().endOf("day");
    };

    const dayjsObject = (date) => {
        if (date == undefined) {
            return dayjs()
                .tz(appSetting.value.timezone);
        } else {
            return dayjs(date)
                .tz(appSetting.value.timezone);
        }
    }

    const formatDate = (date) => {
        if (date == undefined) {
            return dayjs()
                .tz(appSetting.value.timezone)
                .format(
                    `${appSetting.value.date_format}`
                )
        } else {
            return dayjs(date)
                .tz(appSetting.value.timezone)
                .format(
                    `${appSetting.value.date_format}`
                )
        }
    }

    const formatDateTime = (dateTime) => {
        if (dateTime == undefined) {
            return dayjs()
                .tz(appSetting.value.timezone)
                .format(
                    `${appSetting.value.date_format} ${appSetting.value.time_format}`
                )
        } else {
            return dayjs(dateTime)
                .tz(appSetting.value.timezone)
                .format(
                    `${appSetting.value.date_format} ${appSetting.value.time_format}`
                )
        }
    }

    const formatTimeDuration = (seconds) => {
        if (seconds == 0) {
            return 0;
        } else {
            var totalSeconds = parseInt(seconds);
            var hours = Math.floor(totalSeconds / 3600);
            totalSeconds %= 3600;
            var minutes = Math.floor(totalSeconds / 60);
            var remainingSeconds = totalSeconds % 60;

            var secondsString = "";
            if (hours > 0) {
                secondsString += `${hours} H,`;
            }
            if (minutes > 0) {
                secondsString += ` ${minutes} M,`;
            }
            if (remainingSeconds > 0) {
                secondsString = secondsString.trim() + ` ${remainingSeconds} S`;
            }

            return secondsString.trim();
        }
    }

    const formatAmount = (amount) => {
        return parseFloat(parseFloat(amount).toFixed(2));
    };

    const formatAmountCurrency = (amount) => {
        const newAmount = parseFloat(Math.abs(amount)).toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");

        if (appSetting.value.currency.position == "front") {
            var newAmountString = `${appSetting.value.currency.symbol}${newAmount}`;
        } else {
            var newAmountString = `${newAmount}${appSetting.value.currency.symbol}`;
        }

        return amount < 0 ? `- ${newAmountString}` : newAmountString;
    };

    const formatAmountUsingCurrencyObject = (amount, currency) => {
        const newAmount = parseFloat(Math.abs(amount)).toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");

        if (currency.position == "front") {
            var newAmountString = `${currency.symbol}${newAmount}`;
        } else {
            var newAmountString = `${newAmount}${currency.symbol}`;
        }

        return amount < 0 ? `- ${newAmountString}` : newAmountString;
    };

    const calculateFilterString = (filters) => {
        var filterString = "";

        forEach(filters, (filterValue, filterKey) => {
            if (filterValue != undefined) {
                filterString += `${filterKey} eq "${filterValue}" and `;
            }
        })

        if (filterString.length > 0) {
            filterString = filterString.substring(0, filterString.length - 4);
        }

        return filterString;
    }

    const checkPermission = (permissionName) => checkUserPermission(permissionName, user.value);

    const updatePageTitle = (pageName) => {
        store.commit("auth/updatePageTitle", t(`menu.${pageName}`));
    }

    const permsArray = computed(() => {
        const permsArrayList = user && user.value && user.value.role ? [user.value.role.name] : [];

        if (user && user.value && user.value.role) {
            forEach(user.value.role.perms, (permission) => {
                permsArrayList.push(permission.name);
            });
        }

        return permsArrayList;
    });

    const slugify = (text) => {
        return text
            .toString()
            .toLowerCase()
            .replace(/\s+/g, "-") // Replace spaces with -
            .replace(/[^\w\-]+/g, "") // Remove all non-word chars
            .replace(/\-\-+/g, "-") // Replace multiple - with single -
            .replace(/^-+/, "") // Trim - from start of text
            .replace(/-+$/, ""); // Trim - from end of text
    };

    const convertStringToKey = (textString) => {
        return textString
            .toString()
            .toLowerCase()
            .replace(/\s+/g, "_") // Replace spaces with -
            .replace(/[^\w\-]+/g, "") // Remove all non-word chars
            .replace(/\-\-+/g, "_") // Replace multiple - with single -
            .replace(/^-+/, "") // Trim - from start of text
            .replace(/-+$/, ""); // Trim - from end of text
    }

    const convertToPositive = (amount) => {
        return amount < 0 ? amount * -1 : amount;
    }

    const willSubscriptionModuleVisible = (moduleName) => {
        if (appType == 'non-saas') {
            return true;
        } else {
            if (appSetting.value.subscription_plan && appSetting.value.subscription_plan.modules) {
                return includes(appSetting.value.subscription_plan.modules, moduleName);
            } else {
                return false;
            }
        }
    }

    const getCampaignUrl = (campaignStatus = 'active', viewType = 'self') => {
        var campaignsUrl = `call-managers?fields=id,xid,name,status,form_id,x_form_id,form{id,xid,name,form_fields}&campaign_status=${campaignStatus}&view_type=${viewType}&limit=10000`;

        return campaignsUrl;
    }

    const getCampaignStatsUrl = (campaignStatus = 'active', campaignId = undefined) => {
        var campaignStatsUrl = `leads/campaign-stats?campaign_status=${campaignStatus}`;

        if (campaignId != undefined) {
            campaignStatsUrl += `&campaign_id=${campaignId}`;
        }

        return campaignStatsUrl;
    }

    return {
        menuCollapsed,
        appSetting,
        appType,
        addMenus,
        selectedLang,
        user,
        checkPermission,
        permsArray,
        statusColors,
        userStatus,

        disabledDate,
        formatAmount,
        formatAmountCurrency,
        formatAmountUsingCurrencyObject,
        convertToPositive,

        calculateFilterString,
        updatePageTitle,

        downloadLangCsvUrl,
        appModules,
        dayjs,
        formatDate,
        formatDateTime,
        dayjsObject,
        slugify,
        convertStringToKey,

        cssSettings,
        globalSetting,

        willSubscriptionModuleVisible,
        visibleSubscriptionModules,
        formatTimeDuration,

        getCampaignUrl,
        getCampaignStatsUrl,
    };
}

export default common;
