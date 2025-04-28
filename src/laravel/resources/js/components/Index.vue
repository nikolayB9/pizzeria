<script>
import axios from "axios";

export default {
    name: "Index",

    mounted() {
        this.getTest()
    },

    methods: {
        getTest() {
            axios.get('api/test')
                .then(res => {
                    console.log(res)
                })
        }
    }
}
</script>

<template>
    <div>Index</div>
</template>

<style scoped>

</style>
