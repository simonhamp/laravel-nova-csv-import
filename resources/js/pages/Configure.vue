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
                <SelectControl @change="(value) => resource = value" :selected="resource" class="mx-4">
                    <option value="">- Select a resource -</option>
                    <option v-for="(label, index) in resources" :value="index">{{ label }}</option>
                </SelectControl>
            </div>

            <p v-if="resource">
                Now choose which data should fill the appropriate fields of the chosen resource. The columns from your
                uploaded file have been auto-matched to the resource fields with the same name.
            </p>
        </card>

        <card class="p-8 space-y-4 mb-8" v-for="field in fields[resource]" v-if="resource">
            <small>Field</small>
            <h3 class="text-lg font-bold">
                {{ field.name }}
                <small>(<code>{{ field.attribute }}</code>)</small>
            </h3>

            <h4 class="text-base font-bold">Source</h4>

            <SelectControl @change="(value) => mappings[field.attribute] = value" :selected="mappings[field.attribute]">
                <option value="" v-if="field.rules.includes('required')" disabled>- This field is required -</option>
                <option value="" v-else>- Leave field empty -</option>

                <optgroup label="Single column">
                    <option v-for="heading in headings" :value="heading">{{ heading }}</option>
                </optgroup>

                <optgroup label="Combined columns">
                    <option value="combined">Combine values from multiple columns </option>
                </optgroup>

                <optgroup label="Meta data">
                    <option value="meta.file">File name (with suffix): {{ file }}</option>
                    <option value="meta.file_name">File name (without suffix): {{ file_name }}</option>
                    <option value="meta.original_file">Original file name (with suffix): {{ config.original_filename }}</option>
                    <option value="meta.original_file_name">Original file name (without suffix): {{ original_file_name }}</option>
                </optgroup>

                <optgroup label="Custom - same value for each row">
                    <option value="custom">Single value</option>
                </optgroup>

                <optgroup label="Custom - different for each row">
                    <option value="random">Random string</option>
                </optgroup>
            </SelectControl>

            <FieldCombinator v-if="mappings[field.attribute] === 'combined'"
                :attribute="field.attribute"
                :config="combined[field.attribute]"
                :headings="headings"
                @update="setFieldCombinators">
            </FieldCombinator>

            <!-- Custom value input field -->
            <label class="flex items-center space-x-2" v-if="mappings[field.attribute] === 'custom'">
                <span>Value</span>
                <input v-model="values[field.attribute]"
                    class="form-control form-input form-input-bordered flex-1">
            </label>

            <label class="flex items-center space-x-2" v-if="mappings[field.attribute] === 'random'">
                <span>Length</span>
                <input v-model="random[field.attribute]"
                    class="form-control form-input form-input-bordered">
            </label>

            <Modifiers v-if="mappings[field.attribute]"
                :attribute="field.attribute"
                :config.sync="modifiers[field.attribute]"
                :mods="mods"
                @update="setFieldModifiers">
            </Modifiers>
        </card>

        <card class="p-8 space-y-4">
            <div class="flex justify-between">
                <LinkButton @click="goBack">
                    &leftarrow; Upload a different file
                </LinkButton>
                <DefaultButton :disabled="can_save" @click="save">
                    {{ saving ? 'Importing...' : 'Save &amp; Preview &rightarrow;' }}
                </DefaultButton>
            </div>
        </card>
    </div>
</template>

<script>
import draggable from 'vuedraggable';
import FieldCombinator from '../components/FieldCombinator';
import Modifiers from '../components/Modifiers';

export default {
    components: {
        draggable,
        FieldCombinator,
        Modifiers,
    },

    data() {
        return {
            resource: this.config?.resource || '',
            mappings: {},
            values: {},
            modifiers: {},
            combined: {},
            random: {},
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
        'mods',
    ],

    created() {
        this.init();
    },

    watch: {
        resource: {
            handler(newValue) {
                if (newValue === "") {
                    return;
                }

                const fields = this.fields[newValue];

                // Restore original settings
                if (newValue === this.config?.resource) {
                    this.init();

                    return;
                }

                // Reset the config
                for (let {name, attribute} of fields) {
                    this.mappings = {};
                    this.values = {};
                    this.combined = {};
                    this.modifiers = {};
					this.random = {};
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
        save() {
            if (! this.isValid()) {
                return;
            }

            this.saving = true;

            let data = {
                resource: this.resource,
                mappings: this.mappings,
                values: this.values,
                modifiers: this.modifiers,
                combined: this.combined,
                file: this.file,
                random: this.random,
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
                    console.log(e);
                    this.saving = false;
                    Nova.error('There was a problem saving your configuration');
                });

            this.saving = false;
        },

        goBack() {
            Nova.visit('/csv-import/')
        },

        isValid() {
            const mappedColumns = [],
                mappings = this.mappings;

            Object.keys(mappings).forEach(function (key) {
                if (mappings[key] !== "") {
                    mappedColumns.push(key);
                }
            });

            return this.resource !== '' && mappedColumns.length > 0;
        },

        url(path) {
            return '/nova-vendor/laravel-nova-csv-import/' + path;
        },

        init() {
            for (const prop of ['mappings', 'values', 'modifiers', 'combined']) {
                if (this.config[prop] && !Array.isArray(this.config[prop])) {
                    // https://github.com/inertiajs/inertia/issues/775#issuecomment-876030983
                    this[prop] = JSON.parse(JSON.stringify(this.config[prop]));
                }
            }
        },

        setFieldCombinators(attribute, config) {
            this.combined[attribute] = config;
        },

        setFieldModifiers(attribute, config) {
            this.modifiers[attribute] = config;
        }
    },

    computed: {
        can_save() {
            return ! this.isValid() || this.saving;
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
