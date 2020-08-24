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
            </b-tab>
        </b-tabs>
    </div>
</template>

<script>
    import { mapState, mapActions } from 'vuex'

    export default {
        props: ['language'],
        data() {
            return {
                tabIndex: 0,
            }
        },
        computed: {
            ...mapState({
                answers: state => state.answers.items,
            }),
            questions() {
                return this.$store.getters['questions/forLanguage'](this.language)

            },
            pendingQuestions() {
                return this.$store.getters['pendingQuestions/forLanguage'](this.language)
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
        }
    }
</script>
