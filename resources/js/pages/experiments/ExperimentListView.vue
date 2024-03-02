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
        @click:row="onRowClick"
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
const itemsPerPage = ref(10);
const headers= [
  {
    title: 'ID',
    align: 'start',
    key: 'id',
  },
  {
    title: "Name",
    align: "start",
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

const loadItems = ({ page, itemsPerPage, sortBy }) => {
  mutateAsync({page, itemsPerPage, sortBy: sortBy && sortBy[0]}).then((data) => {
    if(data?.experiments){
      experiments.value = data.experiments.data;
      totalItems.value = data.experiments.total;
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
