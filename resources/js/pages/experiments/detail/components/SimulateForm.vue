<template>
  <v-form
    ref="form"
    class="mt-10"
    :disabled="loading"
    validate-on="submit"
    @submit.prevent="onSubmit"
  >
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
        </v-row>
      </v-window-item>
      <v-window-item value="object">
        <v-row class="mt-2">
          <v-col>
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
import { reactive, ref, watch } from "vue";
import { isJsonString } from "@/utils/formRules";
import InputItems from "../../components/InputItems.vue";

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
const tab = ref(null);
const formState = reactive({
    input: "{}",
});

watch(props, () => {
    formState.input = props.context;
});

const inputRules = [
    (value) => isJsonString(value) || trans("ExperimentContextError"),
];

const changeInputItems = (input) => {
    formState.input = input;
};

const onSubmit = () => {
    props.submit(formState.input);
};
</script>
