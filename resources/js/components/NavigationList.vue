<template>
  <v-list
    density="compact"
    nav
    variant="tonal"
  >
    <v-list-item
      prepend-icon="mdi-folder"
      title="My Files"
      value="myfiles"
    />
    <v-list-item
      prepend-icon="mdi-account-multiple"
      title="Shared with me"
      value="shared"
    />
    <v-list-item
      prepend-icon="mdi-star"
      title="Starred"
      value="starred"
    />
  </v-list>
</template>
