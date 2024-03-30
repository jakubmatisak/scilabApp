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
      :label="$t('ExperimentContext')"
      prepend-icon="mdi-code-json"
      :rules="inputRules"
      variant="outlined"
    />
    <div class="d-flex justify-end">
      <v-btn
        :disabled="loading"
        :size="width < 600 ? 'small' : 'default'"
        type="submit"
      >
        {{ $t("Simulate") }}
      </v-btn>
    </div>
  </v-form>
</template>

<script setup>
import { useWindowSize } from "@vueuse/core";
import { trans } from "laravel-vue-i18n";
import { ref, watch } from "vue";
import { isJsonString } from "@/utils/formRules";

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

const { width } = useWindowSize();
const form = ref(null);
const experimentInput = ref("");

watch(props, (newVal, oldVal) => {
    if (newVal.context !== oldVal.context || !experimentInput.value) {
        experimentInput.value = props.context;
    }
});

const inputRules = [
    (value) => isJsonString(value) || trans("ExperimentContextError"),
];

const onSubmit = () => {
    props.submit(experimentInput.value);
};
</script>
