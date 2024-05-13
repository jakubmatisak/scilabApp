<template>
  <v-card
    class="h-100"
    :loading="isPending || isLoadingExperiment"
  >
    <header-component
      :back-button="true"
      :title="title"
    />
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
        :experiment="experiment"
        :loading="isPending"
        :save-experiment="saveExperiment"
        @simulation-data-change="onSimulationDataChanged"
      />
      <v-container
        class="mt-4 pa-0"
        fluid
      >
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
import GraphComponent from "../components/graph/GraphComponent.vue";
import HeaderComponent from "../components/HeaderComponent.vue";
import {
    useExperimentDetailMutation,
    useExperimentSaveMutation,
} from "@/api/queries/experimentQueries";

const route = useRoute();
const isEditView = computed(() => route.path.includes("edit"));
const title = computed(() =>
    isEditView.value ? trans("EditExperiment") : trans("CreateExperiment")
);

const { isPending, mutateAsync: saveExperiment } = useExperimentSaveMutation();
const {
    data: experimentData,
    mutateAsync: loadExperiment,
    isPending: isLoadingExperiment,
    reset,
} = useExperimentDetailMutation();

const experiment = computed(() => {
    return experimentData?.value?.experiment;
});
const graphData = ref([]);

const onSimulationDataChanged = ({ data }) => {
    graphData.value = data.simulation;
};

onMounted(() => {
    if (route.params.id) {
        loadExperiment(route.params.id);
    }
});

watch(route, () => {
    reset();
    if (route.params.id) {
        loadExperiment(route.params.id);
    }
});
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
