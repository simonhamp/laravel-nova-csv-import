<template>
    <div>
        <heading class="mb-6">CSV Import</heading>

        <card class="flex flex-col items-center justify-center" style="min-height: 300px">
            <h1 class="pb-4">Done!</h1>
            <p class="pb-4">All your data was successfully imported.</p>
            <a href="/nova/csv-import/">Upload more</a>
        </card>
    </div>
</template>

<script>
export default {
    mounted() {

    },
    data() {
        return {
            imported: '',
        };
    },
    methods: {
    }
}
</script>

<style>
/* Scoped Styles */
</style>
