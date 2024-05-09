<template>
  <v-col class="form-item">
    <v-row
      align="center"
      class="pl-10"
      justify="space-between"
    >
      <div class="mb-4 text-h6">
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
    <div v-if="width >= 612">
      <v-row
        v-for="(_, idx) in inputItems"
        :key="idx"
        align="center"
        class="pl-10"
        justify="center"
      >
        <v-col class="pa-0">
          <v-text-field
            v-model="inputItems[idx].key"
            :label="$t('Key')"
            required
            :rules="individualInputKeyRules"
            variant="outlined"
            @update:focused="onInputItemsChange"
          />
        </v-col>
        <v-col class="ml-5 pa-0">
          <v-text-field
            v-model="inputItems[idx].value"
            :label="$t('Value')"
            required
            :rules="individualInputValueRules"
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
    </div>
    <div v-else>
      <v-row
        v-for="(_, idx) in inputItems"
        :key="idx"
        align="center"
        class="pl-10"
        dense
        justify="center"
      >
        <v-col class="pa-0">
          <v-row>
            <v-text-field
              v-model="inputItems[idx].key"
              :label="$t('Key')"
              required
              :rules="individualInputKeyRules"
              variant="outlined"
              @update:focused="onInputItemsChange"
            />
          </v-row>
          <v-row>
            <v-text-field
              v-model="inputItems[idx].value"
              :label="$t('Value')"
              required
              :rules="individualInputValueRules"
              variant="outlined"
              @update:model-value="onInputItemsChange"
            />
          </v-row>
        </v-col>
        <v-col class="no-grow pb-0">
          <v-btn
            class="icon-btn mb-6 mt-1"
            color="error"
            density="compact"
            icon="mdi-minus-circle"
            variant="text"
            @click="removeInputItem(idx)"
          />
        </v-col>
        <v-divider />
      </v-row>
    </div>
  </v-col>
</template>

<script setup>
import { trans } from "laravel-vue-i18n";
import { ref, watch } from "vue";
import { isMatlabVectorCharacters } from "@/utils/formRules";
import { escapeCharacters } from "../utils/escapeUtils";
import { useWindowSize } from "@vueuse/core";

const { width } = useWindowSize();
const inputItems = ref([{ key: "", value: "" }]);
const emit = defineEmits(["input-change"]);
const props = defineProps({
    formState: {
        type: Object,
        required: true,
    },
});

watch(props.formState, (newProps, _) => {
    try {
        const inputObject = JSON.parse(newProps.input);
        const keys = Object.keys(inputObject);
        const values = Object.values(inputObject);

        const tmpInputs = [];
        for (let i = 0; i < keys.length; i++) {
            tmpInputs.push({ key: keys[i], value: values[i] });
        }

        if (tmpInputs.length === 0) {
            inputItems.value = [{ key: "", value: "" }];
        } else {
            inputItems.value = tmpInputs;
        }
    } catch (e) {
        inputItems.value = [{ key: "", value: "" }];
    }
});

const onInputItemsChange = (_) => {
    let input = "{";
    for (let i = 0; i < inputItems.value.length; i++) {
        if (i !== 0) {
            input += ", ";
        }
        input += `"${escapeCharacters(
            inputItems.value[i].key
        )}": "${escapeCharacters(inputItems.value[i].value)}"`;
    }
    input += "}";
    emit("input-change", JSON.stringify(JSON.parse(input)));
};

const addInputItem = () => {
    inputItems.value.push({ key: "", value: "" });
    onInputItemsChange();
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

const individualInputValueRules = [
    (value) =>
        isMatlabVectorCharacters(value) || trans("ExperimentInputValueError"),
];
</script>

<style lang="scss" scoped>
.no-grow {
    flex-grow: 0;
}
</style>
