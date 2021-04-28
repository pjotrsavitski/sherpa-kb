<template>
    <b-modal
        ref="modal"
        :id="modalId"
        title="Add new category"
        ok-title="Create"
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
                id="input-group-description"
                label="Description"
                label-for="input-description"
                invalid-feedback="Description is required"
                :state="form.state.description"
            >
                <b-form-textarea
                    id="input-description"
                    v-model="form.description"
                    required
                    :state="form.state.description"
                    rows="2"
                    max-rows="6"
                    trim
                    @update="updateInputState('description', ...arguments)"
                ></b-form-textarea>
            </b-form-group>
        </form>
    </b-modal>
</template>

<script>
import ToastHelpers from '../../mixins/ToastHelpers'

export default {
    mixins: [ToastHelpers],
    computed: {
        modalId() {
            return 'topic-create'
        }
    },
    data() {
        return {
            form: {
                description: '',
                state: {
                    description: null
                }
            },
            isBusy: false
        }
    },
    methods: {
        resetModal() {
            this.form.description = ''
            this.form.state = {
                description: null
            }
        },
        canSave() {
            return this.form.state.description
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
                description: this.form.description
            }

            axios.post('/topics', data)
                .then(response => {
                    this.isBusy = false
                    this.$store.dispatch('topics/insertTopic', response.data)

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
        updateInputState(name, value) {
            this.form.state[name] = value.length > 0
        }
    }
}
</script>
