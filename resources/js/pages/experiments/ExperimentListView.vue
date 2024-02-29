<template>
  <v-card class="h-100">
    <header-component title="Experiments">
      <v-btn
        prepend-icon="mdi-plus-circle"
        to="/experiments/add"
        variant="elevated"
      >
        Create Experiment
      </v-btn>
    </header-component>
    <v-card-text>
      <v-data-table-server
        v-model:items-per-page="itemsPerPage"
        class="px-4"
        :headers="headers"
        :hover="true"
        :items="experimentsMapped"
        :items-length="totalItems"
        :loading="isPending"
        @row:click="onRowClick"
        @update:options="loadItems"
      />
    </v-card-text>
  </v-card>
</template>

<script setup>
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import { useExperimentsListMutation } from "@/api/queries/experimentQueries";
import HeaderComponent from "./components/HeaderComponent.vue";
import { useDate } from 'vuetify';

const date = useDate();
const router = useRouter();
const {isPending, mutateAsync} = useExperimentsListMutation();
const itemsPerPage = ref(5);
const headers= [
  {
    title: 'ID',
    align: 'start',
    sortable: true,
    key: 'id',
  },
  {
    title: "Name",
    align: "start",
    sortable: true,
    key: "name"
  },
  { title: 'Created By', key: 'created_by', align: 'start' },
  { title: 'Created At', key: 'created_at', align: 'end' },
];

const experiments = ref([]);
const totalItems = ref(0);

const experimentsMapped = computed(()=>{
  return experiments.value.map(experiment => ({
    ...experiment,
    created_at: date.format(new Date(experiment.created_at), "keyboardDate")
  }));
});

const loadItems = () => {
  mutateAsync().then((data) => {
    if(data){
      experiments.value = data.experiments;
      totalItems.value = data.experiments.length;
    }
  });
};

const onRowClick = (item) => {
  router.push(`/experiments/${item.id}`);
};
</script>

<style scoped>
.v-card{
  display: flex !important;
  flex-direction: column;
}
.v-card-text{
  overflow: scroll;
}
</style>
