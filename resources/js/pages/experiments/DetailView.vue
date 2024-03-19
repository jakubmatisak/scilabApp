<template>
  <v-card
    class="h-100"
    :loading="isPendingExperiment || isPendingSimulation"
  >
    <header-component
      :back-button="true"
      :title="$t('ExperimentDetailTitle')"
    >
      <v-btn
        v-if="data?.experiment?.created_by === currentLoggedUser.id"
        class="mr-2"
        :density="width < 960 ? 'comfortable' : 'default'"
        icon
        :size="width < 600 ? 'small' : 'default'"
        :to="`/experiments/${data?.experiment?.id}/edit`"
        variant="tonal"
      >
        <v-icon>mdi-pencil</v-icon>
      </v-btn>
      <v-menu
        v-if="data?.experiment?.created_by === currentLoggedUser.id"
      >
        <template #activator="{ props }">
          <v-btn
            v-bind="props"
            :density="width < 960 ? 'comfortable' : 'default'"
            icon="mdi-dots-vertical"
            :size="width < 600 ? 'small' : 'default'"
            variant="tonal"
          />
        </template>
        <v-list>
          <v-list-item
            append-icon="mdi-delete"
            base-color="red"
            title="Remove"
            value="remove"
            @click.stop="onRemoveClicked"
          />
        </v-list>
      </v-menu>
    </header-component>
    <template #loader="{ isActive }">
      <v-progress-linear
        :active="isActive"
        color="blue-grey-lighten-3"
        height="4"
        indeterminate
      />
    </template>
    <v-card-text>
      <v-container>
        <div class="d-flex">
          <div class="text-md-h4 text-sm-h5">
            {{ data?.experiment?.name }}
          </div>
          <v-spacer />
          <div class="text-md-h5 text-sm-h6">
            <span class="font-weight-thin">{{ $t("By") }}:</span>
            <span>{{
              user?.user?.name || data?.experiment?.created_by
            }}</span>
          </div>
        </div>
        <v-divider />
        <simulate-form
          :context="data?.experiment?.context || ''"
          :loading="isPendingSimulation"
          :submit="handleSubmit"
        />
        <v-container>
          <graph-component
            :data="graphData"
            :loading="isPendingSimulation"
          />
        </v-container>
      </v-container>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { trans } from "laravel-vue-i18n";
import { useRoute, useRouter } from "vue-router";
import { onMounted, watch, ref } from "vue";
import {
    useExperimentDestroyMutation,
    useExperimentDetailMutation,
    useExperimentSimulateMutation,
} from "@/api/queries/experimentQueries";
import { useUserDetailMutation } from "@/api/queries/userQueries";
import { useNotificationStore } from "@/stores/NotificationService";
import HeaderComponent from "./components/HeaderComponent.vue";
import GraphComponent from "./components/GraphComponent.vue";
import { useAuthStore } from "@/stores/Auth";
import { storeToRefs } from "pinia";
import SimulateForm from "./components/SimulateForm.vue";
import { useWindowSize } from "@vueuse/core";

const { width } = useWindowSize();
const route = useRoute();
const router = useRouter();
const { id } = route.params;
const graphData = ref([]);

const authStore = useAuthStore();
const { currentLoggedUser } = storeToRefs(authStore);

const {
    data,
    mutateAsync,
    isPending: isPendingExperiment,
} = useExperimentDetailMutation();
const { data: user, mutateAsync: loadUser } = useUserDetailMutation();
const { mutateAsync: simulate, isPending: isPendingSimulation } =
    useExperimentSimulateMutation();
const { mutateAsync: removeExperiment } = useExperimentDestroyMutation();

const { showSnackbar } = useNotificationStore();

onMounted(async () => {
    loadExperiment(id);
});

watch(route, () => {
    if (route.params.id) {
        loadExperiment(route.params.id);
    }
});

const loadExperiment = (experimentId) => {
    mutateAsync(experimentId)
        .then(({ experiment }) => loadUser(experiment.created_by))
        .catch((err) => console.error(err.message));
};

const handleSubmit = async (context) => {
    try {
        const { simulation } = await simulate({
            context,
            id: route.params.id,
        });

        showSnackbar(trans("ExperimentSimulationSuccess"), "success");
        graphData.value = simulation;
    } catch (_) {
        showSnackbar(trans("ExperimentSimulationError"), "error");
    }
};

const onRemoveClicked = async () => {
    await removeExperiment(route.params.id);

    showSnackbar(trans("ExperimentRemoveSuccess", "success"));
    router.push("/experiments");
};
</script>

<style lang="scss" scoped>
.v-card {
    display: flex !important;
    flex-direction: column;

    .v-card-text {
        overflow: scroll;
        padding: 16;
    }
}
</style>
