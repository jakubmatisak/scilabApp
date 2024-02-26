<template>
  <v-list
    density="compact"
    nav
    variant="tonal"
  >
    <v-list-item
      href="/#/experiments/add"
      prepend-icon="mdi-plus-circle"
      title="Create experiment"
      value="addExperiment"
    />
    <v-list-item
      href="/#/experiments"
      prepend-icon="mdi-list-box"
      title="Experiment List"
      value="experiments"
    />
  </v-list>
</template>
