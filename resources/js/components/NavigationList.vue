<template>
  <v-list
    density="compact"
    nav
    variant="tonal"
  >
    <v-list-item
      :active="$route.path === '/experiments/add'"
      href="/#/experiments/add"
      prepend-icon="mdi-plus-circle"
      :title="$t('CreateExperiment')"
      value="addExperiment"
    />
    <v-list-item
      :active="$route.path === '/experiments'"
      href="/#/experiments"
      prepend-icon="mdi-list-box"
      :title="$t('ExperimentList')"
      value="experiments"
    />
  </v-list>
</template>
