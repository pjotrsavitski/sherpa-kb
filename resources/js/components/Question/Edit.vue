<template>
        <b-modal
            ref="modal"
            :id="modalId"
            title="Edit question"
            ok-title="Save"
            @show="resetModal"
            @hidden="resetModal"
            @ok="handleSave"
            :ok-disabled="!canSave()"
            :busy="isBusy"
        >
            <form
                ref="form"
                @submit.stop.prevent="handleSubmit"
            >
                <b-form-group
                    id="input-group-question"
                    :label="questionLabel(language)"
                    label-for="input-question"
                    invalid-feedback="Question is required"
                    :state="questionState"
                >
                    <b-form-input
                        id="input-question"
                        v-model="form.question"
                        type="text"
                        required
                        :state="questionState"
                        trim
                        :disabled="!canEdit()"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    id="input-group-translation"
                    :label="questionLabel('en')"
                    label-for="input-translation"
                    invalid-feedback="English translation is required"
                    :state="translationState"
                    v-if="!isEnglish"
                >
                    <b-form-input
                        id="input-translation"
                        v-model="form.translation"
                        type="text"
                        required
                        :state="translationState"
                        trim
                        :disabled="!canEdit()"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    id="input-group-topic"
                    label="Category"
                    label-for="input-topic"
                >
                    <b-form-select
                        v-model="form.topic"
                        :options="topicOptions"
                        id="input-topic"
                        :disabled="!canEdit()"
                    ></b-form-select>
                </b-form-group>
                <b-form-group
                    id="input-group-answer"
                    label="Answer"
                    label-for="input-answer"
                >
                    <form-answer
                        :options="answerOptions"
                        v-model="form.answer"
                        :language="language"
                        :disabled="!canEdit()"
                    ></form-answer>
                </b-form-group>
                <b-form-group
                    description="Changing status to Translated will send the question for review by SELFIE master. You and other Language Experts would still be able to make changes as needed."
                >
                    <b-form-checkbox
                        v-model="form.translated"
                        name="translated"
                        switch
                        :disabled="!canChangeStatus()"
                    >
                        <b>Change status to translated</b>
                    </b-form-checkbox>
                </b-form-group>

                <div class="text-right" v-if="canDelete()">
                    <b-button
                        variant="danger"
                        @click="handleDelete()"
                        :disabled="isBusy"
                        v-b-tooltip
                        title="Delete"
                    >
                        <font-awesome-icon :icon="['fas', 'trash']" />
                    </b-button>
                </div>
            </form>
        </b-modal>
</template>

