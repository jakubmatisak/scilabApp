<template>
  <v-form
    ref="form"
    :disabled="loading"
  >
    <v-container
      class="ma-0 pa-0"
      fluid
    >
      <v-row
        v-if="!experiment"
        class="px-2"
      >
        <v-switch
          v-model="formState.save"
          color="primary"
          inset
          :label="$t('SaveExperiment')"
        />
      </v-row>
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
                @update:model-value="onInputChange"
              />
            </v-col>
            <v-col class="form-item">
              <v-textarea
                v-model="formState.output"
                :label="$t('ExperimentOutput')"
                prepend-icon="mdi-code-brackets"
                :rules="outputRules"
                variant="outlined"
                @update:model-value="onOutputChange"
              />
            </v-col>
          </v-row>
        </v-window-item>
      </v-window>
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
import OutputItems from "./OutputItems.vue";
import InputItems from "./InputItems.vue";

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
});

const form = ref(null);
const formState = reactive({
    save: false,
    name: "",
    file: undefined,
    output: "[]",
    input: "{}",
    outputItems: [""],
    inputItems: [{ key: "", value: "" }],
});
const file = ref("");

watch(props, () => {
    if (props.experiment && props.loading === false) {
        const { context, name, output, file_name } = props.experiment;
        formState.input = context;
        formState.output = output;
        formState.name = name;
        file.value = file_name;
        onOutputChange();
        onInputChange();
    }
});

const changeOutputItems = (output) => {
    formState.output = output;
    onOutputChange();
};

const changeInputItems = (input) => {
    formState.input = input;
    onInputChange();
};

const onOutputChange = (_) => {
    try {
        const outputArray = JSON.parse(formState.output);

        if (
            outputArray &&
            typeof outputArray === "object" &&
            Array.isArray(outputArray)
        ) {
            formState.outputItems = outputArray;
        }
    } catch (e) {
        formState.outputItems = [""];
    }
};

const onInputChange = (_) => {
    try {
        const inputObject = JSON.parse(formState.input);
        const keys = Object.keys(inputObject);
        const values = Object.values(inputObject);

        const inputItems = [];
        for (let i = 0; i < keys.length; i++) {
            inputItems.push({ key: keys[i], value: values[i] });
        }

        formState.inputItems = inputItems;
    } catch (e) {
        formState.inputItems = [{ key: "", value: "" }];
    }
};

defineExpose({
    form,
    formState,
    file,
});

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
</script>

<style lang="scss">
.form-item {
    min-width: 300px;

    @media (min-width: 600px) {
        min-width: 380px;
    }

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
</style>
