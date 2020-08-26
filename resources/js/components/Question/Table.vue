<template>
    <div>
        <h3>Questions</h3>
        
        <b-table
            striped
            hover
            :fields="fields"
            :items="items"
            primary-key="id"
            thead-class="text-center"
            stacked="lg"
        >
            <template v-slot:cell(id)="data">
                {{ data.value }}
                <question-edit :question="data.item" :language="language" v-if="language"></question-edit>
            </template>

            <template v-slot:cell(description)="data">
                <b-button v-b-modal="editModalId(data.item.id)" variant="link" :class="{ 'text-secondary': !hasDescription(data.item) }">{{ descriptionOrPlaceholderText(data.item) }}</b-button>
            </template>

            <template v-slot:cell(english_translation)="data">
                <b-button v-b-modal="editModalId(data.item.id)" variant="link">{{ data.item.descriptions.en }}</b-button>
            </template>

            <template v-slot:cell(category)="data">
                {{ data.item.topic ? data.item.topic.description : null }}
            </template>

            <template v-slot:cell(languages)="data">
                <b-button
                    pill
                    :variant="languagesButtonVariant(data.item)"
                    v-b-popover.hover.click.blur.top="languagesPopoverData(data.item)"
                >
                    {{ descriptionsCount(data.item) }} / {{ totalLanguages }}
                </b-button>
            </template>

            <template v-slot:cell(date)="data">
                {{ data.value }}
            </template>

            <template v-slot:cell(status)="data">
                {{ data.item.status.status }}
            </template>
        </b-table>
    </div>
</template>

<script>
    import QuestionEdit from './Edit.vue'
    import TableHelpers from '../../mixins/TableHelpers'

    export default {
        props: ['items', 'language'],
        mixins: [TableHelpers],
        components: {
            QuestionEdit
        },
        data() {
            return {
                fields: [
                    {
                        key: 'id',
                        label: 'ID',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'description',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'english_translation',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'category',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'date',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'languages',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'status',
                        sortable: false,
                        tdClass: (value, key, item) => {
                            let classes = ['align-middle', 'text-center']

                            switch(value.value) {
                                case 'in_translation':
                                    classes.push('table-warning')
                                    break
                                case 'translated':
                                    classes.push('table-info')
                                    break
                                case 'published':
                                    classes.push('table-success')
                            }

                            return classes
                        },
                    }
                ]
            }
        },
        methods: {
            editModalId(id) {
                return 'question-edit-' + id
            },
            descriptionsCount(item) {
                return Object.keys(item.descriptions).length
            },
            hasDescription(item) {
                return item.descriptions.hasOwnProperty(this.language) && item.descriptions[this.language].trim()
            },
            descriptionOrPlaceholderText(item) {
                return this.hasDescription(item) ? item.descriptions[this.language] : 'Add translation'
            },
        },
        created() {
            this.$store.dispatch('questions/preloadStates')
            this.$store.dispatch('questions/preloadTopics')
        }
    }
</script>
