import { ref } from "vue";

const viewDrawer = () => {
    const viewDrawerData = ref({});
    const isViewDrawerVisible = ref(false);

    const showViewDrawer = (record) => {
        viewDrawerData.value = record;
        isViewDrawerVisible.value = true;
    }

    const hideViewDrawer = () => {
        viewDrawerData.value = {};
        isViewDrawerVisible.value = false;
    }

    return {
        viewDrawerData,
        isViewDrawerVisible,

        showViewDrawer,
        hideViewDrawer,
    }
}

export default viewDrawer;
