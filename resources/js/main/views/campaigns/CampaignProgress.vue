<template>
    <template v-if="campaign.remaining_leads == 0">
        <AddLead
            v-if="permsArray.includes('leads_create') || permsArray.includes('admin')"
            :campaign="campaign"
            btnClass="p-0"
            btnType="link"
            @success="leadAdded"
        >
            <template #icon>
                <PlusOutlined />
            </template>
            {{ $t("lead.add") }}
        </AddLead>
        <span v-else>-</span>
    </template>
    <template v-else>
        <a-progress
            :percent="
                parseInt(
                    ((campaign.total_leads - campaign.remaining_leads) /
                        campaign.total_leads) *
                        100
                )
            "
            size="small"
        />
        <br />
        <a-typography-paragraph class="mt-5">
            {{ $t("campaign.remaining_leads") }}:
            <a-typography-text strong>
                {{ campaign.total_leads - campaign.remaining_leads }}/{{
                    campaign.total_leads
                }}
            </a-typography-text>
        </a-typography-paragraph>
    </template>
</template>

<script>
import { defineComponent } from "vue";
import { PlusOutlined } from "@ant-design/icons-vue";
import common from "../../../common/composable/common";
import AddLead from "../campaigns/AddLead.vue";

export default defineComponent({
    props: ["campaign"],
    emits: ["success"],
    components: {
        PlusOutlined,
        AddLead,
    },
    setup(props, { emit }) {
        const { permsArray } = common();
        const leadAdded = () => {
            emit("success");
        };

        return {
            leadAdded,
            permsArray,
        };
    },
});
</script>
