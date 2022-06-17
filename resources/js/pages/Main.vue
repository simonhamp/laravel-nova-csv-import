<template>
    <div>
        <heading class="mb-6">CSV Import</heading>
        <!-- TODO: Put some history here -->
        <!-- TODO: Replace with Nova's own file field for sexier results -->
        <card class="flex flex-col items-center justify-center" style="min-height: 300px">
            <DefaultButton
                type="button"
                style="margin-bottom: 1rem"
                class="btn btn-default"
                @click="selectFile"
            >Choose file</DefaultButton>
            <input type="file" hidden name="file" id="import_file" ref="file" @change="handleFile" />
            <span style="margin-bottom: 1rem; font-weight: bold;" id="filename_preview"></span>
            <DefaultButton v-bind:disabled="!file" @click="upload">Upload &amp; Configure &rightarrow;</DefaultButton>
        </card>
    </div>
</template>

<script>
import {Errors} from 'laravel-nova'

export default {
    data() {
        return {
            file: '',
        };
    },

    methods: {
        handleFile: function (event) {
            this.file = this.$refs.file.files[0];
            document.getElementById("filename_preview").innerHTML = this.file.name;
        },

        selectFile: function (event) {
            document.getElementById("import_file").click();
        },

        upload: function (event) {
            let formData = new FormData();
            // send it to the server
            formData.append('file', this.file);

            const self = this;

            // if it's valid, move to the next screen
            return Nova.request()
                .post('/nova-vendor/laravel-nova-csv-import/upload',
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }
                )
                .then((response) => {
                    Nova.success('File uploaded!')
                    Nova.visit(response.data.configure);
                })
                .catch((e) => {
                    Nova.error(e.response.data.message);
                });
        }
    }
}
</script>