<script>
    import { mapState, mapGetters } from 'vuex'
    import ToastHelpers from '../../mixins/ToastHelpers'
    import FormAnswer from '../Input/FormAnswer'
    import { library } from '@fortawesome/fontawesome-svg-core'
    import { faTrash } from '@fortawesome/free-solid-svg-icons'

    library.add(faTrash)

    export default {
        props: ['question', 'language'],
        mixins: [ToastHelpers],
        components: {
            FormAnswer
        },
        computed: {
            ...mapState({
                states: state => state.questions.states,
                languages: state => state.app.languages
            }),
            ...mapGetters({
                answers: 'answers/forQuestion',
                topics: 'topics/sortedAsc'
            }),
            modalId() {
                return `question-edit`
            },
            questionState() {
                return (this.form.question && this.form.question.length) > 0 ? true : false
            },
            translationState() {
                return (this.form.translation && this.form.translation.length) > 0 ? true : false
            },
            topicOptions() {
                const options = this.topics.map(topic => {
                    return {
                        value: topic.id,
                        text: topic.description
                    }
                })
                options.unshift({
                    value: '',
                    text: ''
                })

                return options
            },
            statusOptions() {
                return this.states
                .filter(state => state.value !== 'published')
                .map(state => {
                    return {
                        value: state.value,
                        text: state.text,
                        disabled: ((this.question.status.transitionable.indexOf(state.value) !== -1 || this.question.status.value === state.value)) ? false : true
                    }
                })
            },
            answerOptions() {
                const options = this.answers
                .map(answer => {
                    return {
                        value: answer.id,
                        text: answer.descriptions.hasOwnProperty(this.language) ? answer.descriptions[this.language] : answer.descriptions.en
                    }
                })

                return options
            },
            isEnglish() {
                return this.language === 'en'
            }
        },
        data() {
            return {
                form: {
                    question: '',
                    translation: '',
                    topic: '',
                    answer: '',
                    translated: false
                },
                isBusy: false
            }
        },
        methods: {
            getDescription(item) {
                return item.descriptions[this.language]
            },
            getEnglishDescription(item) {
                return item.descriptions.hasOwnProperty('en') ? item.descriptions.en : ''
            },
            resetModal() {
                this.form.question = this.getDescription(this.question)
                this.form.translation = this.getEnglishDescription(this.question)
                this.form.topic = this.question.topic ? this.question.topic.id : ''
                this.form.answer = this.question.answer ? this.question.answer : ''
                this.form.translated = false
            },
            canEdit() {
                return this.question.status.value === 'in_translation' || this.question.status.value === 'translated'
            },
            canSave() {
                return this.canEdit() && (this.isEnglish ? this.form.question : this.form.question && this.form.translation)
            },
            canDelete() {
                return this.canEdit()
            },
            canChangeStatus() {
                // TODO Check if this check is correct (saving last missing language should allow status to be changed)
                return this.question.status.value === 'in_translation' && this.questionState && this.translationState
            },
            handleSave(bvModelEvent) {
                bvModelEvent.preventDefault()
                this.handleSubmit()
            },
            handleSubmit() {
                if (!this.$refs.form.checkValidity()) {
                    return
                }

                this.isBusy = true

                const data = {
                    descriptions: [],
                }

                if (!this.isEnglish) {
                    data.descriptions.push({
                        code: 'en',
                        value: this.form.translation
                    })
                }

                data.descriptions.push({
                    code: this.language,
                    value: this.form.question
                })

                if (this.form.topic) {
                    data.topic = this.form.topic
                }

                if (this.form.answer) {
                    data.answer = this.form.answer
                }

                if (this.form.translated) {
                    data.status = 'translated'
                }

                axios.put(`/questions/${this.question.id}`, data)
                .then(response => {
                    this.isBusy = false
                    this.$store.dispatch('questions/updateQuestion', response.data)

                    this.$nextTick(() => {
                        this.$bvModal.hide(this.modalId)
                    })
                })
                .catch(error => {
                    this.isBusy = false
                    console.error(error)
                    this.displayHttpError(error)
                })
            },
            questionLabel(code) {
                const language = this.languages.find(language => language.code === code)

                return `Question in ${language ? language.name : code}`
            },
            handleDelete() {
                this.$bvModal.msgBoxConfirm(`Are you sure you want to delete question with ID of ${this.question.id}?`,
                    {
                        title: 'Please confirm',
                        size: 'sm',
                        buttonSize: 'sm',
                        okVariant: 'danger',
                        okTitle: 'Confirm',
                        cancelTitle: 'Cancel',
                        footerClass: 'p-2',
                        hideHeaderClose: false,
                        centered: true
                    })
                    .then(value => {
                        if (value) {
                            this.isBusy = true
                            this.$store.dispatch('questions/deleteQuestion', this.question)
                                .then(() => {
                                    this.isBusy = false
                                    this.$nextTick(() => {
                                        this.$bvModal.hide(this.modalId)
                                    })
                                })
                                .catch(err => {
                                    this.isBusy = false
                                    console.error(err)
                                    this.displayHttpError(err)
                                })
                        }
                    })
                    .catch(err => {
                        console.error('Delete question confirmation dialog error', err)
                    })
            }
        }
    }
</script>
