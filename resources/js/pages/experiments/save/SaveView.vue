<template>
  <v-card
    class="h-100"
    :loading="isPending || isLoadingExperiment"
  >
    <header-component
      :back-button="true"
      :title="title"
    >
      <v-btn
        :density="width < 960 ? 'comfortable' : 'default'"
        :disabled="isPending"
        icon
        variant="tonal"
        @click="onSaveClicked"
      >
        <v-icon :size="width < 600 ? 'small' : 'default'">
          {{ isEditView ? "mdi-content-save" : "mdi-plus-circle" }}
        </v-icon>
      </v-btn>
    </header-component>
    <template #loader="{ isActive }">
      <v-progress-linear
        :active="isActive"
        color="blue-grey-lighten-3"
        height="4"
        indeterminate
      />
    </template>
    <v-card-text class="py-8">
      <create-form
        ref="createFormRef"
        :experiment="experiment"
        :loading="isPending"
      />
      <v-container fluid>
        <graph-component
          :data="graphData"
          :loading="isPending"
        />
      </v-container>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { trans } from "laravel-vue-i18n";
import { computed, onMounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import CreateForm from "./components/CreateForm.vue";
import GraphComponent from "../components/GraphComponent.vue";
import HeaderComponent from "../components/HeaderComponent.vue";
import {
    useExperimentDetailMutation,
    useExperimentSaveMutation,
} from "@/api/queries/experimentQueries";
import { useNotificationStore } from "@/stores/NotificationService";
import { useWindowSize } from "@vueuse/core";

const { width } = useWindowSize();
const route = useRoute();
const isEditView = ref(route.path.includes("edit"));
const title = computed(() =>
    isEditView.value ? trans("EditExperiment") : trans("CreateExperiment")
);

const createFormRef = ref(null);

const { isPending, mutateAsync } = useExperimentSaveMutation();
const {
    data: experimentData,
    mutateAsync: loadExperiment,
    isPending: isLoadingExperiment,
    reset,
} = useExperimentDetailMutation();
const { showSnackbar } = useNotificationStore();

const experiment = computed(() => {
    return experimentData?.value?.experiment;
});
const graphData = ref([]);

onMounted(() => {
    if (route.params.id) {
        loadExperiment(route.params.id);
    }
});

watch(route, () => {
    createFormRef.value.form.reset();
    createFormRef.value.file = "";
    reset();
    resetFormDefaultValues();
    if (route.params.id) {
        loadExperiment(route.params.id);
    }
});

const resetFormDefaultValues = () => {
    createFormRef.value.formState.save = false;
    createFormRef.value.formState.name = "";
    createFormRef.value.formState.file = undefined;
    createFormRef.value.formState.output = "[]";
    createFormRef.value.formState.input = "{}";
    createFormRef.value.formState.outputItems = [""];
    createFormRef.value.formState.inputItems = [{ key: "", value: "" }];
};

const onSaveClicked = async () => {
    const { valid: isValid } = await createFormRef.value.form.validate();
    if (isValid) {
        try {
            const { data } = await mutateAsync({
                id: route.params.id,
                name: createFormRef.value.formState.name,
                file: createFormRef.value.formState.file
                    ? createFormRef.value.formState.file[0]
                    : undefined,
                context: createFormRef.value.formState.input,
                output: createFormRef.value.formState.output,
                save: createFormRef.value.formState.save,
            });

            const snackbarMessage = isEditView.value
                ? trans("ExperimentEditSuccess")
                : trans("ExperimentCreateSuccess");
            showSnackbar(snackbarMessage, "success");

            if (!isEditView.value) {
                createFormRef.value.form.reset();
            }
            graphData.value = data.simulation;
        } catch (err) {
            const snackbarMessage = isEditView.value
                ? trans("ExperimentEditError")
                : trans("ExperimentCreateError");
            showSnackbar(snackbarMessage, "error");
        }
    }
};
</script>

<style scoped>
.v-card-title {
    padding: 0;
}
.v-card {
    display: flex !important;
    flex-direction: column;
}
.v-card-text {
    overflow: scroll;
    padding: 16;
}
</style>
