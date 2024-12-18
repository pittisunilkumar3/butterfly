<template>
    <a-card :bordered="false" :bodyStyle="bodyStyle">
        <CardWidget>
            <a-space>
                <CardWidgetIcon :bgColor="bgColor">
                    <slot name="image"></slot>
                </CardWidgetIcon>
                <figcaption>
                    <slot name="description"></slot>
                </figcaption>
            </a-space>
        </CardWidget>
    </a-card>
</template>

<script>
import { CardWidget, CardWidgetIcon } from "./style";

export default {
    props: {
        bgColor: {
            default: "#5F63F2",
        },
        bodyStyle: {
            default: {},
        },
    },
    components: {
        CardWidget,
        CardWidgetIcon,
    },
};
</script>

<style></style>
