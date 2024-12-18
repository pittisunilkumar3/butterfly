import { useI18n } from "vue-i18n";

const fields = () => {
    const leadLogUrl = "leadFollowUp{id,xid,log_type,time_taken,date_time,lead_id,x_lead_id,user_id,x_user_id},leadFollowUp:user{id,xid,name,profile_image,profile_image_url}";
    const url = `lead-follow-ups?fields=id,xid,reference_number,lead_data,started,campaign_id,x_campaign_id,campaign{id,xid,name,status},time_taken,first_action_by,x_first_action_by,firstActioner{id,xid,name},last_action_by,x_last_action_by,lastActioner{id,xid,name},lead_follow_up_id,x_lead_follow_up_id,${leadLogUrl}`;

    const hashableColumns = ['lead_id', 'campaign_id', 'user_id'];
    const { t } = useI18n();

    const columns = [
        {
            title: t("lead.reference_number"),
            dataIndex: "reference_number",
        },
        {
            title: t("lead.campaign"),
            dataIndex: "campaign",
        },
        {
            title: t("lead_follow_up.follow_up_time"),
            dataIndex: "date_time",
        },
        {
            title: t("lead_follow_up.follow_up_by"),
            dataIndex: "actioner",
        },
        {
            title: t('common.action'),
            dataIndex: "action",
        }
    ];

    const filterableColumns = [];

    return {
        url,
        columns,
        filterableColumns,
        hashableColumns,
    }
}

export default fields;
