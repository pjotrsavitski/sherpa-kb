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
            :per-page="perPage"
            :current-page="currentPage"
        >
            <template v-slot:cell(id)="data">
                {{ data.value }}
                <question-edit :question="data.item" :language="language" v-if="language"></question-edit>
            </template>

            <template v-slot:cell(description)="data" v-if="language">
                <b-button v-b-modal="modalId(data.item.id)" variant="link" :class="{ 'text-secondary': !hasDescription(data.item) }">{{ descriptionOrPlaceholderText(data.item) }}</b-button>
            </template>

            <template v-slot:cell(english_translation)="data">
                <b-button v-b-modal="modalId(data.item.id)" variant="link">{{ data.item.descriptions.en }}</b-button>
            </template>

            <template v-slot:cell(category)="data">
                {{ data.item.topic ? data.item.topic.description : null }}
            </template>

            <template v-slot:cell(answer)="data">
                <b-button
                    pill
                    variant="outline-secondary"
                    v-b-popover.hover.click.blur.top="answerPopoverData(data.item)"
                    v-if="data.item.answer"
                >
                    <font-awesome-icon :icon="['fas', 'info']" />
                </b-button>
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
                {{ formatDate(data.value) }}
            </template>

            <template v-slot:cell(status)="data">
                {{ data.item.status.status }}
            </template>
        </b-table>

        <b-pagination
          v-model="currentPage"
          :total-rows="totalRows"
          :per-page="perPage"
          align="center"
          v-if="totalRows > perPage"
        ></b-pagination>
    </div>
</template>

<script>
    import { mapState, mapGetters } from 'vuex'
    import QuestionEdit from './Edit.vue'
    import TableHelpers from '../../mixins/TableHelpers'
    import { library } from '@fortawesome/fontawesome-svg-core'
    import { faInfo } from '@fortawesome/free-solid-svg-icons'

    library.add(faInfo)

    export default {
        props: ['items', 'language'],
        mixins: [TableHelpers],
        components: {
            QuestionEdit
        },
        computed: {
            ...mapState({
                perPage: state => state.app.itemsPerPage
            }),
            ...mapGetters({
                answers: 'answers/published'
            }),
            totalRows() {
                return this.items.length
            },
            fields() {
                const fields = [
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
                        key: 'answer',
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

                if (!this.language) {
                    return fields.filter(field => field.key !== 'description')
                }

                return fields
            }
        },
        data() {
            return {
                currentPage: 1
            }
        },
        methods: {
            modalId(id) {
                const type = this.language ? 'edit' : 'review'
                return `question-${type}-${id}`
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
            answerPopoverData(item) {
                const answer = this.answers.find(answer => answer.id === item.answer)
                const language = this.language ? this.language : 'en'

                return {
                    content: answer ? this.descriptionInLanguageOrEnglish(answer.descriptions, language) : '',
                    customClass: 'popover-preserve-new-lines'
                }
            }
        },
        created() {
            this.$store.dispatch('questions/preloadStates')
            this.$store.dispatch('questions/preloadTopics')
        }
    }
</script>

<style>
.popover-preserve-new-lines .popover-body{
    white-space: pre-wrap;
}
</style>
