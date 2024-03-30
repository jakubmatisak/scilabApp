<template>
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
      v-for="(_, idx) in outputItems"
      :key="idx"
      v-model="outputItems[idx]"
      class="ml-10"
      required
      :rules="individualOutputRules"
      variant="outlined"
      @update:model-value="onOutputItemsChange"
    >
      <template #append>
        <v-icon
          class="icon"
          color="error"
          @click="removeOutputItem(idx)"
        >
          mdi-minus-circle
        </v-icon>
      </template>
    </v-text-field>
  </v-col>
</template>

<script setup>
import { trans } from "laravel-vue-i18n";
import { ref, watch } from "vue";

const outputItems = ref([""]);
const emit = defineEmits(["output-change"]);
const props = defineProps({
    formState: {
        type: Object,
        required: true,
    },
});

watch(props.formState, (newProps, _) => {
    if (newProps.outputItems !== outputItems.value) {
        if (newProps.outputItems.length === 0) {
            outputItems.value = [""];
        } else {
            outputItems.value = newProps.outputItems;
        }
    }
});

const onOutputItemsChange = (_) => {
    let output = "[";
    for (let i = 0; i < outputItems.value.length; i++) {
        if (i !== 0) {
            output += ", ";
        }
        output += `"${escapeQuotes(outputItems.value[i])}"`;
    }
    output += "]";
    emit("output-change", output);
};

const escapeQuotes = (string) => {
    if (typeof string === "string") {
        return string.replaceAll('"', '\\"');
    }

    return string;
};

const removeOutputItem = (idx) => {
    outputItems.value.splice(idx, 1);
    onOutputItemsChange();
};

const addOutputItem = () => {
    outputItems.value.push("");
};

const individualOutputRules = [
    (value) =>
        outputItems.value.filter((v) => v === value).length === 1 ||
        trans("ExperimentOutputUniqueStringError"),
];
</script>
