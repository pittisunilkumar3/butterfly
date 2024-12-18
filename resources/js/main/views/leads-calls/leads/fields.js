import { ref, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { forEach } from "lodash-es";
import common from "../../../../common/composable/common";

const fields = () => {
    const { convertStringToKey, getCampaignUrl, getCampaignStatsUrl } = common();
    const url = "leads?fields=id,xid,reference_number,lead_data,started,campaign_id,x_campaign_id,campaign{id,xid,name,status},time_taken,first_action_by,x_first_action_by,firstActioner{id,xid,name},last_action_by,x_last_action_by,lastActioner{id,xid,name}";
    const addEditUrl = "leads";
    const hashableColumns = ['campaign_id'];
    const { t } = useI18n();
    const allFormFieldNames = ref([]);
    const allCampaigns = ref([]);
    const formFieldNamesUrl = "form-field-names/all";
    const campaignStats = ref({});
    const userCampaigns = ref([]);
    const viewType = ref("self");
    const activeCampaignType = ref("active");

    const initData = {
        reference_number: "",
    };

    const columns = ref([]);

    const filterableColumns = [
        {
            key: "reference_number",
            value: t("lead.reference_number")
        },
    ];

    onMounted(() => {
        const campaignsUrl = getCampaignUrl(activeCampaignType.value, viewType.value);
        const campaignStatsUrl = getCampaignStatsUrl();
        const formFieldNamesPromise = axiosAdmin.get(formFieldNamesUrl);
        const campaignsPromise = axiosAdmin.get(campaignsUrl);
        const campaignStatsPromise = axiosAdmin.get(campaignStatsUrl);
        const userCampaignsPromise = axiosAdmin.get('campaigns/user-campaigns');

        Promise.all([formFieldNamesPromise, campaignsPromise, campaignStatsPromise, userCampaignsPromise])
            .then(([formFieldNamesResponse, campaignsResponse, campaignStatsResponse, userCampaignsResponse]) => {
                allFormFieldNames.value = formFieldNamesResponse.data.data;
                allCampaigns.value = campaignsResponse.data;
                campaignStats.value = campaignStatsResponse.data;
                userCampaigns.value = userCampaignsResponse.data.user_campaigns;

                var newColumnsArray = [
                    {
                        title: t("lead.reference_number"),
                        dataIndex: "reference_number",
                    },
                    {
                        title: t("lead.campaign"),
                        dataIndex: "campaign",
                    },
                ];

                forEach(formFieldNamesResponse.data.data, (formFieldName) => {
                    newColumnsArray.push({
                        title: formFieldName.field_name,
                        dataIndex: convertStringToKey(formFieldName.field_name),
                    });
                });

                newColumnsArray.push({
                    title: t("common.action"),
                    dataIndex: "action",
                });

                columns.value = newColumnsArray;
            });
    });

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,

        allFormFieldNames,
        convertStringToKey,
        allCampaigns,
        campaignStats,
        userCampaigns,

        activeCampaignType,
        viewType,
    }
}

export default fields;
