import { onMounted, ref, computed } from "vue";
import { useI18n } from "vue-i18n";

const fields = () => {
    const url = "campaigns?fields=id,xid,name,reference_prefix,allow_reference_prefix,remaining_leads,total_leads,campaignUsers{id,xid,user_id,x_user_id,campaign_id,x_campaign_id},campaignUsers:user{id,xid,name,profile_image,profile_image_url},email_template_id,x_email_template_id,emailTemplate{id,xid,name},form_id,x_form_id,form{id,xid,name,form_fields},detail_fields,last_action_by,x_last_action_by,lastActioner{id,xid,name},completed_by,x_completed_by,completedBy{id,xid,name},started_on,completed_on,upcoming_lead_action";
    const addEditUrl = "campaigns";
    const hashableColumns = ['form_id', 'email_template_id'];
    const { t } = useI18n();
    const extraFilters = ref({
        campaign_status: "active",
    });

    const initData = {
        name: "",
        user_id: undefined,
        form_id: undefined,
        email_template_id: undefined,
        allow_reference_prefix: 0,
        reference_prefix: '',
        current_step: 0,
        detail_fields: [],
        import_lead_fields: [],
    };

    const columns = computed(() => {
        if (extraFilters.value.campaign_status == 'active') {
            return [
                {
                    title: t("campaign.name"),
                    dataIndex: "name",
                },
                {
                    title: t("campaign.progress"),
                    dataIndex: "progress",
                },
                {
                    title: t("campaign.members"),
                    dataIndex: "members",
                },
                {
                    title: t("campaign.form"),
                    dataIndex: "form",
                },
                {
                    title: t("campaign.started_on"),
                    dataIndex: "started_on",
                },
                {
                    title: t("campaign.last_actioner"),
                    dataIndex: "last_actioner",
                },
                {
                    title: t("common.action"),
                    dataIndex: "action",
                },
            ];
        } else {
            return [
                {
                    title: t("campaign.name"),
                    dataIndex: "name",
                },
                {
                    title: t("campaign.total_leads"),
                    dataIndex: "total_leads",
                },
                {
                    title: t("campaign.started_on"),
                    dataIndex: "started_on",
                },
                {
                    title: t("campaign.completed_on"),
                    dataIndex: "completed_on",
                },
                {
                    title: t("campaign.completed_by"),
                    dataIndex: "completed_by",
                },
            ];
        }
    });

    const filterableColumns = [
        {
            key: "name",
            value: t("common.name")
        },
    ];

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
        extraFilters,
    }
}

export default fields;
