<template>
    <div class="space-y-4">
        <h4 class="text-base font-bold">Modifiers</h4>

        <p>
            Use modifiers to modify the value of the source selected above <i>before</i> it gets saved to your
            resource. Modifiers are combinatory meaning you can stack them together to do weird and wonderful
            things with your data. They are executed in the order defined.
        </p>

        <p>
            <b>TIP</b>: You can drag and drop modifiers to re-order them.
        </p>

        <draggable
            v-model="modifiers"
            handle=".handle"
            item-key="modifier">

            <template #item="{ element, index }">
                <div class="border-rounded bg-gray-100 p-2 handle mb-2">
                    <div class="flex space-x-2 items-start">
                        <div>{{ index + 1 }}</div>
                        <div class="flex flex-col space-y-2">
                            <SelectControl @change="(value) => element.name = value" :selected="element.name">
                                <option value="">- Do not modify -</option>

                                <option v-for="mod in mods" :value="mod.name">{{ mod.title }}</option>
                            </SelectControl>

                            <label v-for="(config, name) in mods[element.name].settings"
                                v-if="mods[element.name]?.settings" class="flex items-center space-x-2">
                                <span>{{ config.title }}</span>

                                <SelectControl v-if="config.type === 'select'"
                                    @change="(value) => element.settings[name] = value"
                                    :selected="element.settings[name]">
                                    <option v-for="(option, value) of config.options" :value="value"
                                        :selected="value === config.default">
                                        {{ option }}
                                    </option>
                                </SelectControl>

                                <input type="text" v-if="config.type === 'string'" v-model="element.settings[name]"
                                    class="form-control form-input form-input-bordered ml-4" :placeholder="config.default">

                                <input type="text" v-if="config.type === 'boolean'" v-model="element.settings[name]"
                                    class="checkbox" :checked="config.default">

                                <div class="help-text" v-html="config.help"></div>
                            </label>
                        </div>
                        <button @click="remove(index)">&times;</button>
                    </div>
                    <p v-html="mods[element.name]?.description"></p>
                </div>
            </template>
        </draggable>

        <button @click="add()"
            class="cursor-pointer rounded text-sm font-bold focus:outline-none focus:ring h-7 px-1 md:px-3">
            Add modifier
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
        'mods',
    ],

    data() {
        return {
            modifiers: [],
        }
    },

    mounted() {
        this.modifiers = this.config;
    },

    watch: {
        modifiers: {
            handler() {
                this.update();
            },
            deep: true,
        }
    },

    methods: {
        add() {
            if (Array.isArray(this.modifiers)) {
                this.modifiers.push(this.template());
                return;
            }

            this.modifiers = [this.template()];
        },

        remove(index) {
            this.modifiers.splice(index, 1);
        },

        template() {
            return {
                name: '',
                settings: {}
            };
        },

        update() {
            console.log(`Updating modifiers for ${this.attribute}`, this.modifiers);
            this.$emit('update', this.attribute, this.modifiers);
        },
    }
}
</script>
