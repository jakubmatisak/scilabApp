<template>
  <v-form
    ref="form"
    :disabled="loading"
  >
    <v-text-field
      v-model="formState.name"
      class="mb-4"
      label="Name"
      prepend-icon="mdi-rename-outline"
      required
      :rules="nameRules"
      variant="outlined"
    />
    <v-file-input
      v-model="formState.file"
      accept=".zcos"
      chips
      class="mb-4"
      label="Experiment file"
      required
      :rules="fileRules"
      variant="outlined"
    />
    <v-textarea
      v-model="formState.output"
      class="mb-4"
      label="Output object"
      prepend-icon="mdi-code-brackets"
      :rules="outputRules"
      variant="outlined"
    />
    <v-textarea
      v-model="formState.input"
      label="Input object"
      prepend-icon="mdi-code-json"
      :rules="inputRules"
      variant="outlined"
    />
  </v-form>
</template>

<script setup>
import { reactive } from 'vue';
import { ref } from 'vue';

defineProps({
    loading: {
        type: Boolean,
        required: true
    }
});

const form = ref(null);
const formState = reactive({
    name: "",
    file: undefined,
    output: "[]",
    input: "{}",
});

defineExpose({
    form, formState
});

const nameRules = [(value) => !!value || "Name is required"];
const fileRules = [(value) => !value || !!value.length || "Experiment schema is required"];
const outputRules = [(value) => isArrayString(value) || "Output is not a valid Array",
                      (value) => onlyStrings(value) || "Output must contain only strings",
                      (value) => containsUnique(value) || "Output must contain unique strings"
                    ];
const inputRules = [(value) => isJsonString(value) || "Input is not a valid JSON",
                    (value) => onlyNumbersAsValue(value) || "Input must contain only numbers as values"];

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
  const notString = array.find(item=> typeof item !== "string");

  return !notString;
};

const isArrayString = (arrayString) => {
  try {
        const o = JSON.parse(arrayString);

        if (o && typeof o === "object" && Array.isArray(o)) {
            return true;
        }
    }
    catch (e) { /* empty */ }

    return false;
};

const onlyNumbersAsValue = (jsonString) => {
  const o = JSON.parse(jsonString);
  const values = Object.values(o);
  const notNumber = values.find(item => typeof item !== "number");

  return notNumber === undefined;
};

const isJsonString = (jsonString) => {
  try {
        const o = JSON.parse(jsonString);

        if (o && typeof o === "object") {
            return true;
        }
    }
    catch (e) { /* empty */ }

    return false;
};
</script>