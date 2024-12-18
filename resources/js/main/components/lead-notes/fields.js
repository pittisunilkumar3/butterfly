import { ref, onBeforeMount } from "vue";
import { useI18n } from "vue-i18n";
import { forEach } from "lodash-es";
import common from "../../../common/composable/common";

const fields = (props) => {
    const { convertStringToKey, getCampaignUrl, user } = common();
    const leadUrl = "lead{id,xid,reference_number,lead_data,started,campaign_id,x_campaign_id,time_taken,first_action_by,x_first_action_by,last_action_by,x_last_action_by},lead:campaign{id,xid,name,status},lead:firstActioner{id,xid,name},lead:lastActioner{id,xid,name}";
    const formFieldNamesUrl = "form-field-names/all";
    const url = `lead-logs?fields=id,xid,log_type,time_taken,date_time,user_id,x_user_id,notes,user{id,xid,name,profile_image,profile_image_url},lead_id,x_lead_id,${leadUrl}`;
    const allFormFieldNames = ref([]);
    const addEditUrl = "lead-logs";
    const hashableColumns = ['lead_id', 'campaign_id', 'user_id'];
    const { t } = useI18n();
    const initData = {
        notes: "",
        log_type: "notes",
    };
    const columns = ref([]);
    const allCampaigns = ref([]);
    const allUsers = ref([]);

    const filterableColumns = [];

    const getPrefetchData = () => {
        const campaignsUrl = getCampaignUrl();
        const campaignsPromise = axiosAdmin.get(campaignsUrl);
        const formFieldNamesPromise = axiosAdmin.get(formFieldNamesUrl);
        const staffMembersPromise = axiosAdmin.get(`all-users?log_type=${props.logType}`);

        return Promise.all([formFieldNamesPromise, campaignsPromise, staffMembersPromise]).then(([formFieldNamesResponse, campaignsResponse, staffMembersResponse]) => {
            allFormFieldNames.value = formFieldNamesResponse.data.data;
            allCampaigns.value = campaignsResponse.data;
            allUsers.value = staffMembersResponse.data.users;

            var newColumnsArray = [];

            if (props.showLeadDetails) {
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

                // Showing form fields if props.showFormFields
                // sets to true
                if (props.showFormFields) {
                    forEach(formFieldNamesResponse.data.data, (formFieldName) => {
                        newColumnsArray.push({
                            title: formFieldName.field_name,
                            dataIndex: convertStringToKey(formFieldName.field_name),
                        });
                    });
                }
            }

            newColumnsArray.push({
                title: t("common.notes"),
                dataIndex: "notes",
                width: props.showLeadDetails && props.showFormFields ? '40%' : '80%',
            });

            if (props.showActionButton) {
                newColumnsArray.push({
                    title: t('common.action'),
                    dataIndex: "action",
                });
            }

            columns.value = newColumnsArray;
        });
    };

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
        allFormFieldNames,
        allCampaigns,
        allUsers,
        getPrefetchData,
    }
}

export default fields;
