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
      <v-row dense>
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
            <v-col class="form-item">
              <v-row
                align="center"
                class="pl-10"
                justify="space-between"
              >
                <div class="text-h6">
                  {{ $t("ExperimentOutputs") }}:
                </div>
                <v-btn
                  class="icon-btn"
                  color="success"
                  density="compact"
                  icon="mdi-plus-circle"
                  variant="text"
                  @click="addOutputItem"
                />
              </v-row>
              <v-text-field
                v-for="(_, idx) in formState.outputItems"
                :key="idx"
                v-model="formState.outputItems[idx]"
                class="ml-10"
                required
                :rules="individualInputRules"
                variant="outlined"
                @update:model-value="onOutputItemsChange"
              >
                <template #append>
                  <v-icon
                    class="icon"
                    color="red"
                    @click="removeOutputItem(idx)"
                  >
                    mdi-minus-circle
                  </v-icon>
                </template>
              </v-text-field>
            </v-col>
            <v-col class="form-item" />
          </v-row>
        </v-window-item>

        <v-window-item value="object">
          <v-row class="mt-2">
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
            <v-col class="form-item">
              <v-textarea
                v-model="formState.input"
                :label="$t('ExperimentContext')"
                prepend-icon="mdi-code-json"
                :rules="inputRules"
                variant="outlined"
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
import { reactive, watch } from "vue";
import { ref } from "vue";

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
});
const file = ref("");

watch(props, () => {
    if (props.experiment) {
        const { context, name, output, file_name } = props.experiment;
        formState.input = context;
        formState.output = output;
        formState.name = name;
        file.value = file_name;
        onOutputChange();
    }
});

const onOutputChange = (_) => {
    if (formState.output) {
        const values = formState.output
            .replace("[", "")
            .replace("]", "")
            .replaceAll('"', "")
            .split(",");
        formState.outputItems = values.map((value) => value.trim());
    } else {
        formState.outputItems = [""];
    }
};

const onOutputItemsChange = (_) => {
    let output = "[";
    for (let i = 0; i < formState.outputItems.length; i++) {
        if (i !== 0) {
            output += ", ";
        }
        output += `"${formState.outputItems[i]}"`;
    }
    output += "]";
    formState.output = output;
};

const removeOutputItem = (idx) => {
    const items = [...formState.outputItems];
    items.splice(idx, 1);
    formState.outputItems = items;
    onOutputItemsChange();
};

const addOutputItem = () => {
    formState.outputItems = [...formState.outputItems, ""];
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
        containsUnique(value) || trans("ExperimentOutputUniqueStringError"),
];
const inputRules = [
    (value) => isJsonString(value) || trans("ExperimentContextError"),
];

const individualInputRules = [
    (value) =>
        formState.outputItems.filter((v) => v === value).length === 1 ||
        trans("ExperimentOutputUniqueStringError"),
];

const onlyUnique = (value, index, array) => {
    return array.indexOf(value) === index;
};

const containsUnique = (arrayString) => {
    const array = JSON.parse(arrayString);
    const uniqueArray = array.filter(onlyUnique);
    return uniqueArray.length === array.length;
};

const onlyStrings = (arrayString) => {
    const array = JSON.parse(arrayString);
    const notString = array.find((item) => typeof item !== "string");

    return !notString;
};

const isArrayString = (arrayString) => {
    try {
        const o = JSON.parse(arrayString);

        if (o && typeof o === "object" && Array.isArray(o)) {
            return true;
        }
    } catch (e) {
        /* empty */
    }

    return false;
};

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
</script>

<style lang="scss" scoped>
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
