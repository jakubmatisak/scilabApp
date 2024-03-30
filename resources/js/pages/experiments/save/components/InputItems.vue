<template>
  <v-col class="form-item">
    <v-row
      align="center"
      class="pl-8"
      justify="space-between"
    >
      <div class="text-h6">
        {{ $t("ExperimentInputs") }}:
      </div>
      <v-btn
        class="icon-btn"
        color="success"
        density="compact"
        icon="mdi-plus-circle"
        variant="text"
        @click="addInputItem"
      />
    </v-row>
    <v-row
      v-for="(_, idx) in inputItems"
      :key="idx"
      align="center"
      class="pl-8"
      justify="center"
    >
      <v-col class="pa-0">
        <v-text-field
          v-model="inputItems[idx].key"
          :label="$t('Key')"
          required
          :rules="individualInputKeyRules"
          variant="outlined"
          @update:model-value="onInputItemsChange"
        />
      </v-col>
      <v-col class="ml-5 pa-0">
        <v-text-field
          v-model="inputItems[idx].value"
          :label="$t('Value')"
          required
          variant="outlined"
          @update:model-value="onInputItemsChange"
        />
      </v-col>
      <v-btn
        class="icon-btn mb-6 ml-5 mt-1"
        color="error"
        density="compact"
        icon="mdi-minus-circle"
        variant="text"
        @click="removeInputItem(idx)"
      />
    </v-row>
  </v-col>
</template>

<script setup>
import { trans } from "laravel-vue-i18n";
import { ref, watch } from "vue";

const inputItems = ref([{ key: "", value: "" }]);
const emit = defineEmits(["input-change"]);
const props = defineProps({
    formState: {
        type: Object,
        required: true,
    },
});

watch(props.formState, (newProps, _) => {
    if (newProps.inputItems !== inputItems.value) {
        if (newProps.inputItems.length === 0) {
            inputItems.value = [{ key: "", value: "" }];
        } else {
            inputItems.value = newProps.inputItems;
        }
    }
});

const onInputItemsChange = (_) => {
    let input = "{";
    for (let i = 0; i < inputItems.value.length; i++) {
        if (i !== 0) {
            input += ", ";
        }
        input += `"${escapeQuotes(inputItems.value[i].key)}": "${escapeQuotes(
            inputItems.value[i].value
        )}"`;
    }
    input += "}";
    emit("input-change", input);
};

const escapeQuotes = (string) => {
    if (typeof string === "string") {
        return string.replaceAll('"', '\\"');
    }

    return string;
};

const addInputItem = () => {
    inputItems.value.push({ key: "", value: "" });
};

const removeInputItem = (idx) => {
    inputItems.value.splice(idx, 1);
    onInputItemsChange();
};

const individualInputKeyRules = [
    (value) =>
        inputItems.value.filter((v) => v.key === value).length === 1 ||
        trans("ExperimentInputUniqueKeyError"),
];
</script>
