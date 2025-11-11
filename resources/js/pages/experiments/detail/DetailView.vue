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
        v-if="
          data?.experiment?.created_by === currentLoggedUser.id ||
            currentLoggedUser.is_admin
        "
        class="mr-2"
        :density="width < 960 ? 'comfortable' : 'default'"
        icon
        :size="width < 600 ? 'small' : 'default'"
        variant="tonal"
        @click="downloadFile()"
      >
        <v-icon> mdi-download</v-icon>
      </v-btn>
      <v-btn
        v-if="
          data?.experiment?.created_by === currentLoggedUser.id ||
            currentLoggedUser.is_admin
        "
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
        v-if="
          data?.experiment?.created_by === currentLoggedUser.id ||
            currentLoggedUser.is_admin
        "
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
            :title="$t('Remove')"
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
      <v-row dense>
        <div class="text-md-h4 text-sm-h5">
          {{ data?.experiment?.name }}
        </div>
        <v-spacer />
        <div class="text-md-h5 text-sm-h6">
          <span class="font-weight-bold">{{ $t("By") }}:</span>
          <span>{{
            user?.user?.name || data?.experiment?.created_by
          }}</span>
        </div>
      </v-row>
      <v-divider />
      <simulate-form
        :context="JSON.stringify(data?.experiment?.context['data']) || '[]'"
        :loading="isPendingSimulation"
        :submit="handleSubmit"
      />
      <div class="pt-2">
        <graph-component
          :data="graphData"
          :loading="isPendingSimulation"
        />
      </div>
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
import HeaderComponent from "../components/HeaderComponent.vue";
import GraphComponent from "../components/graph/GraphComponent.vue";
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

const downloadFile = async () => {
    try {
        const token = localStorage.getItem("auth_token");
        const response = await fetch(`/api/experiments/${route.params.id}/schemas`, {
            method: "GET",
            headers: {
                Authorization: `Bearer ${token}`,
            },
        });

        if (!response.ok) {
            showSnackbar(trans("ExperimentDownloadError"), "error");
            return;
        }

        const blob = await response.blob();
        const contentDisposition = response.headers.get("Content-Disposition");
        let filename = `experiment_${route.params.id}.zcos`;
        
        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename="?(.+?)"?$/);
            if (filenameMatch) {
                filename = filenameMatch[1];
            }
        }

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        showSnackbar(trans("ExperimentDownloadSuccess"), "success");
    } catch (error) {
        showSnackbar(trans("ExperimentDownloadError"), "error");
    }
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
