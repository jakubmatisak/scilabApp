<template>
  <v-card class="h-100">
    <header-component
      :back-button="true"
      title="Detail View"
    >
      <v-btn
        v-if="data?.experiment?.created_by === currentLoggedUser.id"
        icon
        :to="`/experiments/${data?.experiment?.id}/edit`"
      >
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
        <simulate-form
          :context="data?.experiment?.context || ''"
          :loading="isPendingSimulation"
          :submit="handleSubmit"
        />
        <v-container>
          <graph-component :data="graphData" />
        </v-container>
      </v-container>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { useRoute } from "vue-router";
import { onMounted, watch, ref } from "vue";
import {
    useExperimentDetailMutation,
    useExperimentSimulateMutation,
} from "@/api/queries/experimentQueries";
import { useNotificationStore } from "@/stores/NotificationService";
import HeaderComponent from "./components/HeaderComponent.vue";
import GraphComponent from "./components/GraphComponent.vue";
import { useAuthStore } from "@/stores/Auth";
import { storeToRefs } from "pinia";
import SimulateForm from "./components/SimulateForm.vue";

const route = useRoute();
const { id } = route.params;
const graphData = ref([]);

const authStore = useAuthStore();
const { currentLoggedUser } = storeToRefs(authStore);

const { data, mutateAsync } = useExperimentDetailMutation();
const { mutateAsync: simulate, isPending: isPendingSimulation } =
    useExperimentSimulateMutation();

const { showSnackbar } = useNotificationStore();

onMounted(async () => {
    mutateAsync(id);
});

watch(route, () => {
    if (route.params.id) {
        mutateAsync(route.params.id);
    }
});

const handleSubmit = async (context) => {
    try {
        const { simulation } = await simulate({
            context,
            id: route.params.id,
        });

        showSnackbar("Experiment simulated successfully", "success");
        graphData.value = simulation;
    } catch (_) {
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
