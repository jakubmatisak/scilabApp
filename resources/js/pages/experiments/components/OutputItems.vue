<template>
  <v-col
    class="form-item"
    cols="12"
    md="6"
  >
    <v-row
      align="center"
      class="pl-10"
      justify="space-between"
    >
      <div class="mb-4 text-h6">
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
      :density="width < 400 ? 'compact' : 'default'"
      required
      :rules="individualOutputRules"
      variant="outlined"
      @update:model-value="onOutputItemsChange"
    >
      <template #append>
        <v-icon
          class="icon"
          :color="idx !== 0 ? 'error' : 'grey'"
          :disabled="idx === 0"
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
import { escapeCharacters } from "../utils/escapeUtils";
import { useWindowSize } from "@vueuse/core";

const { width } = useWindowSize();
const outputItems = ref(["time", ""]);
const emit = defineEmits(["output-change"]);
const props = defineProps({
    formState: {
        type: Object,
        required: true,
    },
});

watch(props.formState, (newProps, _) => {
    try {
        const outputArray = JSON.parse(newProps.output);
        if (
            outputArray &&
            typeof outputArray === "object" &&
            Array.isArray(outputArray)
        ) {
            if (outputArray.length === 0) {
                outputItems.value = ["time", ""];
            } else {
                outputItems.value = outputArray;
            }
        }
    } catch (e) {
        outputItems.value = [""];
    }
});

const onOutputItemsChange = (_) => {
    let output = "[";
    for (let i = 0; i < outputItems.value.length; i++) {
        if (i !== 0) {
            output += ", ";
        }
        output += `"${escapeCharacters(outputItems.value[i])}"`;
    }
    output += "]";
    emit("output-change", output);
};

const removeOutputItem = (idx) => {
    outputItems.value.splice(idx, 1);
    onOutputItemsChange();
};

const addOutputItem = () => {
    outputItems.value.push("");
    onOutputItemsChange();
};

const individualOutputRules = [
    (value) =>
        outputItems.value.filter((v) => v === value).length === 1 ||
        trans("ExperimentOutputUniqueStringError"),
];
</script>
