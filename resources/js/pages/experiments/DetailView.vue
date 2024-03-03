<template>
  <v-card class="h-100">
    <header-component
      :back-button="true"
      title="Detail View"
    >
      <v-btn icon>
        <v-icon>mdi-pencil</v-icon>
      </v-btn>
    </header-component>
    <v-card-text>
      <v-container>
        <div class="d-flex">
          <div class="text-h4">
            {{ data?.experiment?.name }}
          </div>
          <v-spacer />
          <div class="text-h5">
            <span class="font-weight-thin">By:</span>
            <span>{{ data?.experiment?.created_by }}</span>
          </div>
        </div>
        <v-divider />
        <v-form
          ref="form"
          class="mt-10"
          :disabled="isPendingSimulation"
          validate-on="submit"
          @submit.prevent="onSubmit"
        >
          <v-textarea
            v-model="experimentInput"
            label="Input object"
            prepend-icon="mdi-code-json"
            :rules="inputRules"
            variant="outlined"
          />
          <div class="d-flex justify-end">
            <v-btn
              :disabled="isPendingSimulation"
              type="submit"
            >
              Simulate
            </v-btn>
          </div>
        </v-form>
        <v-container>
          <graph-component :data="graphData" />
        </v-container>
      </v-container>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { useRoute } from "vue-router";
import { onMounted, ref } from "vue";
import {
    useExperimentDetailMutation,
    useExperimentSimulateMutation,
} from "@/api/queries/experimentQueries";
import { useNotificationStore } from "@/stores/NotificationService";
import HeaderComponent from "./components/HeaderComponent.vue";
import GraphComponent from "./components/GraphComponent.vue";

const route = useRoute();
const { id } = route.params;
const graphData = ref([]);
const { data, mutateAsync } = useExperimentDetailMutation();
const { mutateAsync: simulate, isPending: isPendingSimulation } =
    useExperimentSimulateMutation();
const { showSnackbar } = useNotificationStore();

onMounted(async () => {
    const { experiment } = await mutateAsync(id);
    experimentInput.value = experiment.context;
});

const form = ref(null);
const experimentInput = ref("");

const inputRules = [
    (value) => isJsonString(value) || "Input is not a valid JSON",
    (value) =>
        onlyNumbersAsValue(value) ||
        "Input must contain only numbers as values",
];

const isJsonString = (jsonString) => {
    try {
        const o = JSON.parse(jsonString);

        if (o && typeof o === "object") {
            return true;
        }
    } catch (e) {
        /* empty */
    }

    return false;
};

const onlyNumbersAsValue = (jsonString) => {
    const o = JSON.parse(jsonString);
    const values = Object.values(o);
    const notNumber = values.find((item) => typeof item !== "number");

    return notNumber === undefined;
};

const onSubmit = async () => {
    try {
        const { simulation } = await simulate({
            context: experimentInput.value,
        });

        showSnackbar("Experiment simulated successfully", "success");
        graphData.value = simulation;
    } catch (err) {
        showSnackbar("There was an error when simulating Experiment", "error");
    }
};
</script>

<style scoped>
.v-card {
    display: flex !important;
    flex-direction: column;
}
.v-card-text {
    overflow: scroll;
    padding: 16;
}
</style>
