<template>
    <div class="border-b pb-4 space-y-4">
        <h4 class="text-base font-bold">Combine multiple columns</h4>

        <p>
            Select any number of fields from your import file to be combined. Fields are simply concatenated. Use the
            <code>separator</code> field to define how they should be concatenated. If you don't choose a separator,
            the fields will be imported as an array.
        </p>

        <SelectControl @change="(value) => rawSeparator = value" :selected="separatorOption">
            <option value="">- No separator -</option>
            <option v-for="(name, value) in separators" :value="value">{{ name }}</option>
            <option value="__CUSTOM__">Custom</option>
        </SelectControl>

        <label v-if="rawSeparator?.startsWith('__CUSTOM__')" class="block">
            Custom separator
            <input v-model="separator" class="form-control form-input form-input-bordered mx-2">
        </label>

        <draggable
            v-model="columns"
            handle=".handle"
            item-key="combined">

            <template #item="{ element, index }">
                <div class="flex mb-2 space-x-2 items-start border-rounded bg-gray-100 p-2 handle">
                    <div>{{ index + 1 }}</div>

                    <SelectControl @change="(value) => columns[index].name = value" :selected="columns[index].name">
                        <option value="">- Select field -</option>

                        <optgroup label="Imported column">
                            <option v-for="heading in headings" :value="heading">{{ heading }}</option>
                        </optgroup>


                        <optgroup label="Custom - same value for each row">
                            <option value="custom">Custom value</option>
                        </optgroup>
                    </SelectControl>

                    <label class="flex items-center space-x-2" v-if="columns[index].name === 'custom'">
                        <span>Value</span>
                        <input v-model="columns[index].value"
                            class="form-control form-input form-input-bordered flex-1">
                    </label>

                    <label v-if="! rawSeparator">
                        as
                        <input v-model="columns[index].as"
                            class="form-control form-input form-input-bordered mx-2">
                    </label>

                    <button @click="remove(index)">&times;</button>
                </div>
            </template>
        </draggable>

        <button @click="add()"
            class="cursor-pointer rounded text-sm font-bold focus:outline-none focus:ring h-7 px-1 md:px-3">
            Add field
        </button>
    </div>
</template>

<script>
import draggable from 'vuedraggable';

export default {
    components: {
        draggable,
    },

    props: [
        'attribute',
        'config',
        'headings',
    ],
    
    data() {
        return {
            rawSeparator: '',
            columns: [],
            separators: {
                '__SPACE__': 'Space',
                '__TAB__': 'Tab',
            },
        }
    },

    computed: {
        separator: {
            get() {
                return this.rawSeparator.replace(/__CUSTOM__\.?/, '');
            },
            set(value) {
                this.rawSeparator = '__CUSTOM__.' + value;
            }
        },

        separatorOption() {
            return this.rawSeparator.startsWith('__CUSTOM__') ? '__CUSTOM__' : this.rawSeparator;
        },
    },

    mounted() {
        this.rawSeparator = this.config?.separator || '';
        this.columns = this.config?.columns || [];
    },

    watch: {
        rawSeparator: {
            handler() {
                this.update();
            }
        },

        columns: {
            handler() {
                this.update();
            },
            deep: true,
        }
    },

    methods: {
        add() {
            if (Array.isArray(this.columns)) {
                this.columns.push(this.template());
                return;
            }

            this.columns = [this.template()];
        },

        remove(index) {
            this.columns.splice(index, 1);
        },

        template() {
            return {
                name: '',
                as: null,
                value: null,
            };
        },

        update() {
            console.log(`Updating combinators for ${this.attribute}`, this.columns, this.rawSeparator);

            this.$emit('update', this.attribute, {
                columns: this.columns,
                separator: this.rawSeparator,
            });
        }
    }
}
</script>
