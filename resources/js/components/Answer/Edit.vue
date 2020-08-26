<template>
        <b-modal
            ref="modal"
            :id="modalId"
            title="Edit answer"
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
                    id="input-group-answer"
                    :label="answerLabel(language)"
                    label-for="input-answer"
                    invalid-feedback="Answer is required"
                    :state="form.state.answer"
                >
                    <b-form-textarea
                        id="input-answer"
                        v-model="form.answer"
                        required
                        :state="form.state.answer"
                        rows="2"
                        max-rows="6"
                        trim
                        debounce="250"
                        @update="updateAnswerState"
                        :disabled="!canEdit()"
                    ></b-form-textarea>
                </b-form-group>
                <b-form-group
                    id="input-group-translation"
                    :label="answerLabel('en')"
                    label-for="input-translation"
                    invalid-feedback="English translation is required"
                    :state="form.state.translation"
                >
                    <b-form-textarea
                        id="input-translation"
                        v-model="form.translation"
                        required
                        :state="form.state.translation"
                        rows="2"
                        max-rows="6"
                        trim
                        debounce="250"
                        @update="updateTranslationState"
                        :disabled="!canEdit()"
                    ></b-form-textarea>
                </b-form-group>
                <b-form-group
                    description="Changing status to Translated will send the answer for review by SELFIE master. This would also prevent you from making any changes to the answer itself or English translation."
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
            </form>
        </b-modal>
</template>

<script>
    import { mapState } from 'vuex'

    export default {
        props: ['language', 'answer'],
        computed: {
            ...mapState({
                languages: state => state.app.languages
            }),
            modalId() {
                return `answer-edit-${this.answer.id}`
            },
            totalLanguages() {
                return this.$store.getters['app/totalLanguages']
            }
        },
        data() {
            return {
                form: {
                    answer: '',
                    translation: '',
                    translated: false,
                    state: {
                        answer: null,
                        translation: null
                    }
                },
                isBusy: false
            }
        },
        methods: {
            resetModal() {
                this.form.answer = this.answer.descriptions[this.language]
                this.form.translation = this.answer.descriptions.en
                this.form.translated = false
                this.form.state = {
                    answer: this.form.answer.length > 0,
                    translation: this.form.translation.length > 0
                }
            },
            canEdit() {
                return this.answer.status.value === 'in_translation'
            },
            canSave() {
                return this.canEdit() && this.form.state.answer && this.form.state.translation
            },
            canChangeStatus() {
                // TODO Check if this check is correct (saving last missing language should allow status to be changed)
                return this.answer.status.value === 'in_translation' && this.form.state.answer && this.form.state.translation
                //return this.answer.status.value === 'in_translation' && Object.keys(this.answer.descriptions).length === this.totalLanguages
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
                    descriptions: []
                }
                data.descriptions.push({
                    code: 'en',
                    value: this.form.translation
                })
                data.descriptions.push({
                    code: this.language,
                    value: this.form.answer
                })

                if (this.form.translated) {
                    data.status = 'translated'
                }

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

                    let message = error.message

                    if (error.response && error.response.data && error.response.data.message) {
                        message = error.response.data.message
                    }

                    this.$bvToast.toast(message, {
                        variant: 'danger',
                        solid: true,
                        autoHideDelay: 2500,
                        noCloseButton: true
                    })
                })
            },
            answerLabel(code) {
                const language = this.languages.find(language => language.code === code)

                return `Answer in ${language ? language.name : code}`
            },
            updateInputState(value, name) {
                this.form.state[name] = value.length > 0
            },
            updateAnswerState(value) {
                this.updateInputState(value, 'answer')
            },
            updateTranslationState(value) {
                this.updateInputState(value, 'translation')
            }
        }
    }
</script>
