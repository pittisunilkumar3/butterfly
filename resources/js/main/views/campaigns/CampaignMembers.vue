<template>
    <a-avatar-group
        :max-count="4"
        :max-style="{
            color: '#f56a00',
            backgroundColor: '#fde3cf',
        }"
    >
        <a-tooltip
            v-for="campaignUser in members"
            :key="campaignUser.x_user_id"
            :title="campaignUser.user.name"
            placement="top"
        >
            <a-avatar :src="campaignUser.user.profile_image_url" />
        </a-tooltip>
    </a-avatar-group>
</template>

<script>
import { defineComponent } from "vue";

export default defineComponent({
    props: ["members"],
    setup(props, { emit }) {
        return {};
    },
});
</script>
