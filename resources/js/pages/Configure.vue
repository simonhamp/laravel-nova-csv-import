<template>
    <div>
        <Head>
            <title>Configure Import</title>
        </Head>

        <heading class="mb-6">CSV Import - Configure</heading>

        <card class="p-8 space-y-4 mb-8">
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
        </card>

        <card class="p-8 space-y-4 mb-8">
            <p>
                Choose a resource to import this data into.
            </p>

            <div class="inline-flex items-center">
                <b>Resource:</b>
                <SelectControl @change="(value) => resource = value" :selected="resource" class="ml-4">
                    <option value="">- Select a resource -</option>
                    <option v-for="(label, index) in resources" :value="index">{{ label }}</option>
                </SelectControl>
            </div>
        </card>

        <card class="p-8 space-y-4">
            <p v-if="resource">
                Choose which data to fill the appropriate fields of the chosen resource. The columns from your uploaded
                file have been auto-matched to the resource fields with the same name.
            </p>

            <table cellpadding="10" v-if="resource">
                <thead class="border-b">
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                        <!-- <th>Modifier</th> -->
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="field in fields[resource]">
                        <td class="pr-2">
                            <span class="font-bold">{{ field.name }}</span><br>
                            <small class="text-grey-300">{{ field.attribute }}</small>
                        </td>
                        <td class="md:flex">
                            <SelectControl @change="(value) => mappings[field.attribute] = value" :selected="mappings[field.attribute]">
                                <option value="" v-if="field.rules.includes('required')" disabled>- This field is required -</option>
                                <option value="" v-else>- Leave field empty -</option>

                                <optgroup label="File columns">
                                    <option v-for="heading in headings" :value="heading">{{ heading }}</option>
                                </optgroup>

                                <optgroup label="Meta data">
                                    <option value="meta.file">File name (with suffix): {{ file }}</option>
                                    <option value="meta.file_name">File name (without suffix): {{ file_name }}</option>
                                    <option value="meta.original_file">Original file name (with suffix): {{ config.original_filename }}</option>
                                    <option value="meta.original_file_name">Original file name (without suffix): {{ original_file_name }}</option>
                                </optgroup>

                                <optgroup label="Custom">
                                    <option value="custom">Single value</option>
                                    <option value="custom.true">TRUE</option>
                                    <option value="custom.false">FALSE</option>
                                </optgroup>
                            </SelectControl>

                            <input v-model="values[field.attribute]" v-if="mappings[field.attribute] === 'custom'"
                                class="form-control form-input form-input-bordered ml-4">
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
        </card>
    </div>
</template>

<script>
export default {

    data() {
        return {
            resource: this.config?.resource || '',
            mappings: this.config?.mappings || {},
            saving: false,
        };
    },

    props: [
        'headings',
        'resources',
        'fields',
        'file',
        'file_name',
        'rows',
        'total_rows',
        'config',
    ],

    watch: {
        resource: {
            handler(newValue) {
                const fields = this.fields[newValue];

                // Reset all of the mappings
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

                    // Because they're an exact match, we don't need to get the exact heading out
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
                mappings: this.mappings,
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

        original_file_name() {
            if (this.config.original_filename?.includes('.')) {
                return this.config.original_filename.split('.').slice(0, -1).join('.');
            }

            return this.config.original_filename || '';
        }
    }
}
</script>
