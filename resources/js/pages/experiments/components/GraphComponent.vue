<template>
  <div>
    <span class="text-md-h4 text-sm-h5">{{ $t("ExperimentResult") }}:</span>
    <div class="experiment-graf">
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
  </div>
</template>

<script setup>
import { computed } from "vue";

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
</script>

<style scoped>
.experiment-graf {
    position: relative;
}

.graph-no-data {
    background-color: rgba(255, 255, 255, 0.8);
}
</style>
