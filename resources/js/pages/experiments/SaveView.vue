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
        :disabled="isPending"
        icon
        @click="onSaveClicked"
      >
        <v-icon>mdi-content-save</v-icon>
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
      <div class="ma-auto w-50">
        <create-form
          ref="createFormRef"
          :experiment="experiment"
          :loading="isPending"
        />
      </div>
      <v-container>
        <graph-component :data="graphData" />
      </v-container>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import CreateForm from "./components/CreateForm.vue";
import GraphComponent from "./components/GraphComponent.vue";
import HeaderComponent from "./components/HeaderComponent.vue";
import {
    useExperimentDetailMutation,
    useExperimentSaveMutation,
} from "@/api/queries/experimentQueries";
import { useNotificationStore } from "@/stores/NotificationService";

const route = useRoute();
const isEditView = ref(route.path.includes("edit"));
const title = computed(() => (isEditView.value ? "Edit View" : "Create View"));

const createFormRef = ref(null);

const { isPending, mutateAsync } = useExperimentSaveMutation();
const {
    data: experimentData,
    mutateAsync: loadExperiment,
    isPending: isLoadingExperiment,
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
    if (route.params.id) {
        loadExperiment(route.params.id);
    }
});

const onSaveClicked = async () => {
    const { valid: isValid } = await createFormRef.value.form.validate();
    if (isValid) {
        try {
            console.log(createFormRef);
            const { data } = await mutateAsync({
                id: route.params.id,
                name: createFormRef.value.formState.name,
                file: createFormRef.value.formState.file
                    ? createFormRef.value.formState.file[0]
                    : undefined,
                context: createFormRef.value.formState.input,
                output: createFormRef.value.formState.output,
            });

            const snackbarMessage = `Experiment ${
                isEditView.value ? "updated" : "created"
            } successfully`;
            showSnackbar(snackbarMessage, "success");

            if (!isEditView.value) {
                createFormRef.value.form.reset();
            }
            graphData.value = data.simulation;
        } catch (err) {
            const snackbarMessage = `There was an error when ${
                isEditView.value ? "updating" : "creating"
            } Experiment`;
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
