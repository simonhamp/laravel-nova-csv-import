<template>
    <div>
        <Head>
            <title>Import Preview</title>
        </Head>

        <heading class="mb-6">CSV Import - Preview</heading>

        <card class="flex flex-col" style="min-height: 300px">
            <div class="p-8 space-y-4">
                <p>
                    You've selected to import <b>{{ mapped_columns.length }}</b> field(s) from <b>{{ total_rows }}</b>
                    record(s) in total, into your <b>{{ resource }}</b> resource. The following is a sample of what this
                    data will look like once imported.
                </p>

                <table class="table-auto">
                    <thead class="border-b">
                        <tr>
                            <th></th>
                            <th>Resource field</th>
                            <th v-for="(column, field) in columns">{{ field }}</th>
                        </tr>
                        <tr>
                            <th>#</th>
                            <th>File column</th>
                            <th v-for="(column, field) in columns">{{ column }} <i v-if="! column">unmapped</i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, index) in rows">
                            <td class="text-right border-r">{{ index + 1 }}</td>
                            <td></td>
                            <td v-for="column in columns">{{ row[column] }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="flex justify-center space-x-2">
                    <DefaultButton :disabled="importing" @click="runImport" ref="import">
                        {{ importing ? 'Importing...' : 'Import &rightarrow;' }}
                    </DefaultButton>
                </div>
            </div>
        </card>
    </div>
</template>

<script>
export default {
    data() {
        return {
            importing: false,
        };
    },

    props: [
        'columns',
        'mapped_columns',
        'resource',
        'file',
        'total_rows',
        'rows',
    ],

    methods: {
        runImport: function () {
            this.importing = true;

            let data = {
                file: this.file
            };

            Nova.request()
                .post(this.url('import'), data)
                .then((response) => {
                    if (response.status === 200) {
                        Nova.success('All data imported!', {type: "success"});
                        Nova.visit('/csv-import/review/' + this.file);
                    }
                })
                .catch((e) => {
                    this.importing = false;
                    Nova.error('There were problems importing some of your data');
                });
        },

        url: function (path) {
            return '/nova-vendor/laravel-nova-csv-import/' + path;
        }
    },
}
</script>
