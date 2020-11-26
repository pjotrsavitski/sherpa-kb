<template>
        <b-modal
            ref="modal"
            :id="modalId"
            title="Review question"
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
                    v-for="language in languages"
                    :key="language.code"
                    :id="formGroupId(language)"
                    :label="formGroupLabel(language)"
                    :label-for="formInputId(language)"
                    invalid-feedback="Question text is required"
                    :state="form.state[language.code]"
                >
                    <b-form-input
                        :id="formInputId(language)"
                        v-model="form.question[language.code]"
                        type="text"
                        required
                        :state="form.state[language.code]"
                        trim
                        debounce="250"
                        @update="updateInputState(language.code, ...arguments)"
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
                    id="input-group-status"
                    label="Status"
                    label-for="input-status"
                >
                    <b-form-select
                        v-model="form.status"
                        :options="statusOptions"
                        id="input-status"
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
                        :disabled="!canEdit()"
                    ></form-answer>
                </b-form-group>
            </form>
        </b-modal>
</template>

<script>
    import { mapState, mapGetters } from 'vuex'
    import ToastHelpers from '../../mixins/ToastHelpers'
    import FormAnswer from '../Input/FormAnswer'

    export default {
        props: ['question'],
        mixins: [ToastHelpers],
        components: {
            FormAnswer
        },
        computed: {
            ...mapState({
                states: state => state.questions.states,
                topics: state => state.questions.topics,
                languages: state => state.app.languages
            }),
            ...mapGetters({
                answers: 'answers/forQuestion'
            }),
            modalId() {
                return `question-review`
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
                .filter(state => state.value !== 'in_translation')
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
            }
        },
        data() {
            return {
                form: {
                    question: {},
                    topic: '',
                    status: '',
                    answer: '',
                    state: {}
                },
                isBusy: false
            }
        },
        methods: {
            resetModal() {
                const question = {}
                const state = {}
                Object.values(this.languages).forEach(language => {
                    const code = language.code
                    question[code] = this.question.descriptions.hasOwnProperty(code) ? this.question.descriptions[code] : ''
                    state[code] = question[code] ? true : null
                })
                this.form.question = question
                this.form.state = state
                this.form.status = this.question.status.value
                this.form.topic = this.question.topic ? this.question.topic.id : ''
                this.form.status = this.question.status.value
                this.form.answer = this.question.answer ? this.question.answer : ''
            },
            canEdit() {
                return true
            },
            canSave() {
                return this.canEdit() && Object.values(this.form.state).every(value => value === true)
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
                    status: this.form.status
                }

                data.descriptions = Object.entries(this.form.question).map(entry => {
                    return {
                        code: entry[0],
                        value: entry[1]
                    }
                })

                if (this.form.topic) {
                    data.topic = this.form.topic
                }

                if (this.form.answer) {
                    data.answer = this.form.answer
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
            formGroupId(language) {
                return `input-group-question-${language.code}`
            },
            formGroupLabel(language) {
                return `Question in ${language.name}`
            },
            formInputId(language) {
                return `input-question-${language.code}`
            },
            updateInputState(code, value) {
                this.form.state[code] = value.length > 0
            }
        }
    }
</script>
