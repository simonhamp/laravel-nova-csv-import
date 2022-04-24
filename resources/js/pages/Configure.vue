<template>
    <div>
        <Head>
            <title>Configure Import</title>
        </Head>

        <heading class="mb-6">CSV Import - Configure</heading>

        <card class="flex flex-col" style="min-height: 300px">
            <div class="p-8 space-y-4">
                <p>
                    We were able to discover <b>{{ headings.length }}</b> column(s) and <b>{{ total_rows }}</b>
                    row(s) in your data.
                </p>

                <p>
                    Here's a sample of the data:
                </p>

                <hr>

                <div class="overflow-scroll">
                    <table cellpadding="10">
                        <thead class="border-b">
                            <tr>
                                <th v-for="heading in headings"><span class="font-bold">{{ heading }}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in rows">
                                <td v-for="col in row">
                                    <code>
                                        {{ col }}
                                        <i v-if="! col">null</i>
                                    </code>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr>

                <p>
                    Choose a resource to import them into and match up the headings from the CSV to the
                    appropriate fields of the resource.
                </p>

                <div>
                    <SelectControl @change="(value) => resource = value" :selected="resource" class="md:w-1/2">
                        <option value="">- Select a resource -</option>
                        <option v-for="(label, index) in resources" :value="index">{{ label }}</option>
                    </SelectControl>
                </div>

                <table cellpadding="10" v-if="resource">
                    <thead class="border-b">
                        <tr>
                            <th>Fields</th>
                            <th>Columns</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="field in fields[resource]">
                            <td class="pr-2"><span class="font-bold">{{ field.name }}</span></td>
                            <td class="text-center">
                                <SelectControl @change="(value) => mappings[field.attribute] = value" :selected="mappings[field.attribute]">
                                    <option value="" v-if="field.rules.includes('required')" disabled>- This field is required -</option>
                                    <option value="" v-else>- Ignore this column -</option>
                                    <option v-for="heading in headings" :value="heading">{{ heading }}</option>
                                </SelectControl>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="flex justify-center space-x-2">
                    <LinkButton @click="goBack">
                        &leftarrow; Upload a different file
                    </LinkButton>
                    <DefaultButton :disabled="disabledSave" @click="saveConfig">
                        {{ saving ? 'Importing...' : 'Save &amp; Preview &rightarrow;' }}
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
            resource: this.config?.resource || '',
            mappings: this.config?.map || {},
            saving: false,
        };
    },

    props: [
        'headings',
        'resources',
        'fields',
        'file',
        'rows',
        'total_rows',
        'config',
    ],

    watch: {
        resource: {
            handler(newValue) {
                const fields = this.fields[newValue];

                // Reset all of the headings to blanks
                for (let {name, attribute} of fields) {
                    this.mappings[attribute] = "";
                }

                if (newValue === "") {
                    return;
                }

                // For each field of the resource, try to find a matching heading and pre-assign
                for (let {name, attribute} of fields) {
                    let heading = this.headings.indexOf(attribute);

                    if (heading < 0) {
                        continue;
                    }

                    this.mappings[attribute] = attribute;
                }
            },
            deep: true,
        }
    },

    methods: {
        saveConfig() {
            if (! this.hasValidConfiguration()) {
                return;
            }

            this.saving = true;

            let data = {
                resource: this.resource,
                map: this.mappings,
                file: this.file,
            };

            Nova.request()
                .post(this.url('configure'), data)
                .then((response) => {
                    if (response.status === 200) {
                        Nova.success('Configuration saved');
                        Nova.visit('/csv-import/preview/' + this.file);
                    }
                })
                .catch((e) => {
                    this.saving = false;
                    Nova.error('There was a problem saving your configuration');
                });

            this.saving = false;
        },

        goBack() {
            Nova.visit('/csv-import/')
        },

        hasValidConfiguration() {
            const mappedColumns = [],
                mappings = this.mappings;

            Object.keys(mappings).forEach(function (key) {
                if (mappings[key] !== "") {
                    mappedColumns.push(key);
                }
            });

            return this.resource !== '' && mappedColumns.length > 0;
        },

        url: function (path) {
            return '/nova-vendor/laravel-nova-csv-import/' + path;
        }
    },
    computed: {
        disabledSave() {
            return ! this.hasValidConfiguration() || this.saving;
        },
    }
}
</script>
