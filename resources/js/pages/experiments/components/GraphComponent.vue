<template>
  <apexchart
    :options="options"
    :series="dataSeries"
    type="line"
  />
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    data: {
        type: Array,
        required: true
    }
});

const dataSeries = computed(()=> {
    if(!props.data.length){
        return [];
    }

    let reduceStartValue = [];
    const numberOfItems = Object.values(props.data[0]).length;

    for(let i = 1; i < numberOfItems; i++){
        reduceStartValue.push([]);
    }

    const series = props.data.reduce((prevVal, nextVal)=>{
        const values = Object.values(nextVal);

        const newVal = [];
        for(let i = 1; i < values.length; i++){
            newVal.push([...prevVal[i-1], values[i]]);
        }

        return [...newVal];
    },reduceStartValue);

  const keys = Object.keys(props.data[0]);
  const mappedSeries = series.map((ser, idx) => ({name: Array.isArray(props.data[0]) ? undefined : keys[idx + 1], data: ser}));

  return mappedSeries;
});

const xAxis = computed(()=>
    props.data.map(data => {
        const values = Object.values(data);
        return values[0];
    })
);

const options = computed(()=>({
    chart: {
        id: "scilab-simulation",
    },
    xaxis: {
        type: 'numeric',
        tickAmount: Math.ceil(xAxis.value[xAxis.value.length - 1]) / 1,
        categories: xAxis.value,
    },
    stroke: {
        width: 2,
    },
}));
</script>