<template>
  <v-form
    ref="form"
    class="mt-10"
    :disabled="loading"
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
        :disabled="loading"
        type="submit"
      >
        Simulate
      </v-btn>
    </div>
  </v-form>
</template>

<script setup>
import { ref, watch } from "vue";

const props = defineProps({
    submit: {
        type: Function,
        required: true,
    },
    context: {
        type: String,
        required: true,
    },
    loading: {
        type: Boolean,
        required: true,
    },
});

const form = ref(null);
const experimentInput = ref("");

watch(props, () => {
    experimentInput.value = props.context;
});

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

const onSubmit = () => {
    props.submit(experimentInput.value);
};
</script>
