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
            </form>
        </b-modal>
</template>

<script>
    import { mapState } from 'vuex'
    
    export default {
        props: ['pendingQuestion'],
        computed: {
            ...mapState({
                states: state => state.pendingQuestions.states,
            }),
            modalId() {
                return 'pending-question-review';
            },
            questionState() {
                return (this.form.question && this.form.question.length) > 0 ? true : false;
            },
            translationState() {
                return (this.form.translation && this.form.translation.length) > 0 ? true : false;
            }
        },
        data() {
            return {
                form: {
                    question: "",
                    translation: "",
                    status: ""
                },
                isBusy: false
            };
        },
        methods: {
            getDescription(item) {
                const languages = Object.keys(item.descriptions);

                if (languages.length === 1 && languages[0] === 'en') {
                    return item.descriptions.en;
                }

                return (languages[0] !== 'en') ? item.descriptions[languages[0]] : item.descriptions[languages[1]];
            },
            getEnglishDescription(item) {
                return item.descriptions.hasOwnProperty('en') ? item.descriptions.en : '';
            },
            statusOptions() {
                return this.states.map(state => {
                    return {
                        value: state.value,
                        text: state.text,
                        disabled: (this.pendingQuestion.status.transitionable.indexOf(state.value) !== -1 || this.pendingQuestion.status.value === state.value) ? false : true
                    };
                });
            },
            resetModal() {
                this.form.question = this.getDescription(this.pendingQuestion);
                this.form.translation = this.getEnglishDescription(this.pendingQuestion);
                this.form.status = this.pendingQuestion.status.value;
            },
            isEnglishOnly() {
                const languages = Object.keys(this.pendingQuestion.descriptions);

                return languages.length === 1 && languages[0] === 'en';
            },
            canEdit() {
                return this.pendingQuestion.status.value === 'propagated';
            },
            canSave() {
                return this.canEdit();
            },
            handleSave(bvModelEvent) {
                bvModelEvent.preventDefault();
                this.handleSubmit();
            },
            handleSubmit() {
                if (!this.$refs.form.checkValidity()) {
                    return;
                }

                this.isBusy = true;
                axios.put('/pending_questions/' + this.pendingQuestion.id, {
                    question: this.form.question,
                    translation: this.form.translation,
                    status: this.form.status
                })
                .then(response => {
                    this.isBusy = false;
                    this.$store.dispatch('pendingQuestions/updatePendingQuestion', response.data)
                    if (response.data.status.value === 'completed') {
                        this.$store.dispatch('questions/loadAllQuestions')
                    }


                    this.$nextTick(() => {
                        this.$bvModal.hide(this.modalId);
                    });
                })
                .catch(error => {
                    this.isBusy = false;
                    console.error(error);

                    let message = error.message;

                    if (error.response && error.response.data && error.response.data.message) {
                        message = error.response.data.message;
                    }

                    this.$bvToast.toast(message, {
                        variant: 'danger',
                        solid: true,
                        autoHideDelay: 2500,
                        noCloseButton: true
                    });
                });
            }
        }
    }
</script>
