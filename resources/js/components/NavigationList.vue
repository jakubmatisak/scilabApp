<template>
  <v-list
    density="compact"
    nav
    variant="tonal"
  >
    <v-list-item
      :active="$route.path === '/experiments/add'"
      base-color="primary"
      color="primary"
      href="/#/experiments/add"
      prepend-icon="mdi-plus-circle"
      :title="$t('CreateExperiment')"
      value="addExperiment"
    />
    <v-list-item
      :active="$route.path === '/experiments'"
      base-color="primary"
      color="primary"
      href="/#/experiments"
      prepend-icon="mdi-list-box"
      :title="$t('ExperimentList')"
      value="experiments"
    />
  </v-list>
</template>
