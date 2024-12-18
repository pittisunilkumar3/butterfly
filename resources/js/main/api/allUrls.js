const leadFields = "id,xid,reference_number,lead_data,started,campaign_id,x_campaign_id,campaign{id,xid,name},time_taken,first_action_by,x_first_action_by,firstActioner{id,xid,name},last_action_by,x_last_action_by,lastActioner{id,xid,name}";

const allUrls = () => {
    const getUrl = (table, resourceType = 'index', resourceId = null) => {
        if (resourceType == 'show') {
            return `${table}/${resourceId}?fields=${leadFields}`;
        } else {
            return `${table}?fields=${leadFields}`;
        }
    }

    return {
        getUrl,
    }
}

export default allUrls;
