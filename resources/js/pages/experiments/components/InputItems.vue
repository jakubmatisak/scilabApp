<template>
  <v-col
    class="form-item"
    cols="12"
    :md="isCreateForm ? 6 : 12"
  >
    <v-row
      align="center"
      :class="{ 'pl-10': isCreateForm }"
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
        :class="{ 'pl-10': isCreateForm }"
        justify="center"
      >
        <v-col class="pa-0">
          <v-text-field
            v-model="inputItems[idx].key"
            :density="width < 400 ? 'compact' : 'default'"
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
            :density="width < 400 ? 'compact' : 'default'"
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
        :class="{ 'pl-10': isCreateForm }"
        dense
        justify="center"
      >
        <v-col class="pa-0">
          <v-row>
            <v-text-field
              v-model="inputItems[idx].key"
              :density="width < 400 ? 'compact' : 'default'"
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
              :density="width < 400 ? 'compact' : 'default'"
              :label="$t('Value')"
              required
              :rules="individualInputValueRules"
              variant="outlined"
              @update:model-value="onInputItemsChange"
            />
          </v-row>
        </v-col>
        <v-btn
          class="icon-btn mb-6 ml-4 mt-1"
          color="error"
          density="compact"
          icon="mdi-minus-circle"
          variant="text"
          @click="removeInputItem(idx)"
        />
        <v-divider class="pb-3" />
      </v-row>
    </div>
  </v-col>
</template>

<script setup>
import { trans } from "laravel-vue-i18n";
import { computed, ref, watch } from "vue";
import { isMatlabVectorCharacters } from "@/utils/formRules";
import { escapeCharacters } from "../utils/escapeUtils";
import { useWindowSize } from "@vueuse/core";
import { useRoute } from "vue-router";

const { width } = useWindowSize();
const inputItems = ref([{ key: "", value: "", order: 0 }]);
const emit = defineEmits(["input-change"]);
const props = defineProps({
    formState: {
        type: Object,
        required: true,
    },
});
const route = useRoute();
const isCreateForm = computed(
    () => route.path.includes("edit") || route.path.includes("add")
);

watch(props.formState, (newProps, _) => {    
    try {
        const inputObject = JSON.parse(newProps.input);

        const tmpInputs = [];
        for (let i = 0; i < inputObject.length; i++) {
            tmpInputs.push({ key: inputObject[i]['key'], value: inputObject[i]['value'], order: inputObject[i]['order'] });
        }

        if (inputObject.length === 0) {
            inputItems.value = [{ key: "", value: "", order: 0 }];
        } else {
            inputItems.value = inputObject;
        }
    } catch (e) {
        inputItems.value = [{ key: "", value: "", order: 0 }];
    }
});

const onInputItemsChange = (_) => {    
    let input = "[";
    for (let i = 0; i < inputItems.value.length; i++) {
        if (i !== 0) {
            input += ", ";
        }
        input += `{`;
        input += `"key":"${escapeCharacters( inputItems.value[i].key )}",`;
        input += `"value":"${escapeCharacters(inputItems.value[i].value)}",`;
        input += `"order":${escapeCharacters(inputItems.value[i].order)}`;
        input += `}`;
    }
    input += "]";
    emit("input-change", input);
};

const addInputItem = () => {
    // .length might not work in some cases
    inputItems.value.push({ key: "", value: "", order: inputItems.value.length });
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
