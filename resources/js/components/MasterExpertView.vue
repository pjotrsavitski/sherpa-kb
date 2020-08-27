<template>
    <div class="mt-4">
        <div class="text-right">
            <b-dropdown
                id="language-expert-view"
                dropup
                text="Open language expert view"
                variant="outline-secondary"
                class="m-2"
                size="lg"
            >
                <b-dropdown-item
                    v-for="language in languages"
                    :href="languageUrl(language)"
                    v-bind:key="language.code"
                    class="text-uppercase"
                >
                    {{ language.name }}
                </b-dropdown-item>
            </b-dropdown>
        </div>

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

                <questions-table :items="questions"></questions-table>
            </b-tab>
            <b-tab lazy>
                <template v-slot:title>
                    Pending questions
                    <b-badge :variant="tabTitleBadgeVariant(1)" pill>{{ pendingQuestions.length }}</b-badge>
                </template>

                <pending-questions-table :items="pendingQuestions"></pending-questions-table>
            </b-tab>
            <b-tab lazy>
                <template v-slot:title>
                    Answers
                    <b-badge :variant="tabTitleBadgeVariant(2)" pill>{{ answers.length }}</b-badge>
                </template>

                <answers-table :items="answers"></answers-table>
            </b-tab>
        </b-tabs>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import QuestionsTable from './Question/Table.vue'
    import PendingQuestionsTable from './PendingQuestion/Table.vue'
    import AnswersTable from './Answer/Table.vue'
    
    export default {
        components: {
            QuestionsTable,
            PendingQuestionsTable,
            AnswersTable
        },
        data() {
            return {
                tabIndex: 0,
            }
        },
        computed: {
            ...mapState({
                languages: state => state.app.languages
            }),
            questions() {
                return this.$store.getters['questions/forReview']
            },
            pendingQuestions() {
                return this.$store.getters['pendingQuestions/forReview']
            },
            answers() {
                return this.$store.getters['answers/forReview']
            }
        },
        methods: {
            tabTitleBadgeVariant(index) {
                return this.tabIndex === index ? 'light' : 'secondary'
            },
            languageUrl(language) {
                return `${window.location.href}/${language.code}`
            }
        },
        created() {
            this.$store.dispatch('questions/preloadAllQuestions')
            this.$store.dispatch('pendingQuestions/preloadAllPendingQuestions')
            this.$store.dispatch('answers/preloadAllAnswers')
        }
    }
</script>
