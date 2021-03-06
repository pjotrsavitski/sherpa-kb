<template>
        <b-modal
            ref="modal"
            :id="modalId"
            title="Review pending question"
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
                    label="Question"
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
                    label="English translation"
                    label-for="input-translation"
                    invalid-feedback="English translation is required"
                    :state="translationState"
                    description="Translation is not needed if suggestion was submitted in English."
                >
                    <b-form-input
                        id="input-translation"
                        v-model="form.translation"
                        type="text"
                        required
                        :state="translationState"
                        trim
                        :disabled="!canEdit() || isEnglishOnly()"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    id="input-group-group"
                    label="Group number"
                >
                    <b-form-input
                        id="input-group"
                        v-model="form.group"
                        type="number"
                        min="0"
                        :disabled="!canEdit()"
                    >
                    </b-form-input>
                </b-form-group>
                <b-form-group
                    id="input-group-status"
                    label="Status"
                    label-for="input-status"
                    description="Completed and Canceled are final states, former of those will create a new question as a result. Pending will send the pending question back to language experts."
                >
                    <b-form-select
                        v-model="form.status"
                        :options="statusOptions()"
                        id="input-status"
                        :disabled="!canEdit()"
                    ></b-form-select>
                </b-form-group>

                <div class="text-right">
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
    import { mapState } from 'vuex'
    import ToastHelpers from '../../mixins/ToastHelpers'
    import { library } from '@fortawesome/fontawesome-svg-core'
    import { faTrash } from '@fortawesome/free-solid-svg-icons'

    library.add(faTrash)

    export default {
        props: ['pendingQuestion'],
        mixins: [ToastHelpers],
        computed: {
            ...mapState({
                states: state => state.pendingQuestions.states,
            }),
            modalId() {
                return 'pending-question-review'
            },
            questionState() {
                return (this.form.question && this.form.question.length) > 0 ? true : false
            },
            translationState() {
                return (this.form.translation && this.form.translation.length) > 0 ? true : false
            }
        },
        data() {
            return {
                form: {
                    question: "",
                    translation: "",
                    group: null,
                    status: ""
                },
                isBusy: false
            }
        },
        methods: {
            getDescription(item) {
                const languages = Object.keys(item.descriptions)

                if (languages.length === 1 && languages[0] === 'en') {
                    return item.descriptions.en
                }

                return (languages[0] !== 'en') ? item.descriptions[languages[0]] : item.descriptions[languages[1]]
            },
            getEnglishDescription(item) {
                return item.descriptions.hasOwnProperty('en') ? item.descriptions.en : ''
            },
            statusOptions() {
                return this.states.map(state => {
                    return {
                        value: state.value,
                        text: state.text,
                        disabled: (this.pendingQuestion.status.transitionable.indexOf(state.value) !== -1 || this.pendingQuestion.status.value === state.value) ? false : true
                    }
                })
            },
            resetModal() {
                this.form.question = this.getDescription(this.pendingQuestion)
                this.form.translation = this.getEnglishDescription(this.pendingQuestion)
                this.form.group = this.pendingQuestion.group
                this.form.status = this.pendingQuestion.status.value
            },
            isEnglishOnly() {
                const languages = Object.keys(this.pendingQuestion.descriptions)

                return languages.length === 1 && languages[0] === 'en'
            },
            canEdit() {
                return this.pendingQuestion.status.value === 'propagated'
            },
            canSave() {
                return this.canEdit()
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
                axios.put('/pending_questions/' + this.pendingQuestion.id, {
                    question: this.form.question,
                    translation: this.form.translation,
                    group: this.form.group,
                    status: this.form.status
                })
                .then(response => {
                    this.isBusy = false
                    this.$store.dispatch('pendingQuestions/updatePendingQuestion', response.data)
                    if (response.data.status.value === 'completed') {
                        this.$store.dispatch('questions/loadAllQuestions')
                    }


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
            handleDelete() {
                this.$bvModal.msgBoxConfirm(`Are you sure you want to delete pending question with ID of ${this.pendingQuestion.id}?`,
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
                            this.$store.dispatch('pendingQuestions/deletePendingQuestion', this.pendingQuestion)
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
                        console.error('Delete pending question confirmation dialog error', err)
                    })
            }
        }
    }
</script>
