<template>
    <div>
        <Head>
            <title>Review Import</title>
        </Head>

        <heading class="mb-6">CSV Import - Review</heading>

        <card class="p-4 space-y-4" style="min-height: 300px">
            <p>
                <b>{{ imported }}</b> row(s) out of {{ total_rows }} were successfully imported.
            </p>

            <p v-if="failures.length !== 0 && errors.length !== 0">
                There were some errors...
            </p>

            <!-- TODO: These two should be extracted into a component as they're basically identical -->
            <template v-if="failures.length !== 0">
                <BasicButton @click="showFailures = !showFailures">
                    {{ showFailures ? 'Hide failures' : 'Show failures' }}
                </BasicButton>
                <div v-if="showFailures">
                    <h2>Failures</h2>
                    <table cellpadding="10">
                        <thead>
                            <tr>
                                <th>Row #</th>
                                <th>Attribute</th>
                                <th>Details</th>
                                <th>Data</th>
                                <th>Row Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="(row, rowIndex) in failures">
                                <tr v-for="(problem, index) in row" :class="{'border-b': index === row.length - 1}">
                                    <td v-if="index === 0" :rowspan="row.length" valign="top">
                                        {{ problem.row - 1 }}
                                    </td>
                                    <td valign="top">
                                        {{ problem.attribute }}
                                    </td>
                                    <td valign="top">
                                        <div v-for="error in problem.errors">{{ error }}</div>
                                    </td>
                                    <td valign="top">
                                        {{ problem.values[config.map[problem.attribute]] }}
                                        <i v-if="! problem.values[config.map[problem.attribute]]">null</i>
                                    </td>
                                    <td :rowspan="row.length" valign="top">
                                        <div v-if="index === 0">
                                            <BasicButton @click="showFailureData[rowIndex] = !showFailureData[rowIndex]">
                                                {{ showFailureData[rowIndex] ? 'Hide data' : 'Show all row data' }}
                                            </BasicButton>
                                            <div v-show="showFailureData[rowIndex]">
                                                <div v-for="(value, key) in problem.values">
                                                    {{ key }}: {{ value }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>

            <template v-if="errors.length !== 0">
                <BasicButton @click="showErrors = !showErrors">
                    {{ showErrors ? 'Hide errors' : 'Show errors' }}
                </BasicButton>
                <div v-if="showErrors">
                    <h2>Errors</h2>
                    <table cellpadding="10">
                        <thead>
                            <tr>
                                <th>Row #</th>
                                <th>Attribute</th>
                                <th>Details</th>
                                <th>Data</th>
                                <th>Row Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="(row, rowIndex) in errors">
                                <tr v-for="(problem, index) in row" :class="{'border-b': index === row.length - 1}">
                                    <td v-if="index === 0" :rowspan="row.length" valign="top">
                                        {{ problem.row - 1 }}
                                    </td>
                                    <td valign="top">
                                        {{ problem.attribute }}
                                    </td>
                                    <td valign="top">
                                        <div v-for="error in problem.errors">{{ error }}</div>
                                    </td>
                                    <td valign="top">
                                        {{ problem.values[config.map[problem.attribute]] }}
                                        <i v-if="! problem.values[config.map[problem.attribute]]">null</i>
                                    </td>
                                    <td :rowspan="row.length" valign="top">
                                        <div v-if="index === 0">
                                            <BasicButton @click="showErrorData[rowIndex] = !showErrorData[rowIndex]">
                                                {{ showErrorData[rowIndex] ? 'Hide data' : 'Show all row data' }}
                                            </BasicButton>
                                            <div v-show="showErrorData[rowIndex]">
                                                <div v-for="(value, key) in problem.values">
                                                    {{ key }}: {{ value }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>

            <div class="flex justify-center">
                <LinkButton @click="reconfigure"><HeroiconsOutlineRewind /> Reconfigure</LinkButton>
                <LinkButton @click="restart"><HeroiconsOutlineRefresh /> Upload another</LinkButton>
            </div>
        </card>
    </div>
</template>

<script>
export default {
    mounted() {
        console.log(this.failures.length === 0);
        console.log(this.errors.length === 0);
    },
    props: [
        'failures',
        'errors',
        'total_rows',
        'config',
        'imported',
        'file',
    ],

    data() {
        return {
            showFailureData: {},
            showFailures: false,
            showErrorData: {},
            showErrors: false,
        };
    },

    methods: {
        reconfigure() {
            Nova.visit('/csv-import/configure/' + this.file);
        },

        restart() {
            Nova.visit('/csv-import');
        },
    }
}
</script>