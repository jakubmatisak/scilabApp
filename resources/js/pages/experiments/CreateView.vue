<template>
  <div>
    <header-component
      :back-button="true"
      :title="title"
    >
      <v-btn
        :disabled="isPending"
        icon
        @click="onSaveClicked"
      >
        <v-icon>mdi-content-save</v-icon>
      </v-btn>
    </header-component>
    <v-progress-linear
      :active="isPending"
      color="blue-grey-lighten-3"
      height="4"
      indeterminate
    />
    <div class="py-8">
      <v-form
        ref="form"
        v-model="valid"
        :disabled="isPending"
      >
        <div class="ma-auto w-50">
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
            prepend-icon="mdi-code-json"
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
        </div>
        <v-snackbar
          v-model="snackbar"
          :color="snackbarColor"
          rounded="pill"
        >
          {{ snackbarText }}
        </v-snackbar>
      </v-form>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import HeaderComponent from './components/HeaderComponent.vue';
import { useExperimentSaveMutation } from '../../api/queries/experimentQueries';

const route = useRoute();
const router = useRouter();
const isEditView = ref(route.path.includes('edit'));
const title = computed(()=> isEditView.value ? 'Edit View' : 'Create View');

const { isPending, mutateAsync } = useExperimentSaveMutation();
const snackbar = ref(false);
const snackbarColor = ref("success");
const snackbarText = ref("Experiment created successfully");

const valid = ref(false);
const form = ref(null);
const formState = reactive({
    name: "",
    file: undefined,
    output: "{}",
    input: "{}",
});
const nameRules = [(value) => !!value || "Name is required"];
const fileRules = [(value) => !value || !!value.length || "Experiment schema is required"];
const outputRules = [(value) => isJsonString(value) || "Output is not a valid JSON"];
const inputRules = [(value) => isJsonString(value) || "Input is not a valid JSON"];

const isJsonString = (jsonString) => {
  try {
        var o = JSON.parse(jsonString);

        if (o && typeof o === "object") {
            return true;
        }
    }
    catch (e) { /* empty */ }

    return false;
};

const onSaveClicked = async () => {
  const { valid: isValid } = await form.value.validate();

  if (isValid) {
    try {
      const {data} = await mutateAsync({
        name: formState.name,
        file: formState.file[0],
        context: formState.input,
        output: formState.output,
      });

      console.log(data);
      
      snackbarText.value = "Experiment created successfully";
      snackbarColor.value = "success";
      snackbar.value = true;
      
      router.push("/experiments/10");
    } catch (err) {
      snackbarText.value = "There was an error when creating Experiment";
      snackbarColor.value = "error";
      snackbar.value = true;
    }
    
  }
};
</script>

<style scoped>
.v-card-title{
  padding: 0;
}

.v-card-text{
  padding: 16;
}
</style>