<template>
        <b-modal
            ref="modal"
            :id="modalId"
            title="Review answer"
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
                    invalid-feedback="Answer text is required"
                    :state="form.state[language.code]"
                >
                    <b-form-textarea
                        :id="formInputId(language)"
                        v-model="form.answer[language.code]"
                        required
                        :state="form.state[language.code]"
                        rows="2"
                        max-rows="6"
                        trim
                        debounce="250"
                        @update="updateInputState(language.code, ...arguments)"
                        :disabled="!canEdit()"
                    ></b-form-textarea>
                </b-form-group>
                <b-form-group
                    id="input-group-status"
                    label="Status"
                    label-for="input-status"
                    description="Published status means that current answer is fully translated and available to the chatbot service."
                >
                    <b-form-select
                        v-model="form.status"
                        :options="statusOptions"
                        id="input-status"
                        :disabled="!canEdit()"
                    ></b-form-select>
                </b-form-group>
            </form>
        </b-modal>
</template>

<script>
    import { mapState } from 'vuex'
    import ToastHelpers from '../../mixins/ToastHelpers'

    export default {
        props: ['answer'],
        mixins: [ToastHelpers],
        computed: {
            ...mapState({
                languages: state => state.app.languages,
                states: state => state.answers.states
            }),
            modalId() {
                return `answer-review`
            },
            totalLanguages() {
                return this.$store.getters['app/totalLanguages']
            },
            statusOptions() {
                return this.states.map(state => {
                    return {
                        value: state.value,
                        text: state.text,
                        disabled: (this.answer.status.transitionable.indexOf(state.value) !== -1 || this.answer.status.value === state.value) ? false : true
                    };
                });
            }
        },
        data() {
            return {
                form: {
                    answer: {},
                    status: '',
                    state: {}
                },
                isBusy: false
            }
        },
        methods: {
            resetModal() {
                const answer = {}
                const state = {}
                Object.values(this.languages).forEach(language => {
                    const code = language.code;
                    answer[code] = this.answer.descriptions.hasOwnProperty(code) ? this.answer.descriptions[code] : ''
                    state[code] = answer[code] ? true : null
                })
                this.form.answer = answer
                this.form.state = state
                this.form.status = this.answer.status.value
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

                data.descriptions = Object.entries(this.form.answer).map(entry => {
                    return {
                        code: entry[0],
                        value: entry[1]
                    }
                })

                axios.put(`/answers/${this.answer.id}`, data)
                .then(response => {
                    this.isBusy = false
                    this.$store.dispatch('answers/updateAnswer', response.data)

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
                return `input-group-answer-${language.code}`
            },
            formGroupLabel(language) {
                return `Answer in ${language.name}`
            },
            formInputId(language) {
                return `input-answer-${language.code}`
            },
            updateInputState(code, value) {
                this.form.state[code] = value.length > 0
            }
        }
    }
</script>
