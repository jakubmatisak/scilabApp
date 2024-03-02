<template>
  <v-card
    class="h-100"
    :loading="isPending"
  >
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
    <template #loader="{ isActive }">
      <v-progress-linear
        :active="isActive"
        color="blue-grey-lighten-3"
        height="4"
        indeterminate
      />
    </template>
    <v-card-text class="py-8">
      <div class="ma-auto w-50">
        <create-form
          ref="createFormRef"
          :loading="isPending"
        />
      </div>
      <graph-component :data="graphData" />
    </v-card-text>
  </v-card>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useExperimentSaveMutation } from '../../api/queries/experimentQueries';
import CreateForm from './components/CreateForm.vue';
import HeaderComponent from './components/HeaderComponent.vue';
import GraphComponent from './components/GraphComponent.vue';
import { useNotificationStore } from '@/stores/NotificationService';

const route = useRoute();
const isEditView = ref(route.path.includes('edit'));
const title = computed(()=> isEditView.value ? 'Edit View' : 'Create View');

const createFormRef = ref(null);
const { isPending, mutateAsync } = useExperimentSaveMutation();
const { showSnackbar } = useNotificationStore();

const graphData = ref([]);

const onSaveClicked = async () => {
  console.log(createFormRef.value.form);
  const { valid: isValid } = await createFormRef.value.form.validate();
  if (isValid) {
    try {
      const {data} = await mutateAsync({
        name: createFormRef.value.formState.name,
        file: createFormRef.value.formState.file[0],
        context: createFormRef.value.formState.input,
        output: createFormRef.value.formState.output,
      });
      
      showSnackbar("Experiment created successfully", "success");
      createFormRef.value.form.reset();
      graphData.value = data.simulation;

    } catch (err) {
      showSnackbar("There was an error when creating Experiment", "error");
    }
  }
};
</script>

<style scoped>
.v-card-title{
  padding: 0;
}
.v-card{
  display: flex !important;
  flex-direction: column;
}
.v-card-text{
  overflow: scroll;
  padding: 16;
}
</style>