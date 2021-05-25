<template>
    <div>
    <question-edit :question="question" :language="language" v-if="language && question"></question-edit>
    <question-review :question="question" v-if="!language && question"></question-review>

    <b-modal
        ref="modal"
        :id="modalId"
        title="Answer questions"
        :hide-footer="true"
        @show="resetModal"
        @hidden="resetModal"
    >
        <strong>{{ description(answer) }}</strong>

        <div
            v-if="questions && questions.length > 0"
            class="mt-2"
        >
            <b-badge variant="secondary">{{ questions.length }}</b-badge>
            <b-list-group>
                <b-list-group-item
                    v-for="question in questions"
                    :key="question.id"
                    class="d-flex flex-row flex-row-reverse"
                >
                    <div class="pl-2">
                        <b-button-group size="sm">
                            <b-button
                                variant="success"
                                @click="onOpenQuestionModal(question)"
                            >
                                <font-awesome-icon :icon="['fas', 'edit']" />
                            </b-button>
                        </b-button-group>
                    </div>

                    <div class="flex-grow-1">
                        <small class="mb-0">
                            {{ description(question) }}
                        </small>
                    </div>
                </b-list-group-item>
            </b-list-group>
        </div>

        <div
            v-if="!questions || questions.length === 0"
            class="mt-2"
        >
            <small>No questions found!</small>
        </div>
    </b-modal>
    </div>
</template>

<script>

import QuestionEdit from '../Question/Edit.vue'
import QuestionReview from '../Question/Review.vue'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faEdit } from '@fortawesome/free-solid-svg-icons'

library.add(faEdit)

export default {
    props: ['language', 'answer'],
    components: {
        QuestionEdit,
        QuestionReview
    },
    computed: {
        questions() {
            return this.$store.getters['questions/relatedToAnswer'](this.answer)
        },
        modalId() {
            return `answer-questions`
        }
    },
    data() {
        return {
            question: null
        }
    },
    methods: {
        resetModal() {
            this.question = null
        },
        description(item) {
            return item.descriptions.hasOwnProperty(this.language) ? item.descriptions[this.language] : item.descriptions['en']
        },
        onOpenQuestionModal(question) {
            this.question = question
            this.$nextTick(() => {
                this.$bvModal.show(this.language ? 'question-edit' : 'question-review')
            })
        }
    }
}
</script>
