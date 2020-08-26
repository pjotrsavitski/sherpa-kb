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
                    <b-badge :variant="tabTitleBadgeVariant(0)" pill>{{ questions.length }}</b-badge>
                </template>

                <question-create :language="language"></question-create>
                <b-button v-b-modal="'question-create'" variant="primary" class="mb-4">Add new question</b-button>

                <questions-table :items="questions" :language="language"></questions-table>
            </b-tab>
            <b-tab lazy>
                <template v-slot:title>
                    Pending questions
                    <b-badge :variant="tabTitleBadgeVariant(1)" pill>{{ pendingQuestions.length }}</b-badge>
                </template>

                <pending-questions-table :items="pendingQuestions" :language="language"></pending-questions-table>
            </b-tab>
            <b-tab lazy>
                <template v-slot:title>
                    Answers
                    <b-badge :variant="tabTitleBadgeVariant(2)" pill>{{ answers.length }}</b-badge>
                </template>

                <answer-create :language="language"></answer-create>
                <b-button v-b-modal="'answer-create'" variant="primary" class="mb-4">Add new answer</b-button>
                
                <answers-table :items="answers" :language="language"></answers-table>
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

    export default {
        props: ['language'],
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
                questions: state => state.questions.items
            }),
            pendingQuestions() {
                return this.$store.getters['pendingQuestions/forLanguage'](this.language)
            },
            answers() {
                return this.$store.getters['answers/forLanguage'](this.language) 
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

        }
    }
</script>
