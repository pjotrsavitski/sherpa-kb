<template>
    <div class="mt-4">
        <b-tabs
            v-model="tabIndex"
            content-class="mt-4"
            active-nav-item-class="bg-primary text-white"
            fill
        >
            <b-tab lazy>
                <template v-slot:title>
                    Questions
                    <b-badge
                        :variant="tabTitleBadgeVariant(0)"
                        pill
                        v-if="!isBusy"
                    >
                        {{ questions.length }}
                    </b-badge>
                    <b-spinner
                        :variant="tabTitleBadgeVariant(0)"
                        type="grow"
                        label="Loading..."
                        small
                        v-if="isBusy"
                    ></b-spinner>
                </template>

                <question-create :language="language"></question-create>
                <b-button
                    v-b-modal="'question-create'"
                    variant="primary"
                    class="mb-4"
                    v-b-tooltip
                    title="Add new question"
                >
                    <font-awesome-icon :icon="['fas', 'plus']" />
                </b-button>

                <questions-table :items="questions" :language="language" :is-busy="isBusy"></questions-table>
            </b-tab>
            <b-tab lazy>
                <template v-slot:title>
                    Pending questions
                    <b-badge
                        :variant="tabTitleBadgeVariant(1)"
                        pill
                        v-if="!isBusy"
                    >
                        {{ pendingQuestions.length }}
                    </b-badge>
                    <b-spinner
                        :variant="tabTitleBadgeVariant(1)"
                        type="grow"
                        label="Loading..."
                        small
                        v-if="isBusy"
                    ></b-spinner>
                </template>

                <pending-questions-table :items="pendingQuestions" :language="language" :is-busy="isBusy"></pending-questions-table>
            </b-tab>
            <b-tab lazy>
                <template v-slot:title>
                    Answers
                    <b-badge
                        :variant="tabTitleBadgeVariant(2)"
                        pill
                        v-if="!isBusy"
                    >
                        {{ answers.length }}
                    </b-badge>
                    <b-spinner
                        :variant="tabTitleBadgeVariant(2)"
                        type="grow"
                        label="Loading..."
                        small
                        v-if="isBusy"
                    ></b-spinner>
                </template>

                <answer-create :language="language"></answer-create>
                <b-button
                    v-b-modal="'answer-create'"
                    variant="primary"
                    class="mb-4"
                    v-b-tooltip
                    title="Add new answer"
                >
                    <font-awesome-icon :icon="['fas', 'plus']" />
                </b-button>

                <answers-table :items="answers" :language="language" :is-busy="isBusy"></answers-table>
            </b-tab>
        </b-tabs>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import QuestionsTable from './Question/Table.vue'
    import PendingQuestionsTable from './PendingQuestion/Table.vue'
    import AnswersTable from './Answer/Table.vue'
    import AnswerCreate from './Answer/Create.vue'
    import QuestionCreate from './Question/Create.vue'
    import { library } from '@fortawesome/fontawesome-svg-core'
    import { faPlus } from '@fortawesome/free-solid-svg-icons'

    library.add(faPlus)

    export default {
        props: ['language', 'isBusy'],
        components: {
            QuestionsTable,
            PendingQuestionsTable,
            AnswersTable,
            AnswerCreate,
            QuestionCreate
        },
        data() {
            return {
                tabIndex: 0,
            }
        },
        computed: {
            ...mapState({
                questions: state => state.questions.items,
                answers: state => state.answers.items
            }),
            pendingQuestions() {
                return this.$store.getters['pendingQuestions/forLanguage'](this.language)
            },
            isEnglish() {
                return this.language === 'en'
            }
        },
        methods: {
            tabTitleBadgeVariant(index) {
                return this.tabIndex === index ? 'light' : 'secondary'
            }
        },
        created() {
            this.$store.dispatch('questions/preloadAllQuestions')
            this.$store.dispatch('pendingQuestions/preloadAllPendingQuestions')
            this.$store.dispatch('answers/preloadAllAnswers')
            this.$store.dispatch('topics/preloadAllTopics')
        }
    }
</script>
