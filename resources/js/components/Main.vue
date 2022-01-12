<template>
    <div>
        <heading class="mb-6">CSV Import</heading>

        <card class="flex flex-col items-center justify-center" style="min-height: 300px">
            <input type="file" name="file" ref="file" @change="handleFile" class="mb-3">
            <button type="submit" class="btn btn-default btn-primary" v-bind:disabled="!file" @click="upload">Import</button>
        </card>
    </div>
</template>

<script>
export default {
    mounted() {
        //
    },
    data() {
        return {
            file: '',
        };
    },
    methods: {
        handleFile: function (event) {
            this.file = this.$refs.file.files[0];
        },
        upload: function (event) {
            let formData = new FormData();
            // send it to the server
            formData.append('file', this.file);

            const self = this;

            // if it's valid, move to the next screen
            Nova.request()
                .post('/nova-vendor/laravel-nova-csv-import/upload',
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }
                ).then(function(response){
                    self.$router.push({name: 'csv-import-preview', params: {file: response.data.file}})
                })
                .catch(function(e){
                    self.$toasted.show(e.response.data.message, {type: "error"});
                });
        }
    }
}
</script>

<style>
/* Scoped Styles */
</style>
