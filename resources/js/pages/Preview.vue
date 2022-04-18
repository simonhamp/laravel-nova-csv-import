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

                <table cellpadding="10">
                    <thead class="border-b">
                        <tr>
                            <th class="border-r" rowspan="2" valign="bottom">#</th>
                            <th v-for="(column, field) in columns">{{ field }}</th>
                        </tr>
                        <tr>
                            <th v-for="(column, field) in columns">{{ column }} <i v-if="! column">unmapped</i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, index) in rows">
                            <td class="text-right border-r">{{ index + 1 }}</td>
                            <td v-for="column in columns">{{ row[column] }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="flex justify-center space-x-2">
                    <LinkButton @click="reconfigure"><HeroiconsOutlineRewind /> Reconfigure</LinkButton>
                    
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
        runImport() {
            this.importing = true;

            let data = {
                file: this.file
            };

            Nova.request()
                .post(this.url('import'), data)
                .then((response) => {
                    if (response.status === 200) {
                        Nova.success('Importing...');
                        Nova.visit('/csv-import/review/' + this.file);
                    }
                })
                .catch((e) => {
                    this.importing = false;
                    Nova.error('There were problems importing some of your data');
                });

            this.importing = false;
        },

        reconfigure() {
            Nova.visit('/csv-import/configure/' + this.file);
        },

        url: function (path) {
            return '/nova-vendor/laravel-nova-csv-import/' + path;
        }
    },
}
</script>