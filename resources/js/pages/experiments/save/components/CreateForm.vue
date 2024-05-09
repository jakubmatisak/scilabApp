<template>
  <v-form
    ref="form"
    :disabled="loading"
  >
    <v-container
      class="ma-0 pa-0"
      fluid
    >
      <v-row>
        <v-col class="form-item">
          <v-text-field
            v-model="formState.name"
            :label="$t('ExperimentName')"
            prepend-icon="mdi-rename-outline"
            required
            :rules="nameRules"
            variant="outlined"
          />
        </v-col>
        <v-col class="form-item">
          <v-file-input
            v-model="formState.file"
            accept=".zcos"
            chips
            :label="$t('ExperimentSchema')"
            required
            :rules="fileRules"
            variant="outlined"
          />
          <div
            v-if="file"
            class="file-info ml-10"
          >
            <strong>File:</strong> {{ file }}
          </div>
        </v-col>
      </v-row>
      <v-tabs v-model="tab">
        <v-tab value="individual">
          {{ $t("ExperimentIndividualItems") }}
        </v-tab>
        <v-tab value="object">
          {{ $t("ExperimentObjects") }}
        </v-tab>
      </v-tabs>
      <v-window v-model="tab">
        <v-window-item value="individual">
          <v-row class="mt-2">
            <input-items
              :form-state="formState"
              @input-change="changeInputItems"
            />
            <output-items
              :form-state="formState"
              @output-change="changeOutputItems"
            />
          </v-row>
        </v-window-item>

        <v-window-item value="object">
          <v-row class="mt-2">
            <v-col class="form-item">
              <v-textarea
                v-model="formState.input"
                :label="$t('ExperimentContext')"
                prepend-icon="mdi-code-json"
                :rules="inputRules"
                variant="outlined"
              />
            </v-col>
            <v-col class="form-item">
              <v-textarea
                v-model="formState.output"
                :label="$t('ExperimentOutput')"
                prepend-icon="mdi-code-brackets"
                :rules="outputRules"
                variant="outlined"
              />
            </v-col>
          </v-row>
        </v-window-item>
      </v-window>
      <v-row
        v-if="!experiment"
        class="px-2"
        dense
        justify="end"
      >
        <v-spacer />
        <v-col class="no-grow pb-0">
          <v-btn
            :size="width < 650 ? 'small' : 'default'"
            variant="elevated"
            @click="onSimulateClicked"
          >
            {{ $t("Simulate") }}
          </v-btn>
        </v-col>
        <v-col class="no-grow">
          <v-btn
            :size="width < 650 ? 'small' : 'default'"
            variant="elevated"
            @click="onSaveClicked"
          >
            {{ $t("SaveExperiment") }}
          </v-btn>
        </v-col>
      </v-row>
      <v-row
        v-else
        class="px-2"
        justify="end"
      >
        <v-btn
          :size="width < 650 ? 'small' : 'default'"
          variant="elevated"
          @click="onSimulateClicked"
        >
          {{ $t("SaveExperiment") }}
        </v-btn>
      </v-row>
    </v-container>
  </v-form>
</template>

<script setup>
import { trans } from "laravel-vue-i18n";
import { ref, reactive, watch } from "vue";
import {
    arrayContainsUniqueItems,
    onlyStrings,
    isArrayString,
    isJsonString,
    objectContainsUniqueKeys,
} from "@/utils/formRules";
import OutputItems from "../../components/OutputItems.vue";
import InputItems from "../../components/InputItems.vue";
import { useWindowSize } from "@vueuse/core";
import { useNotificationStore } from "@/stores/NotificationService";
import { useRoute } from "vue-router";

const { width } = useWindowSize();
const { showSnackbar } = useNotificationStore();
const tab = ref(null);
const props = defineProps({
    loading: {
        type: Boolean,
        required: true,
    },
    experiment: {
        type: Object,
        default: undefined,
    },
    saveExperiment: {
        type: Function,
        required: true,
    },
});
const route = useRoute();
const isEditView = ref(route.path.includes("edit"));

const form = ref(null);
const formState = reactive({
    name: "",
    file: undefined,
    output: "[]",
    input: "{}",
});
const file = ref("");

watch(route, () => {
    resetFormDefaultValues();
    file.value = "";
    form.value.resetValidation();
});

watch(props, () => {
    if (props.experiment && props.loading === false) {
        const { context, name, output, file_name } = props.experiment;
        formState.input = context;
        formState.output = output;
        formState.name = name;
        file.value = file_name;
    }
});

const resetFormDefaultValues = () => {
    formState.save = false;
    formState.name = "";
    formState.file = undefined;
    formState.output = "[]";
    formState.input = "{}";
};

const changeOutputItems = (output) => {
    formState.output = output;
};

const changeInputItems = (input) => {
    formState.input = input;
};

const nameRules = [
    (value) => !formState.save || !!value || trans("ExperimentNameError"),
];
const fileRules = [
    (value) =>
        !value ||
        !!value.length ||
        !!file.value ||
        trans("ExperimentSchemaError"),
];
const outputRules = [
    (value) => isArrayString(value) || trans("ExperimentOutputArrayError"),
    (value) => onlyStrings(value) || trans("ExperimentOutputStringsError"),
    (value) =>
        arrayContainsUniqueItems(value) ||
        trans("ExperimentOutputUniqueStringError"),
];
const inputRules = [
    (value) => isJsonString(value) || trans("ExperimentContextError"),
    (value) =>
        objectContainsUniqueKeys(value) ||
        trans("ExperimentInputUniqueKeyError"),
];

const emit = defineEmits(["simulation-data-change"]);

const onSimulateClicked = () => {
    formState.save = false;
    createExperiment(false);
};

const onSaveClicked = () => {
    formState.save = true;
    createExperiment(true);
};

const createExperiment = async (isSave) => {
    const { valid: isValid } = await form.value.validate();
    if (isValid) {
        try {
            emit("simulation-data-change", { data: { simulation: [] } });
            const { data } = await props.saveExperiment({
                id: route.params.id,
                name: formState.name,
                file: formState.file || undefined,
                context: formState.input,
                output: formState.output,
                save: isSave,
            });

            const snackbarMessage = isEditView.value
                ? trans("ExperimentEditSuccess")
                : trans("ExperimentCreateSuccess");
            showSnackbar(snackbarMessage, "success");

            if (data.simulation.length == 0) {
                showSnackbar(trans("ExperimentSimulationError"), "error");
            }

            if (isSave) {
                resetFormDefaultValues();
                form.value.resetValidation();
            }
            emit("simulation-data-change", { data });
        } catch (err) {
            console.log(err);
            const snackbarMessage = isEditView.value
                ? trans("ExperimentEditError")
                : trans("ExperimentCreateError");
            showSnackbar(snackbarMessage, "error");
        }
    }
};
</script>

<style lang="scss">
.form-item {
    min-width: 400px;

    :deep(.v-field__input) {
        padding: 8px 16px;
    }

    .icon {
        opacity: 100%;
    }

    .file-info {
        margin-top: -16px;
    }

    .icon-btn {
        height: 24px !important;
        width: 24px !important;

        :deep(v-btn__content) {
            color: green;
        }
    }

    .v-row {
        margin: 0px;
    }
}

.no-grow {
    flex-grow: 0;
}
</style>
