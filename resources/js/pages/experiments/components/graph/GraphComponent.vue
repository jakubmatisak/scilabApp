<template>
  <div>
    <span class="text-md-h5 text-sm-h5">{{ $t("ExperimentResult") }}:</span>
    <v-tabs v-model="tab">
      <v-tab value="graph">
        {{ $t("GraphTab") }}
      </v-tab>
      <v-tab value="json">
        {{ $t("JSONTab") }}
      </v-tab>
    </v-tabs>
    <v-window v-model="tab">
      <v-window-item value="graph">
        <v-row
          dense
          justify="center"
        >
          <div
            class="experiment-graf mt-4"
            :style="{ 'max-width': `${(height - 304) * 1.5}px` }"
          >
            <apexchart
              :options="options"
              :series="dataSeries"
              type="line"
            />
            <v-overlay
              class="align-center justify-center rounded-lg"
              :close-on-back="false"
              contained
              :model-value="showOverlay"
              no-click-animation
              persistent
            >
              <div
                class="graph-no-data pa-4 rounded-lg text-md-h2 text-sm-h5"
              >
                <v-progress-circular
                  v-if="loading"
                  color="primary"
                  indeterminate
                  :size="45"
                />
                <span v-else>
                  {{ $t("GraphNoData") }}
                </span>
              </div>
            </v-overlay>
          </div>
        </v-row>
      </v-window-item>
      <v-window-item value="json">
        <div class="json">
          <vue-json-pretty
            :data="props.data"
            virtual
          />
        </div>
      </v-window-item>
    </v-window>
    <v-row
      class="pt-4 px-2"
      dense
      justify="center"
      justify-sm="end"
    >
      <v-btn
        :disabled="data.length === 0"
        variant="elevated"
        @click="onExportCSVClick"
      >
        {{ $t("ExportCSV") }}
      </v-btn>
    </v-row>
  </div>
</template>

<script setup>
import { computed, ref } from "vue";
import VueJsonPretty from "vue-json-pretty";
import "vue-json-pretty/lib/styles.css";
import { transformDataToCSVContent } from "./utils";
import { useWindowSize } from "@vueuse/core";

const { height } = useWindowSize();
console.log(height);

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
    loading: {
        type: Boolean,
        required: true,
    },
});

const showOverlay = computed(() => props.data.length === 0 || props.loading);
const tab = ref(null);

const dataSeries = computed(() => {
    if (!props.data.length) {
        return [];
    }

    let reduceStartValue = [];
    const numberOfItems = Object.values(props.data[0]).length;

    for (let i = 1; i < numberOfItems; i++) {
        reduceStartValue.push([]);
    }

    const series = props.data.reduce((prevVal, nextVal) => {
        const values = Object.values(nextVal);

        const newVal = [];
        for (let i = 1; i < values.length; i++) {
            newVal.push([...prevVal[i - 1], values[i]]);
        }

        return [...newVal];
    }, reduceStartValue);

    const keys = Object.keys(props.data[0]);
    const mappedSeries = series.map((ser, idx) => ({
        name: Array.isArray(props.data[0]) ? undefined : keys[idx + 1],
        data: ser,
    }));

    return mappedSeries;
});

const xAxis = computed(() =>
    props.data.map((data) => {
        const values = Object.values(data);
        return values[0];
    })
);

const options = computed(() => ({
    chart: {
        id: "scilab-simulation",
        toolbar: {
            tools: {
                download: false,
            },
        },
    },
    xaxis: {
        type: "numeric",
        tickAmount: Math.ceil(xAxis.value[xAxis.value.length - 1]) / 1,
        categories: xAxis.value,
    },
    stroke: {
        width: 2,
    },
    yaxis: {
        labels: {
            formatter: function (value) {
                return value.toFixed(3);
            },
        },
    },
}));

const onExportCSVClick = () => {
    const csvData = transformDataToCSVContent(props.data, true);
    console.log(csvData);
    const blob = new Blob([csvData], { type: "text/csv;charset=utf-8" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", "data.csv");
    link.click();
    link.remove();
};
</script>

<style scoped>
.experiment-graf {
    position: relative;
    flex: 1;
}

.graph-no-data {
    background-color: rgba(255, 255, 255, 0.8);
}

.json {
    background-color: rgba(0, 0, 0, 0.16);
    border-radius: 8px;
    padding: 4px;
}
</style>
