import { defineStore } from "pinia";
import { reactive, toRefs } from "vue";

export const useNotificationStore = defineStore(
    "notification", () => {
    const state = reactive({
        snackbar: {
            show: false,
            color: 'success',
            message: '',
        },
    });

    const showSnackbar = (message, color = 'success') => {
        state.snackbar.show = true;
        state.snackbar.color = color;
        state.snackbar.message = message;
    };

    return {...toRefs(state), showSnackbar};
});
