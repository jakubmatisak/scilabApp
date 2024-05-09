<template>
  <v-card class="h-100">
    <header-component :title="$t('ExperimentList')">
      <v-btn
        v-if="width > 520"
        prepend-icon="mdi-plus-circle"
        :size="width < 600 ? 'small' : 'default'"
        to="/experiments/add"
        variant="tonal"
      >
        {{ $t("CreateExperiment") }}
      </v-btn>
      <v-btn
        v-else
        density="comfortable"
        icon
        size="small"
        to="/experiments/add"
      >
        <v-icon>mdi-plus-circle</v-icon>
      </v-btn>
    </header-component>
    <v-card-text>
      <v-data-table-server
        v-model:items-per-page="itemsPerPage"
        class="px-4"
        fixed-header
        :headers="headers"
        :hover="true"
        :items="experimentsMapped"
        :items-length="totalItems"
        :loading="isPending"
        :mobile="false"
        :search="searchName"
        @click:row="onRowClick"
        @update:options="loadItems"
      >
        <template #top>
          <v-text-field
            v-model="searchName"
            append-inner-icon="mdi-magnify"
            class="search-input"
            hide-details
            :placeholder="$t('SearchExperiment')"
          />
        </template>
        <!-- CODE IS INSPIRED BY JAKUB MATISAK -->
        <template
          v-if="width < 768"
          #headers="{ columns, isSorted, getSortIcon, toggleSort }"
        >
          <v-expansion-panels variant="accordion">
            <v-expansion-panel
              elevation="1"
              :title="$t('Sort by headers')"
            >
              <v-expansion-panel-text>
                <v-list>
                  <v-list-item
                    v-for="item in columns"
                    :key="item.key"
                    :title="item.title"
                    :value="item"
                    @click.stop="
                      item.sortable
                        ? toggleSort(item)
                        : null
                    "
                  >
                    <template
                      v-if="isSorted(item)"
                      #append
                    >
                      <v-icon :icon="getSortIcon(item)" />
                    </template>
                  </v-list-item>
                </v-list>
              </v-expansion-panel-text>
            </v-expansion-panel>
          </v-expansion-panels>
        </template>

        <template
          v-if="width < 768"
          #body="{ internalItems, items, headers }"
        >
          <tr
            v-if="items.length === 0"
            class="text-center"
          >
            <td>{{ $t("No data to display") }}</td>
          </tr>
          <tr
            v-for="(item, i) in internalItems"
            v-else
            :key="i"
            class="cursor-pointer"
            @click.stop="() => console.log(width)"
          >
            <td>
              <ul class="mobile-grid-content">
                <li
                  v-for="(column, j) in Object.keys(
                    item.columns
                  )"
                  :key="j"
                  class="mobile-grid-item"
                >
                  <div class="mobile-grid-item-bolder">
                    {{ headers[0][j].title }}
                  </div>
                  <div class="mobile-grid-item-value">
                    {{ item.columns[column] }}
                  </div>
                </li>
              </ul>
            </td>
          </tr>
        </template>
      </v-data-table-server>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { trans } from "laravel-vue-i18n";
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import { useExperimentsListMutation } from "@/api/queries/experimentQueries";
import { useUserListQuery } from "@/api/queries/userQueries";
import HeaderComponent from "./components/HeaderComponent.vue";
import { useDate } from "vuetify";
import { useWindowSize } from "@vueuse/core";

const { width } = useWindowSize();
const searchName = ref("");
const date = useDate();
const router = useRouter();
const { isPending, mutateAsync } = useExperimentsListMutation();
const { data } = useUserListQuery();
const itemsPerPage = ref(10);
const headers = computed(() => [
    {
        title: "ID",
        align: "start",
        key: "id",
    },
    {
        title: trans("ExperimentName"),
        align: "start",
        key: "name",
    },
    { title: trans("CreatedBy"), key: "created_by", align: "start" },
    { title: trans("CreatedAt"), key: "created_at", align: "end" },
]);

const experiments = ref([]);
const totalItems = ref(0);

const experimentsMapped = computed(() => {
    return experiments.value.map((experiment) => ({
        ...experiment,
        created_at: date.format(
            new Date(experiment.created_at),
            "keyboardDateTime"
        ),
        created_by: getCreatedBy(experiment.created_by),
    }));
});

const getCreatedBy = (id) => {
    const user = data?.value?.data?.users?.find((u) => u.id === id);

    return user ? user.name : id;
};

const debounce = (fn, delay) => {
    let timer;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            fn(...args);
        }, delay);
    };
};

const loadItems = debounce(({ page, itemsPerPage, sortBy }) => {
    mutateAsync({
        page,
        itemsPerPage,
        sortBy: sortBy && sortBy[0],
        search: searchName.value,
    }).then((data) => {
        if (data?.experiments) {
            experiments.value = data.experiments.data;
            totalItems.value = data.experiments.total;
        }
    });
}, 500);

const onRowClick = (_, { item }) => {
    router.push(`/experiments/${item.id}`);
};
</script>

<style lang="scss" scoped>
.v-card {
    display: flex !important;
    flex-direction: column;

    .v-card-text {
        overflow: scroll;

        .v-table {
            height: 100%;

            :deep(.v-data-table__th) {
                font-weight: bold;
            }

            .search-input {
                display: block;
                flex: 0;
            }

            .mobile-grid-content {
                list-style-type: none;
                padding: 1rem 0;
                margin: 0;
                display: flex;
                flex-wrap: wrap;
                width: 100%;

                .mobile-grid-item {
                    display: flex;
                    justify-content: space-between;
                    min-height: 1.5rem;
                    padding: 0.25rem 1rem;
                    width: 100%;

                    .mobile-grid-item-bolder {
                        font-weight: bold;
                    }

                    .mobile-grid-item-value {
                        text-align: right;
                    }
                }
            }
        }
    }
}
</style>
